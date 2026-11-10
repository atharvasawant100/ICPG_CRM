<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'client_id',
        'name',
        'slug',
        'logo_path',
        'website',
        'industry',
        'description',
        'color',
        'primary_color',
        'is_active',
        'created_by',
    ];

    public function setClientIdAttribute($value): void
    {
        $this->attributes['customer_id'] = $value;
    }

    public function getClientIdAttribute()
    {
        return $this->customer_id;
    }

    public function setPrimaryColorAttribute($value): void
    {
        $this->attributes['color'] = $value;
    }

    public function getPrimaryColorAttribute()
    {
        return $this->color ?? '#3b82f6';
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($brand) {
            if (empty($brand->slug) && ! empty($brand->name)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    /**
     * The client (customer) owning this brand.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Backwards-compatible alias for client relationship.
     */
    public function customer(): BelongsTo
    {
        return $this->client();
    }

    /**
     * Social media accounts connected to this brand.
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class)->where('is_active', true);
    }

    /**
     * All social accounts including inactive ones.
     */
    public function allSocialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Content posts created under this brand.
     */
    public function contentPosts(): HasMany
    {
        return $this->hasMany(ContentPost::class)->latest('id');
    }

    /**
     * The user who created this brand record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Color styling configuration for UI cards & tags.
     */
    public function colorConfig(): array
    {
        $color = $this->color ?: 'indigo';
        return match ($color) {
            'rose', 'red' => [
                'badge' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                'dot' => 'bg-rose-500',
                'border' => 'border-rose-400',
            ],
            'emerald', 'green' => [
                'badge' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'dot' => 'bg-emerald-500',
                'border' => 'border-emerald-400',
            ],
            'amber', 'orange' => [
                'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                'dot' => 'bg-amber-500',
                'border' => 'border-amber-400',
            ],
            'purple', 'violet' => [
                'badge' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                'dot' => 'bg-purple-500',
                'border' => 'border-purple-400',
            ],
            'sky', 'blue' => [
                'badge' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300 border-sky-200 dark:border-sky-800',
                'dot' => 'bg-sky-500',
                'border' => 'border-sky-400',
            ],
            default => [
                'badge' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
                'dot' => 'bg-indigo-500',
                'border' => 'border-indigo-400',
            ],
        };
    }
}
