<?php

namespace App\Models;

use App\Rbac\Concerns\Auditable;
use App\Rbac\Rbac;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A department, e.g. Sales.
 *
 * users.department stores this model's name as a string, which keeps the
 * existing column and every existing row untouched while making the list of
 * departments editable from the UI.
 *
 * Deliberately has no data-scope trait: a department is a shared record, not
 * something a user owns.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $manager_id
 * @property bool $is_active
 */
class Department extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentFactory> */
    use Auditable, HasFactory;

    /**
     * Audit module label.
     */
    protected $auditModule = 'departments';

    protected $fillable = [
        'name',
        'description',
        'manager_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Bind by name in the URL rather than by id.
     *
     * Departments are addressed by name everywhere in the UI ("/admin/
     * departments/development"), and the name is the value users.department
     * stores, so it is the key that means something to an admin.
     */
    public function getRouteKeyName(): string
    {
        return 'name';
    }

    /**
     * The user who manages this department.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Users assigned to this department, matched on the name stored in
     * users.department.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department', 'name');
    }

    /**
     * Active users who can be chosen as a department manager.
     *
     * Active admins and managers only, matching the Manager dropdown on the
     * Users page so the two stay consistent.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public static function managerCandidates(?int $exceptUserId = null)
    {
        return User::query()
            ->active()
            ->where(function ($query) {
                $query->whereHas('roles', fn ($roles) => $roles->where('name', Rbac::ADMIN_ROLE))
                    ->orWhereHas('roles', fn ($roles) => $roles->where('name', 'manager'));
            })
            ->when($exceptUserId, fn ($query) => $query->whereKeyNot($exceptUserId))
            ->orderBy('name')
            ->get();
    }

    /**
     * The manager a user joining this department reports to.
     *
     * The Add User form has no Manager field, so this is derived instead of
     * typed. It also keeps the "team" data scope working, which resolves
     * through users.manager_id.
     *
     * Returned raw: whether it happens to be the user being edited is a
     * validation question, answered by the request, so one rule lives in one
     * place.
     */
    public function managerUserId(): ?int
    {
        if ($this->manager_id === null) {
            return null;
        }

        // Users are deactivated, never deleted, so the row is still there.
        return User::query()->whereKey($this->manager_id)->exists()
            ? (int) $this->manager_id
            : null;
    }

    /**
     * Department names for dropdowns, as a flat list for a plain <select>.
     *
     * $activeOnly is false for filter dropdowns so a deactivated department
     * can still be filtered on, and true when assigning a new user.
     *
     * @return list<string>
     */
    public static function names(bool $activeOnly = true): array
    {
        return self::query()
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * Every department name, active or not. Used by validation.
     *
     * @return list<string>
     */
    public static function allNames(): array
    {
        return self::query()->orderBy('name')->pluck('name')->all();
    }
}
