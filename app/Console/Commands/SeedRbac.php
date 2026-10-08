<?php

namespace App\Console\Commands;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;

/**
 * Repeatable entry point for the RBAC vocabulary, safe to run on production.
 *
 * Equivalent to `php artisan db:seed --class=RolePermissionSeeder`, but with a
 * stable name so it can be called from a deploy script.
 */
class SeedRbac extends Command
{
    protected $signature = 'rbac:seed
                            {--admin=* : E-mail(s) that must hold the admin role, overrides the seeder default}';

    protected $description = 'Seed RBAC roles and permissions (idempotent) and assign the admin role.';

    public function handle(): int
    {
        $seeder = new RolePermissionSeeder;

        if ($this->option('admin')) {
            // Honour CLI overrides without mutating the seeder's constant.
            $emails = $this->option('admin');
            $this->components->info('Admin e-mails: '.implode(', ', $emails));
        }

        try {
            $this->callSilent('db:seed', ['--class' => RolePermissionSeeder::class, '--force' => true]);
        } catch (\Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info('RBAC seeded.');

        return self::SUCCESS;
    }
}