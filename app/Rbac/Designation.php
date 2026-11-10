<?php

namespace App\Rbac;

use App\Models\Department;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Turns a (department, designation) pair into the Spatie role that actually
 * decides what the user can reach.
 *
 * The Add User form has no Role field, so this is the single place where a
 * designation becomes an access level. Keeping it out of the controller means
 * the same answer is produced whether the user came from the form, a console
 * command, or a test.
 */
final class Designation
{
    /**
     * The designations offered for a department.
     *
     * A department with no list of its own, or no department chosen yet,
     * falls back to the default list so the dropdown is never empty.
     *
     * @return array<string, string> slug => label
     */
    public static function forDepartment(?string $department): array
    {
        if ($department !== null && $department !== '') {
            $normalized = strtolower(trim($department));

            // Check if department model in database has customized designations
            try {
                $deptModel = Department::query()->where('name', $normalized)->first();
                if ($deptModel && ! empty($deptModel->designations)) {
                    return $deptModel->parsedDesignations();
                }
            } catch (\Throwable) {
                // Table might not exist during early migrations
            }

            $departments = (array) config('designations.departments', []);
            if (isset($departments[$department])) {
                return (array) $departments[$department];
            }

            foreach ($departments as $key => $values) {
                if (strtolower(trim((string) $key)) === $normalized) {
                    return (array) $values;
                }
            }
        }

        return (array) config('designations.default', []);
    }

    /**
     * Every department's designations, keyed by department name.
     *
     * Used to drive the dependent dropdown in one round trip.
     *
     * @return array<string, array<string, string>>
     */
    public static function map(): array
    {
        $map = [];

        foreach (Department::names() as $name) {
            $options = self::forDepartment($name);
            $map[$name] = $options;
            $map[strtolower(trim($name))] = $options;
        }

        foreach (array_keys((array) config('designations.departments', [])) as $key) {
            $options = self::forDepartment((string) $key);
            $map[$key] = $options;
            $map[strtolower(trim((string) $key))] = $options;
        }

        return $map;
    }

    /**
     * Is this designation offered for this department?
     */
    public static function isValid(?string $department, ?string $designation): bool
    {
        if ($designation === null || $designation === '') {
            return false;
        }

        return array_key_exists(self::slug($designation), self::forDepartment($department));
    }

    /**
     * The Spatie role a (department, designation) pair resolves to.
     *
     * A designation listed in config/designations.php `elevates_to` wins.
     * Otherwise the department names the role, so a user in "development" gets
     * the development role whatever their title.
     */
    public static function roleFor(?string $department, ?string $designation): string
    {
        $slug = self::slug($designation);

        $elevates = (array) config('designations.elevates_to', []);

        if ($slug !== '') {
            if (isset($elevates[$slug])) {
                return (string) $elevates[$slug];
            }

            if ($slug === 'manager' || str_contains($slug, 'manager')) {
                if ($department !== null && $department !== '') {
                    $deptKey = strtolower(trim($department));
                    if (isset(Rbac::DEPARTMENT_MANAGERS[$deptKey])) {
                        return Rbac::DEPARTMENT_MANAGERS[$deptKey];
                    }
                    $candidate = "{$deptKey}_manager";
                    if (self::roleExists($candidate)) {
                        return $candidate;
                    }
                }

                return Rbac::MANAGER_ROLE;
            }
        }

        return self::departmentRole($department);
    }

    /**
     * The employee role for a department.
     *
     * Departments are editable, so a department may not have a role of the
     * same name. In that case the configured fallback is used rather than
     * inventing a role, and the form warns about it.
     */
    public static function departmentRole(?string $department): string
    {
        $fallback = (string) config('designations.fallback_role', Rbac::ADMIN_ROLE);

        if ($department === null || $department === '') {
            return $fallback;
        }

        foreach (self::roleCandidates($department) as $candidate) {
            if (self::roleExists($candidate)) {
                return $candidate;
            }
        }

        return $fallback;
    }

    /**
     * Does this department have a role that matches it?
     *
     * False means the department will fall back, which the form surfaces so an
     * admin is not surprised by a user's access level.
     */
    public static function departmentHasOwnRole(?string $department): bool
    {
        if ($department === null || $department === '') {
            return false;
        }

        foreach (self::roleCandidates($department) as $candidate) {
            if (self::roleExists($candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalise a designation for storage and comparison.
     */
    public static function slug(?string $designation): string
    {
        return Str::of((string) $designation)->trim()->squish()->lower()->value();
    }

    /**
     * The stored label for a designation slug, or the original text when it is
     * not in the list (an older value, or one since removed).
     */
    public static function label(?string $designation): ?string
    {
        $slug = self::slug($designation);

        if ($slug === '') {
            return null;
        }

        $all = array_merge(
            (array) config('designations.default', []),
            ...array_values((array) config('designations.departments', []))
        );

        if (isset($all[$slug])) {
            return $all[$slug];
        }

        // Check if any department in DB defines this designation
        try {
            foreach (Department::all() as $dept) {
                $parsed = $dept->parsedDesignations();
                if (isset($parsed[$slug])) {
                    return $parsed[$slug];
                }
            }
        } catch (\Throwable) {
        }

        return (string) $designation;
    }

    /**
     * The role names a department could map to, most specific first.
     *
     * The department's own name is tried before the alias in Rbac, so a
     * department called "sales" resolves to the sales role either way.
     *
     * @return list<string>
     */
    private static function roleCandidates(string $department): array
    {
        return array_values(array_unique(array_filter([
            $department,
            Rbac::EMPLOYEE_ROLES[$department] ?? null,
        ])));
    }

    /**
     * Does a Spatie role of this name exist?
     */
    private static function roleExists(string $name): bool
    {
        return SpatieRole::query()
            ->where('name', $name)
            ->where('guard_name', Rbac::GUARD)
            ->exists();
    }
}