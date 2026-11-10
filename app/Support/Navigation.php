<?php

namespace App\Support;

use App\Models\User;
use App\Rbac\DataScope;

/**
 * Reads config/navigation.php and answers "may this user see this item?".
 *
 * Kept out of the Blade template on purpose: a view that has to build closures
 * and filter collections inline is hard to test and fragile to Blade's
 * compilation. The template only asks questions.
 */
final class Navigation
{
    /**
     * Whether the user holds a permission. A null permission always passes,
     * which is how an item with no permission requirement is expressed.
     */
    public static function can(?User $user, ?string $permission): bool
    {
        if ($permission === null) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        // Tasks module is completely excluded from HR and the Sales Department
        if ($permission === 'tasks.view') {
            if ($user->isHr() || ($user->department && strtolower((string) $user->department) === 'sales')) {
                return false;
            }
            return true;
        }

        // Employee Costing report is exclusive to Company Owner (Admin)
        if ($permission === 'reports.costing') {
            return $user->isAdmin();
        }

        // Manager Performance Tracking report is exclusive to Admin and Department Managers
        if ($permission === 'reports.performance') {
            return $user->isAdmin() || $user->isManager();
        }

        // Team module is exclusive to Department Managers for their team (not for Admin or HR)
        if ($permission === 'team.view') {
            if ($user->isAdmin() || $user->isHr()) {
                return false;
            }
            return $user->isManager();
        }

        // Sales module is strictly exclusive to Admin and Sales department
        $module = explode('.', $permission)[0];
        if (in_array($module, ['customers', 'sales_tracking', 'leads', 'followups'], true)) {
            if (! $user->isAdmin() && ! $user->isSales()) {
                return false;
            }
        }

        if ($user->isAdmin()) {
            // Admins do not have employee self-service attendance, leaves, or personal expense claims
            if (in_array($permission, ['my_attendance.view', 'my_leaves.view', 'my_expenses.view'], true)) {
                return false;
            }

            // In Admin Panel administration section: keep only Roles & Permission, Department Module, and Audit module.
            // Exclude operational payroll, user management, attendance, leaves, and settings from Admin navigation.
            if (in_array($permission, [
                'payroll.view',
                'payroll.create',
                'payroll.edit',
                'users.view',
                'users.create',
                'attendance.view',
                'attendance.create',
                'leaves.view',
                'leaves.create',
                'settings.view',
            ], true)) {
                return false;
            }
        }

        return $user->can($permission);
    }

    /**
     * The visible items for a section, with children already filtered.
     *
     * An item is kept when it has no children, or when at least one child
     * survived. A parent with nothing left to show is dropped entirely, so a
     * collapsible button never appears over an empty menu.
     *
     * Each returned item gains:
     *   _children : the permitted children ([] when the item is a leaf)
     *   _href     : the resolved URL
     *   _active   : true when the current route matches
     *   _id       : a slug used for the submenu DOM ids
     *
     * @return array<int, array<string, mixed>>
     */
    public static function itemsFor(array $section, ?User $user): array
    {
        $items = [];

        foreach ($section['items'] ?? [] as $item) {
            if (! self::can($user, $item['permission'] ?? null)) {
                continue;
            }

            // In Admin Panel administration section: keep only Roles & Permission, Department Module, and Audit module
            if ($user && $user->isAdmin() && ($section['label'] ?? '') === 'Administration') {
                $allowedLabels = ['Roles & Permissions', 'Departments', 'Audit Log'];
                if (! in_array($item['label'] ?? '', $allowedLabels, true)) {
                    continue;
                }
            }

            $children = self::filterChildren($item['children'] ?? [], $user);

            if (($item['children'] ?? []) !== [] && $children === []) {
                // Every child was hidden: hide the parent too.
                continue;
            }

            $item['_children'] = $children;

            /*
             | Only a leaf needs a destination. A parent with children is a
             | collapsible toggle and deliberately has no route of its own, so
             | resolving one here would fail.
             */
            $item['_href'] = isset($item['url'])
                ? $item['url']
                : (isset($item['route']) ? route($item['route']) : '#');

            $item['_id'] = str((string) $item['label'])->slug()->value();
            $item['_active'] = self::matches($item['active'] ?? [])
                || self::childActive($children);

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Every section that still has at least one visible item.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function sections(?User $user): array
    {
        $sections = [];

        foreach (config('navigation.sections', []) as $section) {
            $items = self::itemsFor($section, $user);

            if ($items === []) {
                continue;
            }

            $section['items'] = $items;
            $sections[] = $section;
        }

        return $sections;
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     * @return array<int, array<string, mixed>>
     */
    private static function filterChildren(array $children, ?User $user): array
    {
        $kept = [];

        foreach ($children as $child) {
            if (! self::can($user, $child['permission'] ?? null)) {
                continue;
            }

            $child['_href'] = $child['url'] ?? route($child['route']);
            $child['_id'] = str((string) $child['label'])->slug()->value();
            $child['_active'] = self::matches($child['active'] ?? []);

            $kept[] = $child;
        }

        return $kept;
    }

    /**
     * True when the current request matches any of the route name patterns.
     *
     * @param  array<int, string>  $patterns
     */
    private static function matches(array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $children
     */
    private static function childActive(array $children): bool
    {
        foreach ($children as $child) {
            if ($child['_active'] ?? false) {
                return true;
            }
        }

        return false;
    }

    /**
     * "All Expenses" or "My Expenses", decided by the user's data scope rather
     * than by a role name.
     */
    public static function expenseScopeLabel(?User $user): string
    {
        $scope = DataScope::scopeFor($user, 'expenses');
        if ($scope === DataScope::ALL) {
            return 'All Expenses';
        }
        if ($scope === DataScope::TEAM) {
            return 'Team Expenses';
        }
        return 'My Expenses';
    }
}