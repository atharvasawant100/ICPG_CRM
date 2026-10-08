<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoicePaymentRequest;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoicePaymentController extends Controller
{
    /**
     * Store a newly created payment
     */
    public function store(StoreInvoicePaymentRequest $request, Invoice $invoice)
    {
        $this->authorize('recordPayment', $invoice);
        if ($invoice->status === 'cancelled') {
            return back()->with('error', 'Cannot add payment to a cancelled invoice.');
        }

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot add payment to an already paid invoice.');
        }

        $data = $request->validated();
        
        // Check for overpayment
        $remainingAmount = $invoice->getRemainingAmount();
        if ($data['amount'] > $remainingAmount) {
            return back()->withInput()
                ->with('error', 'Payment amount (' . number_format($data['amount'], 2) . ') exceeds remaining amount (' . number_format($remainingAmount, 2) . ').');
        }

        DB::beginTransaction();
        try {
            $payment = InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Update invoice status based on payments (with lock to prevent race condition)
            $invoice->refresh();
            $invoice->updateStatus();

            DB::commit();

            return back()->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified payment
     */
    public function destroy(InvoicePayment $payment)
    {
        $this->authorize('deletePayment', $payment);
        $invoice = $payment->invoice;

        try {
            $payment->delete();
            
            // Update invoice status after payment deletion
            $invoice->updateStatus();
            
            return back()->with('success', 'Payment deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }
}
