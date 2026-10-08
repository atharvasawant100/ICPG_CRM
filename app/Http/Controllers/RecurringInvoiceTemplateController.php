<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringInvoiceTemplateRequest;
use App\Http\Requests\UpdateRecurringInvoiceTemplateRequest;
use App\Models\RecurringInvoiceTemplate;
use App\Models\RecurringInvoiceItem;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecurringInvoiceTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $this->authorize('viewAny', RecurringInvoiceTemplate::class);

        $templates = RecurringInvoiceTemplate::with(['customer', 'creator'])
            ->applyDataScope($request->user(), 'recurring_invoices')
            ->latest()
            ->paginate(15);

        return view('recurring-invoices.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $this->authorize('create', RecurringInvoiceTemplate::class);

        $customers = Customer::where('status', 'active')->orderBy('name')->get();

        return view('recurring-invoices.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecurringInvoiceTemplateRequest $request)
    {

        $this->authorize('create', RecurringInvoiceTemplate::class);

        $data = $request->validated();

        // Calculate totals if not provided
        $items = $data['items'] ?? [];
        if (!isset($data['subtotal'])) {
            $subtotal = 0;
            foreach ($items as $item) {
                $itemTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                $subtotal += $itemTotal;
            }
            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $data['tax_amount'] ?? 0;
            $data['discount_amount'] = $data['discount_amount'] ?? 0;
        }

        // Calculate next generation date
        $startDate = Carbon::parse($data['start_date']);
        $template = new RecurringInvoiceTemplate();
        $template->frequency = $data['frequency'];
        $template->custom_interval_days = $data['custom_interval_days'] ?? null;
        
        // If start_date is in the past, calculate from today instead
        // to avoid immediate generation on next command run
        $baseDate = $startDate->isPast() ? Carbon::now() : $startDate;
        $nextGenerationDate = $template->calculateNextGenerationDate($baseDate);

        DB::beginTransaction();
        try {
            $template = RecurringInvoiceTemplate::create([
                'name' => $data['name'],
                'customer_id' => $data['customer_id'],
                'frequency' => $data['frequency'],
                'custom_interval_days' => $data['custom_interval_days'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'next_generation_date' => $nextGenerationDate->toDateString(),
                'subtotal' => $data['subtotal'],
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            // Create template items
            foreach ($items as $index => $item) {
                RecurringInvoiceItem::create([
                    'recurring_template_id' => $template->id,
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'item_order' => $index,
                ]);
            }

            DB::commit();

            return redirect()->route('recurring-invoices.show', $template)
                ->with('success', 'Recurring invoice template created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create template: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RecurringInvoiceTemplate $template)
    {

        $this->authorize('view', $template);

        $template->load(['customer', 'creator', 'items', 'invoices']);
        
        return view('recurring-invoices.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RecurringInvoiceTemplate $template)
    {

        $this->authorize('update', $template);

        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $template->load(['items']);

        return view('recurring-invoices.edit', compact('template', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecurringInvoiceTemplateRequest $request, RecurringInvoiceTemplate $template)
    {

        $this->authorize('update', $template);

        $data = $request->validated();

        // Calculate totals if not provided
        $items = $data['items'] ?? [];
        if (!isset($data['subtotal'])) {
            $subtotal = 0;
            foreach ($items as $item) {
                $itemTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                $subtotal += $itemTotal;
            }
            $data['subtotal'] = $subtotal;
            $data['tax_amount'] = $data['tax_amount'] ?? 0;
            $data['discount_amount'] = $data['discount_amount'] ?? 0;
        }

        // Recalculate next generation date if frequency or start_date changed
        if (isset($data['frequency']) || isset($data['start_date'])) {
            $startDate = Carbon::parse($data['start_date'] ?? $template->start_date);
            $tempTemplate = clone $template;
            $tempTemplate->frequency = $data['frequency'] ?? $template->frequency;
            $tempTemplate->custom_interval_days = $data['custom_interval_days'] ?? $template->custom_interval_days;
            $nextGenerationDate = $tempTemplate->calculateNextGenerationDate($startDate);
            $data['next_generation_date'] = $nextGenerationDate->toDateString();
        }

        DB::beginTransaction();
        try {
            $template->update([
                'name' => $data['name'],
                'customer_id' => $data['customer_id'],
                'frequency' => $data['frequency'],
                'custom_interval_days' => $data['custom_interval_days'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'next_generation_date' => $data['next_generation_date'] ?? $template->next_generation_date,
                'subtotal' => $data['subtotal'],
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Delete existing items and create new ones
            $template->items()->delete();
            foreach ($items as $index => $item) {
                RecurringInvoiceItem::create([
                    'recurring_template_id' => $template->id,
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                    'item_order' => $index,
                ]);
            }

            DB::commit();

            return redirect()->route('recurring-invoices.show', $template)
                ->with('success', 'Recurring invoice template updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update template: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringInvoiceTemplate $template)
    {

        $this->authorize('delete', $template);

        try {
            $template->delete();
            return redirect()->route('recurring-invoices.index')
                ->with('success', 'Recurring invoice template deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('recurring-invoices.show', $template)
                ->with('error', 'Failed to delete template: ' . $e->getMessage());
        }
    }

    /**
     * Toggle template active status
     */
    public function toggle(RecurringInvoiceTemplate $template)
    {

        $this->authorize('toggle', $template);

        $template->is_active = !$template->is_active;
        $template->save();

        $message = $template->is_active ? 'Template activated.' : 'Template deactivated.';
        return back()->with('success', $message);
    }
}
