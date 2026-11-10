<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\RecurringInvoiceTemplate;
use App\Models\Service;
use App\Policies\CustomerPolicy;
use App\Policies\ExpenseCategoryPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LeadFollowupPolicy;
use App\Policies\LeadPolicy;
use App\Policies\RecurringInvoiceTemplatePolicy;
use App\Policies\ServicePolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Rbac\Rbac;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default timezone to Asia/Kolkata (IST)
        Carbon::setLocale('en');
        date_default_timezone_set(config('app.timezone'));

        $this->registerAdminGate();
        $this->registerPolicies();
    }

    /**
     * Map models to their policies.
     *
     * Registering explicitly rather than relying on convention, because the
     * conventions do not line up: the model is RecurringInvoiceTemplate but the
     * module name is recurring_invoices, and LeadFollowup is nested under leads
     * in the permission vocabulary.
     */
    protected function registerPolicies(): void
    {
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(RecurringInvoiceTemplate::class, RecurringInvoiceTemplatePolicy::class);
        Gate::policy(LeadFollowup::class, LeadFollowupPolicy::class);
        Gate::policy(ExpenseCategory::class, ExpenseCategoryPolicy::class);
        Gate::policy(\App\Models\User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(\App\Models\Task::class, \App\Policies\TaskPolicy::class);

        /*
         | Role abilities that take a Role argument. Spatie's Role is not an
         | Eloquent model of ours, so Gate::policy() cannot infer the argument
         | types; these explicit definitions tell it what to inject. Without
         | them the controller's authorize('update', $role) fails to resolve.
         */
        Gate::define('update', [RolePolicy::class, 'update']);
        Gate::define('view', [RolePolicy::class, 'view']);

        // Team module is exclusive to Department Managers for their team (not for Admin or HR)
        Gate::define('team.view', function (\App\Models\User $user) {
            if ($user->isAdmin() || $user->isHr()) {
                return false;
            }

            return $user->isManager();
        });
    }

    /**
     * Grant the admin role unrestricted access.
     *
     * Returning true short-circuits every Gate check, so the admin can never be
     * locked out by a permission that was added but not yet granted to anyone.
     * Returning null (rather than false) for everyone else is deliberate: it
     * lets the normal permission checks run.
     */
    protected function registerAdminGate(): void
    {
        Gate::before(function ($user, string $ability) {
            if (! method_exists($user, 'hasRole')) {
                return null;
            }

            try {
                if ($user->hasRole(Rbac::ADMIN_ROLE)) {
                    // Admins are business owners/employers; they do not have personal employee attendance, leaves, expense claims, or department team
                    if (in_array($ability, ['my_attendance.view', 'my_leaves.view', 'my_leaves.create', 'my_expenses.view', 'my_expenses.create', 'team.view'], true)) {
                        return false;
                    }

                    return true;
                }

                // Non-sales and non-admin users can never access sales module abilities
                $module = explode('.', $ability)[0];
                if (in_array($module, ['customers', 'sales_tracking', 'leads', 'followups'], true)) {
                    if (! $user->isAdmin() && ! $user->isSales()) {
                        return false;
                    }
                }

                // HR users do not have a department team view or task assignments
                if ($user->isHr() && in_array($ability, ['team.view', 'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete'], true)) {
                    return false;
                }

                // HR users have access to HR operational capabilities: departments, users, attendance, leaves, and payroll
                if ($user->isHr() && in_array($ability, [
                    'departments.view',
                    'users.view', 'users.create', 'users.edit',
                    'attendance.view', 'attendance.create', 'attendance.edit',
                    'leaves.view', 'leaves.create', 'leaves.approve', 'leaves.settings',
                    'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.delete', 'payroll.export',
                ], true)) {
                    return true;
                }

                // Department managers inherit permissions granted to their department's role (e.g. Sales Manager gets permissions ticked on Sales role)
                static $deptRolePermissions = [];
                if ($user->isManager() && ! empty($user->department)) {
                    $dept = strtolower((string) $user->department);
                    if (! array_key_exists($dept, $deptRolePermissions)) {
                        $role = Role::where('name', $dept)->where('guard_name', Rbac::GUARD)->first();
                        $deptRolePermissions[$dept] = $role ? $role->permissions->pluck('name')->all() : [];
                    }

                    if (in_array($ability, $deptRolePermissions[$dept], true)) {
                        return true;
                    }
                }

                return null;
            } catch (\Throwable) {
                // Permission tables unavailable (e.g. mid-migration): fall back to
                // the legacy mirror rather than locking everyone out.
                if ($user->getAttribute('role') === Rbac::ADMIN_ROLE) {
                    if (in_array($ability, ['my_attendance.view', 'my_leaves.view', 'my_leaves.create', 'my_expenses.view', 'my_expenses.create', 'team.view'], true)) {
                        return false;
                    }

                    return true;
                }

                if ($user->isHr() && $ability === 'team.view') {
                    return false;
                }

                if ($user->isHr() && in_array($ability, [
                    'departments.view',
                    'users.view', 'users.create', 'users.edit',
                    'attendance.view', 'attendance.create', 'attendance.edit',
                    'leaves.view', 'leaves.create', 'leaves.approve', 'leaves.settings',
                    'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.delete', 'payroll.export',
                ], true)) {
                    return true;
                }

                return null;
            }
        });
    }
}
