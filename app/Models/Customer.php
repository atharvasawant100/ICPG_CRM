<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use App\Rbac\HasDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use Auditable, HasFactory, HasDataScope;

    /** Audit module label. */
    protected $auditModule = 'customers';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'address',
        'status',
        'created_by',
    ];

    /**
     * Get the user that created this customer
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get leads converted to this customer
     */
    public function convertedLeads()
    {
        return $this->hasMany(Lead::class, 'converted_to_customer_id');
    }

    /**
     * Get invoices for this customer
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get recurring invoice templates for this customer
     */
    public function recurringInvoiceTemplates()
    {
        return $this->hasMany(RecurringInvoiceTemplate::class);
    }
}
