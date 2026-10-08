<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Service;
use App\Models\RecurringInvoiceTemplate;
use App\Models\RecurringInvoiceItem;
use App\Services\InvoiceNumberGenerator;
use App\Services\InvoiceEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Invoice::with(['customer', 'creator'])->withCount('payments');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->where('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest('invoice_date')->paginate(8);

        // Get customers for filter dropdown
        $customers = Customer::orderBy('name')->get();

        // Return empty collection for departments as it is removed
        $departments = collect();

        return view('invoices.index', compact('invoices', 'customers', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $services = Service::active()->orderBy('name')->get();

        $departments = collect();
        $defaultDepartmentId = null;

        return view('invoices.create', compact('customers', 'services', 'departments', 'defaultDepartmentId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        $data = $request->validated();

        // Customer check
        $customer = Customer::find($data['customer_id']);
        if (!$customer) {
            return back()->withInput()
                ->with('error', 'Customer not found. Please select a valid customer.');
        }

        // Generate invoice number
        $invoiceNumber = InvoiceNumberGenerator::generate();

        // Calculate totals if not provided
        $items = $data['items'] ?? [];
        if (!isset($data['subtotal']) || !isset($data['total_amount'])) {
            $subtotal = 0;
            foreach ($items as $item) {
                $itemTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                $subtotal += $itemTotal;
            }
            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $data['tax_amount'] ?? 0;
            $data['discount_amount'] = $data['discount_amount'] ?? 0;
            $data['total_amount'] = $subtotal + $data['tax_amount'] - $data['discount_amount'];
        }

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $data['customer_id'],
                // 'department_id' => null, // Removed
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'subtotal' => $data['subtotal'],
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'total_amount' => $data['total_amount'],
                'status' => $data['status'] ?? 'draft',
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Create invoice items
            foreach ($items as $index => $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'item_order' => $index + 1,
                ]);
            }

            // Create recurring invoice template if requested
            $recurringTemplate = null;
            if ($request->has('create_recurring') && $request->create_recurring == '1') {
                $frequency = $request->input('recurring_frequency');
                $startDate = Carbon::parse($request->input('recurring_start_date'));
                $endDate = $request->input('recurring_end_date') ? Carbon::parse($request->input('recurring_end_date')) : null;
                $customIntervalDays = $request->input('recurring_custom_interval_days');

                // Calculate next generation date
                $tempTemplate = new RecurringInvoiceTemplate();
                $tempTemplate->frequency = $frequency;
                $tempTemplate->custom_interval_days = ($frequency === 'custom') ? $customIntervalDays : null;
                $nextGenerationDate = $tempTemplate->calculateNextGenerationDate($startDate);

                // Generate template name
                $customerName = $customer->name;
                $templateName = "Recurring Invoice - {$customerName} - " . ucfirst($frequency);

                $recurringTemplate = RecurringInvoiceTemplate::create([
                    'name' => $templateName,
                    'customer_id' => $data['customer_id'],
                    // 'department_id' => null, // Removed
                    'frequency' => $frequency,
                    'custom_interval_days' => ($frequency === 'custom') ? $customIntervalDays : null,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate ? $endDate->toDateString() : null,
                    'next_generation_date' => $nextGenerationDate->toDateString(),
                    'subtotal' => $data['subtotal'],
                    'tax_amount' => $data['tax_amount'] ?? 0,
                    'discount_amount' => $data['discount_amount'] ?? 0,
                    'payment_terms' => $data['payment_terms'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                ]);

                // Create recurring template items
                foreach ($items as $index => $item) {
                    RecurringInvoiceItem::create([
                        'recurring_template_id' => $recurringTemplate->id,
                        'service_id' => $item['service_id'] ?? null,
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total' => $item['quantity'] * $item['unit_price'],
                        'item_order' => $index + 1,
                    ]);
                }

                // Link invoice to template
                $invoice->update([
                    'is_recurring' => true,
                    'recurring_template_id' => $recurringTemplate->id,
                ]);
            }

            DB::commit();

            $successMessage = 'Invoice created successfully.';
            if ($recurringTemplate) {
                $successMessage .= ' Recurring invoice template has also been created.';
            }

            return redirect()->route('invoices.show', $invoice)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        // Removed department permission check

        $invoice->load(['customer', 'creator', 'items.service', 'payments.creator']); // Removed department from load

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        // Removed department permission check

        // Draft and sent invoices without payments can be edited.
        if (! in_array($invoice->status, ['draft', 'sent'], true) || $invoice->payments()->exists()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft or unpaid sent invoices can be edited.');
        }

        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $services = Service::active()->orderBy('name')->get();

        $departments = collect(); // Removed departments

        $invoice->load(['customer', 'items.service']);

        return view('invoices.edit', compact('invoice', 'customers', 'services', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        // Removed department permission check

        // Keep paid/partial/cancelled accounting records immutable.
        if (! in_array($invoice->status, ['draft', 'sent'], true) || $invoice->payments()->exists()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft or unpaid sent invoices can be edited.');
        }

        $data = $request->validated();

        // Calculate totals if not provided
        $items = $data['items'] ?? [];
        if (!isset($data['subtotal']) || !isset($data['total_amount'])) {
            $subtotal = 0;
            foreach ($items as $item) {
                $itemTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                $subtotal += $itemTotal;
            }
            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $data['tax_amount'] ?? 0;
            $data['discount_amount'] = $data['discount_amount'] ?? 0;
            $data['total_amount'] = $subtotal + $data['tax_amount'] - $data['discount_amount'];
        }

        DB::beginTransaction();
        try {
            $invoice->update([
                'customer_id' => $data['customer_id'],
                // 'department_id' => $data['department_id'], // Removed
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'subtotal' => $data['subtotal'],
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'total_amount' => $data['total_amount'],
                'status' => $data['status'] ?? 'draft',
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Store existing items temporarily before deletion
            $existingItems = $invoice->items()->get();

            // Delete existing items
            $invoice->items()->delete();

            // Create new items
            try {
                foreach ($items as $index => $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'service_id' => $item['service_id'] ?? null,
                        'description' => $item['description'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total' => $item['quantity'] * $item['unit_price'],
                        'item_order' => $index + 1,
                    ]);
                }
            } catch (\Exception $e) {
                // Restore items if creation fails
                foreach ($existingItems as $existingItem) {
                    InvoiceItem::create($existingItem->toArray());
                }
                throw $e;
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // Removed department permission check

        // Only draft invoices can be deleted
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be deleted.');
        }

        try {
            $invoice->delete();
            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    /**
     * Send invoice (mark as sent)
     */
    public function send(Request $request, Invoice $invoice)
    {
        // Removed department permission check

        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            return back()->with('error', 'Cannot send invoice with status: ' . $invoice->status);
        }

        $shouldSendEmail = $request->has('send_email') && $request->boolean('send_email');

        // Mark as sent if needed (but still allow re-sending emails anytime)
        if ($invoice->status === 'draft') {
            $invoice->status = 'sent';
            $invoice->save();
        }

        // Send email if requested
        if ($shouldSendEmail) {
            try {
                $emailService = new InvoiceEmailService();
                $emailService->sendInvoice($invoice, true);

                // Track email sends (count + latest timestamp)
                $invoice->forceFill([
                    'email_sent_at' => now(),
                    'email_sent_count' => (int) ($invoice->email_sent_count ?? 0) + 1,
                ])->save();

                return back()
                    ->with('success', 'Invoice email sent to customer.')
                    ->with('invoice_email_popup', ['type' => 'success', 'message' => 'Email sent successfully to customer.']);
            } catch (\Throwable $e) {
                $errMsg = $e->getMessage();
                return back()
                    ->with('error', 'Email failed: ' . $errMsg)
                    ->with('invoice_email_popup', ['type' => 'error', 'message' => 'Email failed: ' . $errMsg]);
            }
        }

        // Mark as sent button (no email)
        if ($invoice->status !== 'sent') {
            $invoice->status = 'sent';
            $invoice->save();
        }

        return back()->with('success', 'Invoice marked as sent.');
    }

    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice)
    {
        // Removed department permission check

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot cancel a paid invoice.');
        }

        $invoice->status = 'cancelled';
        $invoice->save();

        return back()->with('success', 'Invoice cancelled successfully.');
    }

    /**
     * Generate and download PDF
     */
    public function pdf(Invoice $invoice)
    {
        // Removed department permission check

        // Ensure storage/fonts directory exists
        $fontsDir = storage_path('fonts');
        if (!is_dir($fontsDir)) {
            mkdir($fontsDir, 0755, true);
        }

        $invoice->load(['customer', 'items.service', 'payments.creator']); // Removed department

        // Configure PDF options before loading view (setOptions expects an array)
        $options = [
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
        ];

        $pdf = PdfFacade::setOptions($options)->loadView('invoices.pdf', compact('invoice'));

        return $pdf->download($invoice->getPdfFilename());
    }
}
