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
        $this->authorize('viewAny', Invoice::class);

        $user = Auth::user();

        $query = Invoice::with(['customer', 'creator'])->withCount('payments');

        // Data scope. No-op for the admin.
        $query->applyDataScope($user, 'invoices');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
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

        $this->authorize('create', Invoice::class);

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

        $this->authorize('create', Invoice::class);

        $data = $request->validated();

        // Customer check
        $customer = Customer::find($data['customer_id']);
        if (!$customer) {
            return back()->withInput()
                ->with('error', 'Customer not found. Please select a valid customer.');
        }

        // Generate invoice number
        $invoiceNumber = InvoiceNumberGenerator::generate();

        // Calculate totals authoritatively
        $items = $data['items'] ?? [];
        $subtotal = 0;
        foreach ($items as $item) {
            $itemTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $subtotal += $itemTotal;
        }
        $data['subtotal'] = $subtotal;
        $data['tax_amount'] = (float) ($data['tax_amount'] ?? 0);
        $data['discount_amount'] = (float) ($data['discount_amount'] ?? 0);
        $data['total_amount'] = max(0, $subtotal + $data['tax_amount'] - $data['discount_amount']);

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $data['customer_id'],
                'brand' => $data['brand'] ?? null,
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
                    'brand' => $data['brand'] ?? null,
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

        $this->authorize('view', $invoice);

        // Removed department permission check

        $invoice->load(['customer', 'creator', 'items.service', 'payments.creator']); // Removed department from load

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {

        $this->authorize('update', $invoice);

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

        $this->authorize('update', $invoice);

        // Removed department permission check

        // Keep paid/partial/cancelled accounting records immutable.
        if (! in_array($invoice->status, ['draft', 'sent'], true) || $invoice->payments()->exists()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Only draft or unpaid sent invoices can be edited.');
        }

        $data = $request->validated();

        // Read before the update: deciding whether this is an "approval" needs
        // to know where the invoice came from, not where it ends up.
        $wasAlreadySent = $invoice->status !== 'draft';

        // Calculate totals authoritatively
        $items = $data['items'] ?? [];
        $subtotal = 0;
        foreach ($items as $item) {
            $itemTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $subtotal += $itemTotal;
        }
        $data['subtotal'] = $subtotal;
        $data['tax_amount'] = (float) ($data['tax_amount'] ?? 0);
        $data['discount_amount'] = (float) ($data['discount_amount'] ?? 0);
        $data['total_amount'] = max(0, $subtotal + $data['tax_amount'] - $data['discount_amount']);

        DB::beginTransaction();
        try {
            $invoice->update([
                'customer_id' => $data['customer_id'],
                'brand' => $data['brand'] ?? null,
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

            /*
             | Moving an invoice out of draft is an approval, and an approval
             | means the customer gets the invoice. Queued rather than sent
             | inline so a slow SMTP host cannot roll back the edit the user
             | just made — a failure shows up on the Email Log page instead.
             */
            $emailQueued = false;

            if (! $wasAlreadySent && $invoice->status === 'sent') {
                try {
                    $emailQueued = (new InvoiceEmailService)->queueInvoice($invoice, true, Auth::id()) !== null;
                } catch (\Throwable $e) {
                    // The edit already committed; do not pretend it failed.
                    report($e);
                }
            }

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.' . ($emailQueued ? ' Invoice email queued for the customer.' : ''));
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

        $this->authorize('delete', $invoice);

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

        $this->authorize('send', $invoice);

        // Removed department permission check

        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            return back()->with('error', 'Cannot send invoice with status: ' . $invoice->status);
        }

        /*
         | Sending an invoice now emails it by default. The only way to skip
         | the email is to say so explicitly (send_email=0), which is what the
         | "Mark as Sent" button posts.
         */
        $shouldSendEmail = $request->boolean('send_email', true);

        // Mark as sent if needed (but still allow re-sending emails anytime)
        if ($invoice->status === 'draft') {
            $invoice->status = 'sent';
            $invoice->save();
        }

        if (! $shouldSendEmail) {
            return back()->with('success', 'Invoice marked as sent. No email was sent.');
        }

        try {
            $log = (new InvoiceEmailService)->queueInvoice($invoice, true, Auth::id());
        } catch (\Throwable $e) {
            $errMsg = $e->getMessage();

            return back()
                ->with('error', 'Email could not be queued: ' . $errMsg)
                ->with('invoice_email_popup', ['type' => 'error', 'message' => 'Email could not be queued: ' . $errMsg]);
        }

        // No address on file is a data problem, not a crash: the invoice is
        // still marked sent and the user is told exactly why nothing arrived.
        if ($log === null) {
            return back()
                ->with('error', 'Invoice marked as sent, but no email was sent: the customer has no email address.')
                ->with('invoice_email_popup', ['type' => 'error', 'message' => 'Customer has no email address on file.']);
        }

        return back()
            ->with('success', 'Invoice marked as sent. Email queued for ' . $log->recipient_email . '.')
            ->with('invoice_email_popup', [
                'type' => 'success',
                'message' => 'Invoice email queued for ' . $log->recipient_email . '. Delivery happens in the background.',
            ]);
    }

    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice)
    {

        $this->authorize('cancel', $invoice);

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

        $this->authorize('pdf', $invoice);

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

        $filename = $invoice->getPdfFilename();
        $output = $pdf->output();

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename),
            'Content-Length' => strlen($output),
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}
