<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
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
