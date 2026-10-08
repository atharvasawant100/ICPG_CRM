<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Throwable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, \App\Rbac\AssignsRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // 'role' is a read-only mirror of the Spatie role. It is deliberately
        // NOT mass assignable: role changes must go through assignRbacRole().
        'department',
        'manager_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if the user is an administrator.
     *
     * Spatie is the source of truth. The legacy users.role column is only read
     * as a fallback, because isAdmin() runs on every request via
     * EnsureUserIsAdmin and must not throw while the permission tables are
     * absent or a migration is half-applied.
     */
    public function isAdmin(): bool
    {
        try {
            return $this->hasRole(\App\Rbac\Rbac::ADMIN_ROLE);
        } catch (Throwable) {
            return $this->getAttribute('role') === \App\Rbac\Rbac::ADMIN_ROLE;
        }
    }

    /**
     * The user's manager.
     */
    public function manager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    /**
     * Users reporting to this user.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    /**
     * Active users only. Used as the default scope for admin listings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get customers created by this user
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    /**
     * Get leads created by this user
     */
    public function createdLeads()
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    /**
     * Get expenses created by this user
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get expenses approved by this user
     */
    public function approvedExpenses()
    {
        return $this->hasMany(Expense::class, 'approved_by');
    }

    /**
     * Get custom expense categories created by this user
     */
    public function customExpenseCategories()
    {
        return $this->hasMany(ExpenseCategory::class, 'created_by');
    }

    /**
     * Get invoices created by this user
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    /**
     * Get recurring invoice templates created by this user
     */
    public function recurringInvoiceTemplates()
    {
        return $this->hasMany(RecurringInvoiceTemplate::class, 'created_by');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'created_by');
    }
}
