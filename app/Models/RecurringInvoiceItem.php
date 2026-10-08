<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringInvoiceItem extends Model
{
    protected $fillable = [
        'recurring_template_id',
        'service_id',
        'description',
        'quantity',
        'unit_price',
        'total',
        'item_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the recurring template this item belongs to
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoiceTemplate::class, 'recurring_template_id');
    }
}
