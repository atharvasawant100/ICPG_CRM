<?php

/*
|--------------------------------------------------------------------------
| Sidebar navigation
|--------------------------------------------------------------------------
|
| One config, rendered in a loop. Adding a role later never needs a view
| edit: an item appears when the user holds its permission, and disappears when
| they do not.
|
| Item keys
|   label      : text shown in the sidebar
|   icon       : single SVG path `d` attribute, matching the existing icon style
|   route      : route name (preferred) or `url` for a literal path
|   permission : the permission that reveals this item
|   active     : route name patterns that highlight it, without the leading dot
|   badge      : true renders a small "NEW" pill
|   children   : sub-items; the parent is only rendered when at least one
|                child is allowed
|
| A section with a `label` renders a header. Its header is suppressed when every
| item inside it is hidden, so no orphan headings appear for a restricted user.
|
*/

return [

    'sections' => [

        // ---- Main modules, same order as before -----------------------
        [
            'label' => null,
            'items' => [

                [
                    'label' => 'Dashboard',
                    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                    'route' => 'dashboard',
                    'permission' => 'dashboard.view',
                    'active' => ['dashboard'],
                ],

                [
                    'label' => 'Customers',
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                    'route' => 'customers.index',
                    'permission' => 'customers.view',
                    'active' => ['customers.*'],
                ],

                [
                    'label' => 'Leads',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    'permission' => 'leads.view',
                    'active' => ['leads.*'],
                    'children' => [
                        [
                            'label' => 'Leads Dashboard',
                            'route' => 'leads.dashboard',
                            'permission' => 'leads.view',
                            'active' => ['leads.dashboard'],
                        ],
                        [
                            'label' => 'Add New Lead',
                            'route' => 'leads.create',
                            'permission' => 'leads.create',
                            'active' => ['leads.create', 'leads.create.*'],
                        ],
                        [
                            'label' => 'View all Leads',
                            'url' => '/leads',
                            'permission' => 'leads.view',
                            'active' => ['leads.index'],
                        ],
                        [
                            'label' => 'Follow-up Calendar',
                            'route' => 'leads.calendar',
                            'permission' => 'followups.view',
                            'active' => ['leads.calendar'],
                        ],
                        [
                            'label' => 'Trash Leads',
                            'route' => 'leads.trash',
                            'permission' => 'leads.view',
                            'active' => ['leads.trash'],
                        ],
                    ],
                ],

                [
                    'label' => 'Expenses',
                    'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    'permission' => 'expenses.view',
                    'active' => ['expenses.*', 'expense-categories.*'],
                    'children' => [
                        [
                            'label' => 'Add New Expense',
                            'route' => 'expenses.create',
                            'permission' => 'expenses.create',
                            'active' => ['expenses.create'],
                        ],
                        [
                            // "All" vs "My" is decided by data scope, not by role.
                            'label' => 'expenses.label',
                            'url' => '/expenses',
                            'permission' => 'expenses.view',
                            'active' => ['expenses.index'],
                        ],
                        [
                            'label' => 'Pending Expenses',
                            'route' => 'expenses.pending',
                            'permission' => 'expenses.approve',
                            'active' => ['expenses.pending'],
                        ],
                        [
                            'label' => 'Expense Categories',
                            'url' => '/expense-categories',
                            'permission' => 'expense_categories.view',
                            'active' => ['expense-categories.*'],
                        ],
                        [
                            'label' => 'Expense Reports',
                            'url' => '/expenses/reports/analytics',
                            'permission' => 'expenses.export',
                            'active' => ['expenses.reports'],
                        ],
                    ],
                ],

                [
                    'label' => 'Services',
                    'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                    'route' => 'services.index',
                    'permission' => 'services.view',
                    'active' => ['services.*'],
                ],

                [
                    'label' => 'Invoices',
                    'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    'permission' => 'invoices.view',
                    'active' => ['invoices.*', 'recurring-invoices.*'],
                    'children' => [
                        [
                            'label' => 'All Invoices',
                            'route' => 'invoices.index',
                            'permission' => 'invoices.view',
                            'active' => ['invoices.*'],
                            'activeExclude' => ['recurring-invoices.*'],
                        ],
                        [
                            'label' => 'Recurring Invoices',
                            'route' => 'recurring-invoices.index',
                            'permission' => 'recurring_invoices.view',
                            'active' => ['recurring-invoices.*'],
                        ],
                    ],
                ],
            ],
        ],

        // ---- Administration -------------------------------------------
        [
            'label' => 'Administration',
            'items' => [

                [
                    'label' => 'Users & Roles',
                    'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                    'route' => 'admin.users.index',
                    'permission' => 'users.view',
                    'active' => ['admin.users.*'],
                    'badge' => true,
                    'children' => [
                        [
                            'label' => 'Users',
                            'route' => 'admin.users.index',
                            'permission' => 'users.view',
                            'active' => ['admin.users.index', 'admin.users.create', 'admin.users.edit'],
                        ],
                        [
                            'label' => 'Roles & Permissions',
                            'route' => 'admin.roles.index',
                            'permission' => 'roles.view',
                            'active' => ['admin.roles.*'],
                        ],
                    ],
                ],

                [
                    'label' => 'Audit Log',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m3 7h-5m3 4h-3m-6-3h.01M9 16h.01',
                    'route' => 'admin.audit-logs.index',
                    'permission' => 'audit_logs.view',
                    'active' => ['admin.audit-logs.*'],
                    'badge' => true,
                ],

                [
                    'label' => 'Settings',
                    'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
                    'route' => 'admin.settings.index',
                    'permission' => 'settings.view',
                    'active' => ['admin.settings.*'],
                ],
            ],
        ],
    ],
];