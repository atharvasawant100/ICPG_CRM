<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_id',
        // 'department_id', // Removed
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'email_sent_at',
        'email_sent_count',
        'overdue_reminder_last_sent_at',
        'overdue_reminder_sent_count',
        'payment_terms',
        'notes',
        'is_recurring',
        'recurring_template_id',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'email_sent_at' => 'datetime',
        'email_sent_count' => 'integer',
        'overdue_reminder_last_sent_at' => 'datetime',
        'overdue_reminder_sent_count' => 'integer',
    ];

    /**
     * Get the customer for this invoice
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }



    /**
     * Get the user who created this invoice
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the recurring template for this invoice
     */
    public function recurringTemplate(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoiceTemplate::class, 'recurring_template_id');
    }

    /**
     * Get all items for this invoice
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('item_order');
    }

    /**
     * Get all payments for this invoice
     */
    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderBy('payment_date', 'desc');
    }

    /**
     * Get total paid amount
     */
    public function getPaidAmount(): float
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->total_amount - $this->getPaidAmount());
    }

    /**
     * Get PDF filename in format: invoice-INV-YYYYMMDD-XXXXXX.pdf
     */
    public function getPdfFilename(): string
    {
        return 'invoice-' . $this->invoice_number . '.pdf';
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        if (in_array($this->status, ['paid', 'cancelled'])) {
            return false;
        }
        return $this->due_date < now()->toDateString();
    }

    /**
     * Update invoice status based on payments
     */
    public function updateStatus(): void
    {
        // Use lock to prevent race conditions
        $this->lockForUpdate();
        $this->refresh();

        $paidAmount = $this->getPaidAmount();

        // Don't override cancelled or paid status unless payment changes
        if ($this->status === 'cancelled') {
            return;
        }

        if ($paidAmount <= 0) {
            if ($this->status !== 'sent' && $this->status !== 'draft' && $this->status !== 'cancelled') {
                $this->status = 'sent';
            }
        } elseif ($paidAmount >= $this->total_amount) {
            $this->status = 'paid';
        } else {
            // Only set to partial if not already overdue
            // Overdue takes precedence over partial
            if (!$this->isOverdue()) {
                $this->status = 'partial';
            }
        }

        // Check if overdue - overdue takes precedence over partial
        if ($this->isOverdue() && !in_array($this->status, ['paid', 'cancelled'])) {
            $this->status = 'overdue';
        }

        $this->save();
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }



    /**
     * Scope to get overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
            ->whereNotIn('status', ['paid', 'cancelled']);
    }
}
