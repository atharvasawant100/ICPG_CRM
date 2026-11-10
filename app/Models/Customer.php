<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use App\Rbac\HasDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'brands',
        'address',
        'status',
        'designated_person',
        'created_by',
        'assigned_to',
    ];

    /**
     * Get brands as a clean array of trimmed strings
     */
    public function getBrandsListAttribute(): array
    {
        $raw = $this->brands;
        if (empty($raw)) {
            return !empty($this->company) ? [trim($this->company)] : [];
        }

        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw)));
        }

        $decoded = json_decode((string) $raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded)));
        }

        $parts = preg_split('/[,;\n]+/', (string) $raw);
        return array_values(array_filter(array_map('trim', $parts)));
    }

    /**
     * Get the user assigned as the designated person for this customer
     */
    public function assignedPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Helper to get designated contact person (falls back to creator)
     */
    public function designatedPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to')->withDefault(function ($user, $customer) {
            return $customer->creator;
        });
    }

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
     * Get the brand records belonging to this client.
     */
    public function brandsRelation(): HasMany
    {
        return $this->hasMany(Brand::class, 'customer_id');
    }

    /**
     * Get content posts for this client.
     */
    public function contentPosts(): HasMany
    {
        return $this->hasMany(ContentPost::class, 'client_id');
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
