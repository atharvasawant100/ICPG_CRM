<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use App\Rbac\HasDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use Auditable, HasFactory, HasDataScope;

    /** Audit module label. */
    protected $auditModule = 'services';

    protected $fillable = [
        'name',
        'description',
        'rate',
        'frequency',
        'custom_interval_days',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
        'custom_interval_days' => 'integer',
    ];

    /**
     * Get the user who created the service.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the invoice items that use this service.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get frequency label
     */
    public function getFrequencyLabelAttribute(): string
    {
        $labels = [
            'one-time' => 'One Time',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
            'custom' => 'Custom (' . $this->custom_interval_days . ' days)',
        ];

        return $labels[$this->frequency] ?? ucfirst($this->frequency);
    }
}
