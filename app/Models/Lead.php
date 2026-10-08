<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use App\Rbac\HasDataScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use Auditable, HasFactory, HasDataScope;

    /** Audit module label. */
    protected $auditModule = 'leads';
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'source',
        'status',
        'extra_fields',
        'created_by',
        'converted_to_customer_id',
    ];

    protected $casts = [
        'extra_fields' => 'array',
    ];

    /**
     * Extra fields with Meta-style slug values converted to readable text.
     *
     * @return array<string, string>
     */
    public function getFormattedExtraFieldsAttribute(): array
    {
        $fields = $this->extra_fields ?? [];
        if (! is_array($fields) || $fields === []) {
            return [];
        }

        $formatted = [];
        foreach ($fields as $label => $value) {
            $formatted[$label] = self::humanizeExtraValue($value);
        }

        return $formatted;
    }

    /**
     * Convert Meta lead export slug values into readable text.
     */
    public static function humanizeExtraValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        // Fix accidental spaces in thousand separators: "25, 000" → "25,000"
        $value = preg_replace('/(\d),\s+(\d)/', '$1,$2', $value) ?? $value;

        $boolMap = [
            'true' => 'Yes',
            'false' => 'No',
            'yes' => 'Yes',
            'no' => 'No',
            '1' => 'Yes',
            '0' => 'No',
        ];

        $lower = strtolower($value);
        if (isset($boolMap[$lower])) {
            return $boolMap[$lower];
        }

        // Keep emails, URLs, and @handles as-is
        if (
            filter_var($value, FILTER_VALIDATE_EMAIL)
            || preg_match('#^(https?://|www\.)#i', $value)
            || str_starts_with($value, '@')
        ) {
            return $value;
        }

        // Already readable (no underscores / slug dashes)
        if (! str_contains($value, '_') && ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)+$/i', $value)) {
            return $value;
        }

        $value = str_replace('_', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        $value = preg_replace('/\(\s+/', '(', $value) ?? $value;
        $value = preg_replace('/\s+\)/', ')', $value) ?? $value;
        // Space after comma only when followed by a letter (not thousand separators like 25,000)
        $value = preg_replace('/,\s*(?=[A-Za-z])/', ', ', $value) ?? $value;
        $value = preg_replace('/\s*-\s*/', ' - ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        $value = trim($value);

        // Title-case words, keep numbers/punctuation intact
        $parts = preg_split('/(\s+|-|\(|\))/', $value, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$value];
        $result = '';
        foreach ($parts as $part) {
            if ($part === '' || preg_match('/^(\s+|-|\(|\))$/', $part)) {
                $result .= $part;
                continue;
            }

            if (preg_match('/^\d/', $part)) {
                $result .= $part;
                continue;
            }

            // Preserve & and similar short tokens
            if (in_array($part, ['&', '/', '|'], true)) {
                $result .= $part;
                continue;
            }

            $words = explode(' ', $part);
            $cased = [];
            foreach ($words as $word) {
                if ($word === '') {
                    continue;
                }
                if (preg_match('/^\d/', $word) || in_array($word, ['&', '/', '|'], true)) {
                    $cased[] = $word;
                    continue;
                }
                $cased[] = ucwords(strtolower($word));
            }
            $result .= implode(' ', $cased);
        }

        // Normalize " - " after split (dashes were captured alone)
        $result = preg_replace('/\s*-\s*/', ' - ', $result) ?? $result;
        $result = preg_replace('/\s+/', ' ', $result) ?? $result;

        return trim($result);
    }

    /**
     * Get the user that created this lead
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all activities for this lead
     */
    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    /**
     * Get all notes for this lead
     */
    public function notes()
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    /**
     * Get public notes (visible to all)
     */
    public function publicNotes()
    {
        return $this->hasMany(LeadNote::class)->where('is_internal', false)->latest();
    }

    /**
     * Get internal notes (admin only)
     */
    public function internalNotes()
    {
        return $this->hasMany(LeadNote::class)->where('is_internal', true)->latest();
    }

    /**
     * Scope to filter leads by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter leads by source
     */
    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Get all follow-ups for this lead
     */
    public function followups()
    {
        return $this->hasMany(LeadFollowup::class)->orderBy('followup_date');
    }

    /**
     * Get pending follow-ups
     */
    public function pendingFollowups()
    {
        return $this->hasMany(LeadFollowup::class)->where('status', 'pending')->orderBy('followup_date');
    }

    /**
     * Get the customer this lead was converted to
     */
    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_to_customer_id');
    }

    /**
     * Check if lead is converted
     */
    public function isConverted(): bool
    {
        return !is_null($this->converted_to_customer_id);
    }

    /**
     * Auto-update status based on activities
     */
    public function autoUpdateStatus(?int $userId = null): void
    {
        // Don't auto-update if already converted or lost
        if ($this->status === 'converted' || $this->status === 'lost') {
            return;
        }

        $userId = $userId ?? auth()->id() ?? $this->created_by;

        $completedFollowups = $this->followups()->where('status', 'completed')->count();
        $notesCount = $this->notes()->where('is_internal', false)->count();
        $activitiesCount = $this->activities()->count();

        // Auto-progress from "new" to "contacted"
        if ($this->status === 'new') {
            if ($completedFollowups > 0 || $notesCount > 0 || $activitiesCount > 1) {
                $oldStatus = $this->status;
                $this->update(['status' => 'contacted']);
                \App\Models\LeadActivity::create([
                    'lead_id' => $this->id,
                    'user_id' => $userId,
                    'activity_type' => 'status_changed',
                    'description' => 'Status automatically updated: New → Contacted (first interaction)',
                    'old_value' => $oldStatus,
                    'new_value' => 'contacted',
                ]);
            }
        }
        // Auto-progress from "contacted" to "qualified"
        elseif ($this->status === 'contacted') {
            if ($completedFollowups >= 2 || ($notesCount >= 2 && $completedFollowups >= 1)) {
                $oldStatus = $this->status;
                $this->update(['status' => 'qualified']);
                \App\Models\LeadActivity::create([
                    'lead_id' => $this->id,
                    'user_id' => $userId,
                    'activity_type' => 'status_changed',
                    'description' => 'Status automatically updated: Contacted → Qualified (sufficient engagement)',
                    'old_value' => $oldStatus,
                    'new_value' => 'qualified',
                ]);
            }
        }
    }
}
