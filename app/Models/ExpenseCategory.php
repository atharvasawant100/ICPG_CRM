<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use App\Rbac\HasDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseCategoryFactory> */
    use Auditable, HasFactory, HasDataScope;

    /** Audit module label. */
    /** Audit module label. */
    protected $auditModule = 'expense_categories';

        protected $fillable = [
        'name',
        'description',
        'is_custom',
        'created_by',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
    ];

    /**
     * Get the user who created this category
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all expenses in this category
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
