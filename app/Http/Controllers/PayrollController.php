<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Models\Department;
use App\Models\EmployeeSalary;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollEmailService;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    use AuthorizesByPermission;

    /**
     * List all payroll records with filters and summary stats.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('payroll.view');

        $currentYear = (int) ($request->input('year') ?: now()->year);
        $currentMonth = $request->input('month') !== null && $request->input('month') !== ''
            ? (int) $request->input('month')
            : null;

        $query = Payroll::query()
            ->with(['user', 'creator'])
            ->latest('payroll_year')
            ->latest('payroll_month')
            ->latest('id');

        if ($currentYear) {
            $query->where('payroll_year', $currentYear);
        }

        if ($currentMonth) {
            $query->where('payroll_month', $currentMonth);
        }

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('payment_status', $status);
        }

        if ($department = $request->string('department')->trim()->value()) {
            $query->whereHas('user', fn ($q) => $q->where('department', $department));
        }

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('payroll_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('emp_id', 'like', "%{$search}%");
                    });
            });
        }

        // Summary metrics for current query scope
        $metricsQuery = clone $query;
        $summary = [
            'total_gross' => (clone $metricsQuery)->sum('gross_salary'),
            'total_deductions' => (clone $metricsQuery)->sum('total_deductions'),
            'total_net' => (clone $metricsQuery)->sum('net_salary'),
            'paid_count' => (clone $metricsQuery)->where('payment_status', 'paid')->count(),
            'pending_count' => (clone $metricsQuery)->where('payment_status', '!=', 'paid')->count(),
        ];

        $payrolls = $query->paginate(15)->withQueryString();

        return view('payroll.index', [
            'payrolls' => $payrolls,
            'summary' => $summary,
            'selectedYear' => $currentYear,
            'selectedMonth' => $currentMonth,
            'selectedStatus' => $request->input('status', ''),
            'selectedDepartment' => $request->input('department', ''),
            'departments' => Department::names(activeOnly: false),
            'years' => range(now()->year - 2, now()->year + 1),
        ]);
    }

    /**
     * Show form to generate monthly payroll.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('payroll.create');

        $year = (int) ($request->input('year') ?: now()->year);
        $month = (int) ($request->input('month') ?: now()->month);

        // Fetch all active employees
        $employees = User::query()
            ->active()
            ->with(['salary'])
            ->orderBy('name')
            ->get();

        // Already generated payroll user IDs for this period
        $generatedUserIds = Payroll::query()
            ->where('payroll_year', $year)
            ->where('payroll_month', $month)
            ->pluck('user_id')
            ->flip()
            ->all();

        return view('payroll.create', [
            'employees' => $employees,
            'generatedUserIds' => $generatedUserIds,
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'years' => range(now()->year - 2, now()->year + 1),
        ]);
    }

    /**
     * Store generated payroll records.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('payroll.create');

        $validated = $request->validate([
            'payroll_year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'payroll_month' => ['required', 'integer', 'min:1', 'max:12'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
            'bonus' => ['nullable', 'array'],
            'bonus.*' => ['nullable', 'numeric', 'min:0'],
            'overtime_pay' => ['nullable', 'array'],
            'overtime_pay.*' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'string', 'in:pending,processed,paid'],
            'payment_date' => ['nullable', 'date'],
        ]);

        $year = (int) $validated['payroll_year'];
        $month = (int) $validated['payroll_month'];
        $userIds = $validated['user_ids'];
        $bonuses = $request->input('bonus', []);
        $overtimes = $request->input('overtime_pay', []);
        $status = $validated['payment_status'];
        $paymentDate = $status === 'paid' ? ($validated['payment_date'] ?: now()->toDateString()) : null;

        $createdCount = 0;

        DB::transaction(function () use ($userIds, $year, $month, $bonuses, $overtimes, $status, $paymentDate, &$createdCount) {
            foreach ($userIds as $userId) {
                // Skip if already exists for this period
                if (Payroll::where('user_id', $userId)->where('payroll_year', $year)->where('payroll_month', $month)->exists()) {
                    continue;
                }

                $user = User::with('salary')->findOrFail($userId);
                $salary = $user->salary;

                // Ensure salary structure is approved by Admin before generating payroll
                if ($salary && ! $salary->isApproved()) {
                    continue;
                }

                $basic = $salary ? (float) $salary->basic_salary : 0;
                $hra = $salary ? (float) $salary->hra : 0;
                $allowances = $salary ? ((float) $salary->conveyance_allowance + (float) $salary->special_allowance + (float) $salary->other_allowances) : 0;
                $pf = $salary ? (float) $salary->provident_fund : 0;
                $pt = $salary ? (float) $salary->professional_tax : 0;
                $tds = $salary ? (float) $salary->tds : 0;
                $otherDeductions = $salary ? (float) $salary->other_deductions : 0;

                $bonus = isset($bonuses[$userId]) && is_numeric($bonuses[$userId]) ? (float) $bonuses[$userId] : 0;
                $overtime = isset($overtimes[$userId]) && is_numeric($overtimes[$userId]) ? (float) $overtimes[$userId] : 0;

                $payroll = new Payroll([
                    'user_id' => $user->id,
                    'payroll_month' => $month,
                    'payroll_year' => $year,
                    'payroll_code' => Payroll::generateCode($user, $year, $month),
                    'basic_salary' => $basic,
                    'hra' => $hra,
                    'allowances' => $allowances,
                    'bonus' => $bonus,
                    'overtime_pay' => $overtime,
                    'provident_fund' => $pf,
                    'professional_tax' => $pt,
                    'tds' => $tds,
                    'other_deductions' => $otherDeductions,
                    'payment_status' => $status,
                    'payment_date' => $paymentDate,
                    'payment_method' => $salary?->payment_method ?? 'bank_transfer',
                    'created_by' => auth()->id(),
                ]);

                $payroll->computeTotals();
                $payroll->save();

                $createdCount++;
            }
        });

        return redirect()->route('payrolls.index', ['year' => $year, 'month' => $month])
            ->with('success', "{$createdCount} payroll records generated successfully.");
    }

    /**
     * Show professional printable Payslip view.
     */
    public function show(Payroll $payroll)
    {
        $this->authorizePermission('payroll.view');

        $payroll->load(['user.salary', 'creator']);

        return view('payroll.show', [
            'payroll' => $payroll,
        ]);
    }

    /**
     * Generate and download Payslip PDF.
     */
    public function pdf(Payroll $payroll)
    {
        $this->authorizePermission('payroll.view');

        $fontsDir = storage_path('fonts');
        if (!is_dir($fontsDir)) {
            mkdir($fontsDir, 0755, true);
        }

        $payroll->load(['user.salary', 'creator']);

        $pdf = PdfFacade::setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
        ])->loadView('payroll.payslip-pdf', compact('payroll'));

        $filename = $payroll->getPdfFilename();
        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename),
            'Content-Length' => strlen($output),
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    /**
     * Show form to edit an existing payroll record.
     */
    public function edit(Payroll $payroll)
    {
        $this->authorizePermission('payroll.edit');

        $payroll->load(['user.salary']);

        return view('payroll.edit', [
            'payroll' => $payroll,
        ]);
    }

    /**
     * Update an existing payroll record.
     */
    public function update(Request $request, Payroll $payroll, PayrollEmailService $payslips)
    {
        $this->authorizePermission('payroll.edit');

        $validated = $request->validate([
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'hra' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'overtime_pay' => ['nullable', 'numeric', 'min:0'],
            'provident_fund' => ['nullable', 'numeric', 'min:0'],
            'professional_tax' => ['nullable', 'numeric', 'min:0'],
            'tds' => ['nullable', 'numeric', 'min:0'],
            'other_deductions' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'string', 'in:pending,processed,paid'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Read before the fill: only a move *into* paid is worth mailing. Saving
        // a correction on an already-paid payroll must not re-send the slip.
        $wasPaid = $payroll->payment_status === 'paid';

        $payroll->fill($validated);

        // A record can be flipped to paid without a payment date; the payslip
        // would otherwise read "pending disbursement" while saying it was paid.
        if ($payroll->payment_status === 'paid' && ! $payroll->payment_date) {
            $payroll->payment_date = now()->toDateString();
        }

        $payroll->computeTotals();
        $payroll->save();

        $message = "Payroll record {$payroll->payroll_code} updated successfully.";

        if (! $wasPaid && $payroll->payment_status === 'paid') {
            $message = $this->markPaidThenEmail($payslips, $payroll);
        }

        return redirect()->route('payrolls.show', $payroll)->with('success', $message);
    }

    /**
     * Delete a payroll record.
     */
    public function destroy(Payroll $payroll)
    {
        $this->authorizePermission('payroll.delete');

        $code = $payroll->payroll_code;
        $payroll->delete();

        return redirect()->route('payrolls.index')
            ->with('success', "Payroll record {$code} deleted.");
    }

    /**
     * Quick mark as paid action.
     *
     * Marking a payroll paid is the moment the employee is actually made whole,
     * so the payslip goes out with it. Sending is queued, and a payroll is
     * never left unpaid because a mail host was slow.
     */
    public function markAsPaid(Request $request, Payroll $payroll, PayrollEmailService $payslips)
    {
        $this->authorizePermission('payroll.edit');

        $payroll->payment_status = 'paid';
        $payroll->payment_date = $request->input('payment_date') ?: now()->toDateString();
        if ($ref = $request->string('transaction_reference')->trim()->value()) {
            $payroll->transaction_reference = $ref;
        }
        $payroll->save();

        return back()->with('success', $this->markPaidThenEmail($payslips, $payroll));
    }

    /**
     * Queue the payslip and phrase the flash message around what happened.
     *
     * Kept out of markAsPaid() because update() shares it, and a shared helper
     * is what stops the two paths from drifting apart on wording and on
     * whether a missing address is an error or a warning.
     */
    private function markPaidThenEmail(PayrollEmailService $payslips, Payroll $payroll): string
    {
        try {
            $log = $payslips->queuePayslip($payroll, true, auth()->id());
        } catch (\Throwable $e) {
            // The payroll is already marked paid; do not undo that over mail.
            report($e);

            return "Payroll {$payroll->payroll_code} marked as paid, but the payslip email could not be queued: {$e->getMessage()}";
        }

        if ($log === null) {
            return "Payroll {$payroll->payroll_code} marked as paid. No email was sent: the employee has no email address on file.";
        }

        return "Payroll {$payroll->payroll_code} marked as paid. Payslip queued for {$log->recipient_email}.";
    }
}
