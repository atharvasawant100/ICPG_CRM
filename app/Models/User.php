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
use App\Rbac\Concerns\Auditable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Auditable, HasFactory, Notifiable, HasApiTokens, HasRoles, \App\Rbac\AssignsRoles;

    /**
     * Audit module label for this model.
     */
    protected $auditModule = 'users';

    /**
     * Never write these to the audit trail, in any case.
     *
     * @var list<string>
     */
    protected $auditExclude = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'emp_id',
        'name',
        'email',
        'password',
        // 'role' is a read-only mirror of the Spatie role. It is deliberately
        // NOT mass assignable: role changes must go through assignRbacRole().
        'department',
        'designation',
        'manager_id',
        'avatar',
        'is_active',
    ];

    /**
     * Generate the next sequential EMP ID (e.g. ICPG_01, ICPG_02).
     */
    public static function nextEmpId(): string
    {
        $prefix = 'ICPG_';

        $empIds = static::query()
            ->whereNotNull('emp_id')
            ->where('emp_id', 'like', "{$prefix}%")
            ->pluck('emp_id');

        $maxNum = 0;
        foreach ($empIds as $id) {
            if (preg_match('/^ICPG_(\d+)$/i', (string) $id, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;

        return sprintf('%s%02d', $prefix, $nextNum);
    }

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
        if ($this->getAttribute('role') === \App\Rbac\Rbac::ADMIN_ROLE) {
            return true;
        }

        try {
            return $this->hasRole(\App\Rbac\Rbac::ADMIN_ROLE);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Check if the user is in Human Resources (HR role, HR department, or HR designation).
     */
    public function isHr(): bool
    {
        try {
            if ($this->hasRole('hr') || $this->hasRole('hr_manager')) {
                return true;
            }
        } catch (\Throwable) {
        }

        $role = strtolower((string) $this->getAttribute('role'));
        $dept = strtolower((string) $this->department);
        $designation = strtolower((string) $this->designation);

        if ($role === 'hr'
            || $role === 'hr_manager'
            || $dept === 'hr'
            || str_contains($role, 'hr')
            || str_contains($dept, 'hr')
            || str_contains($designation, 'hr')
            || str_contains($designation, 'human resource')) {
            return true;
        }

        try {
            return \App\Models\Department::where('manager_id', $this->id)
                ->where(function ($q) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%hr%'])
                      ->orWhereRaw('LOWER(name) LIKE ?', ['%human resource%']);
                })->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Check if the user belongs to the Sales department.
     */
    public function isSales(): bool
    {
        try {
            if ($this->hasRole('sales') || $this->hasRole('sales_manager')) {
                return true;
            }
        } catch (\Throwable) {
        }

        $role = strtolower((string) $this->getAttribute('role'));
        $dept = strtolower((string) $this->department);
        $designation = strtolower((string) $this->designation);

        if ($role === 'sales'
            || $role === 'sales_manager'
            || $dept === 'sales'
            || str_contains($role, 'sales')
            || str_contains($dept, 'sales')
            || str_contains($designation, 'sales')) {
            return true;
        }

        try {
            return \App\Models\Department::where('manager_id', $this->id)
                ->where(function ($q) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%sales%'])
                      ->orWhereRaw('LOWER(description) LIKE ?', ['%sales%']);
                })->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Determine whether this user participates in tasks workflow.
     * All active employees can create and participate in tasks.
     */
    public function hasTasks(): bool
    {
        return true;
    }

    /**
     * Check if the user is a department manager.
     */
    public function isManager(): bool
    {
        $role = strtolower((string) $this->getAttribute('role'));
        $designation = strtolower((string) $this->designation);

        if ($role === 'manager' || str_ends_with($role, '_manager') || str_contains($designation, 'manager')) {
            return true;
        }

        try {
            if ($this->hasRole(\App\Rbac\Rbac::MANAGER_ROLE) || $this->roles->contains(fn ($r) => str_ends_with($r->name, '_manager'))) {
                return true;
            }
        } catch (\Throwable) {
        }

        try {
            return \App\Models\Department::where('manager_id', $this->id)->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Check if the given operator user can manage/update this user's avatar.
     * Allowed: Admin, HR, and the user themselves.
     */
    public function canManageAvatar(?User $operator): bool
    {
        if (! $operator) {
            return false;
        }

        return $operator->isAdmin()
            || $operator->isHr()
            || (int) $operator->id === (int) $this->id
            || $operator->can('users.edit');
    }

    /**
     * Get avatar public URL if avatar exists.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }

        return null;
    }


    /**
     * The user's manager.
     */
    public function manager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    /**
     * Resolve the department manager for this user.
     * Checks direct manager_id, then department manager_id, then active manager in the department.
     */
    public function departmentManager(): ?User
    {
        if ($this->manager_id && $this->manager) {
            return $this->manager;
        }

        if ($this->department) {
            $dept = Department::whereRaw('LOWER(name) = ?', [strtolower($this->department)])->first();
            if ($dept && $dept->manager_id && $dept->manager) {
                return $dept->manager;
            }

            return self::active()
                ->where('id', '!=', $this->id)
                ->whereRaw('LOWER(department) = ?', [strtolower($this->department)])
                ->get()
                ->first(fn ($u) => $u->isManager());
        }

        return null;
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

    public function salary(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(EmployeeSalary::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_by');
    }

    public function taskWorkLogs(): HasMany
    {
        return $this->hasMany(TaskWorkLog::class);
    }

    public function assignedContentPosts(): HasMany
    {
        return $this->hasMany(ContentPost::class, 'assigned_to');
    }

    public function createdContentPosts(): HasMany
    {
        return $this->hasMany(ContentPost::class, 'created_by');
    }
}
