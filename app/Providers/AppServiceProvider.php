<?php

namespace App\Providers;

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
