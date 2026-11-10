<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use Auditable, HasFactory;

    protected $auditModule = 'leaves';

    protected $fillable = [
        'name',
        'code',
        'days_allowed_per_year',
        'is_paid',
        'requires_approval',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'days_allowed_per_year' => 'decimal:1',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function balances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
