<?php

/**
 * Data-scope configuration.
 *
 * This is the ONE place that maps a module to its existing owner column and a
 * role to its data scope. Controllers, policies and the dashboard must all read
 * from here rather than hard-coding column names.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Module => owner column
    |--------------------------------------------------------------------------
    |
    | Reuses the columns that already exist in this schema. No new owner
    | columns were added: customers/leads/invoices/services/recurring
    | templates already carry `created_by`, expenses and follow-ups `user_id`.
    |
    | A module absent from this map is unscoped, which is correct for modules
    | that have no per-user ownership (settings, roles, audit_logs).
    |
    */
    'owner_columns' => [
        'customers'              => ['table' => 'customers', 'column' => 'created_by'],
        'leads'                  => ['table' => 'leads', 'column' => 'created_by'],
        'invoices'               => ['table' => 'invoices', 'column' => 'created_by'],
        'services'               => ['table' => 'services', 'column' => 'created_by'],
        'recurring_invoices'     => ['table' => 'recurring_invoice_templates', 'column' => 'created_by'],
        'expenses'               => ['table' => 'expenses', 'column' => 'user_id'],
        // Module name 'followups' maps to the lead_followups table.
        'followups'              => ['table' => 'lead_followups', 'column' => 'user_id'],
        'expense_categories'     => ['table' => 'expense_categories', 'column' => 'created_by'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role => module => scope
    |--------------------------------------------------------------------------
    |
    | Scopes: own | team | all
    |
    |   own  - only records the user owns
    |   team - own records plus those of users who report to them
    |   all  - everything (never filtered)
    |
    | Only `admin` is defined in this phase and it is `all` everywhere. Admin
    | is additionally short-circuited by Gate::before, so this map is the
    | belt-and-braces path used by applyDataScope().
    |
    | Any role NOT listed here defaults to `own` when it is granted a view
    | permission. That is the safe default: it can never expose more than the
    | user's own records.
    |
    */
    'scopes' => [
        'admin' => [
            'default' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default scope for unlisted roles
    |--------------------------------------------------------------------------
    */
    'default_scope' => 'own',

];