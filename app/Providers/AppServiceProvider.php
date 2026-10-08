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
use App\Rbac\Rbac;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
                return $user->hasRole(Rbac::ADMIN_ROLE) ? true : null;
            } catch (\Throwable) {
                // Permission tables unavailable (e.g. mid-migration): fall back to
                // the legacy mirror rather than locking everyone out.
                return $user->getAttribute('role') === Rbac::ADMIN_ROLE ? true : null;
            }
        });
    }
}
