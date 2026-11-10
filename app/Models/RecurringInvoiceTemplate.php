<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use App\Rbac\HasDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class RecurringInvoiceTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\RecurringInvoiceTemplateFactory> */
    use Auditable, HasFactory, HasDataScope;

    /** Audit module label. */
    /** Audit module label. */
    protected $auditModule = 'recurring_invoices';

        protected $fillable = [
        'name',
        'customer_id',
        'brand',
        'frequency',
        'custom_interval_days',
        'start_date',
        'end_date',
        'next_generation_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'payment_terms',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_generation_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the customer for this template
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all items for this template
     */
    public function items(): HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class, 'recurring_template_id')->orderBy('item_order');
    }

    /**
     * Get all invoices generated from this template
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'recurring_template_id');
    }

    /**
     * Calculate next generation date based on frequency
     */
    public function calculateNextGenerationDate(Carbon $fromDate = null): Carbon
    {
        $fromDate = $fromDate ?: Carbon::now();

        switch ($this->frequency) {
            case 'weekly':
                return $fromDate->copy()->addWeek();
            case 'monthly':
                return $fromDate->copy()->addMonth();
            case 'yearly':
                return $fromDate->copy()->addYear();
            case 'custom':
                return $fromDate->copy()->addDays($this->custom_interval_days ?? 30);
            default:
                return $fromDate->copy()->addMonth();
        }
    }

    /**
     * Check if invoice should be generated today
     */
    public function shouldGenerate(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check if start date has been reached
        if ($this->start_date > Carbon::now()->toDateString()) {
            return false;
        }

        // Check if end date has been reached
        if ($this->end_date && $this->end_date < Carbon::now()->toDateString()) {
            return false;
        }

        // Check if next generation date is today or earlier
        return $this->next_generation_date <= Carbon::now()->toDateString();
    }

    /**
     * Scope to filter active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
