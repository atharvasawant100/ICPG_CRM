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
                    'label' => 'Team',
                    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                    'route' => 'team.index',
                    'permission' => 'team.view',
                    'active' => ['team.*'],
                ],

                [
                    'label' => 'My Attendance',
                    'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'route' => 'my-attendance.index',
                    'permission' => 'my_attendance.view',
                    'active' => ['my-attendance.*'],
                ],

                [
                    'label' => 'My Leaves',
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'route' => 'my-leaves.index',
                    'permission' => 'my_leaves.view',
                    'active' => ['my-leaves.*'],
                ],

                [
                    'label' => 'My Expenses',
                    'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                    'route' => 'my-expenses.index',
                    'permission' => 'my_expenses.view',
                    'active' => ['my-expenses.*'],
                ],

                [
                    'label' => 'Task Pool',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                    'route' => 'tasks.index',
                    'permission' => 'tasks.view',
                    'active' => ['tasks.*'],
                ],

                [
                    'label' => 'Direct Admin Tickets',
                    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'route' => 'tickets.index',
                    'permission' => null,
                    'active' => ['tickets.*'],
                ],

                [
                    'label' => 'Performance Report',
                    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    'route' => 'reports.performance',
                    'permission' => 'reports.performance',
                    'active' => ['reports.performance'],
                ],

            ],
        ],

        // ---- Sales ----------------------------------------------------
        [
            'label' => 'Sales',
            'items' => [

                [
                    'label' => 'Customers',
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                    'route' => 'customers.index',
                    'permission' => 'customers.view',
                    'active' => ['customers.*'],
                ],

                [
                    'label' => 'Sales Tracking',
                    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    'route' => 'sales-tracking.index',
                    'permission' => 'sales_tracking.view',
                    'active' => ['sales-tracking.*'],
                ],

                [
                    'label' => 'Leads',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    'permission' => 'leads.view',
                    'active' => ['leads.*'],
                    'children' => [
                        [
                            'label' => 'All Leads',
                            'url' => '/leads',
                            'permission' => 'leads.view',
                            'active' => ['leads.index'],
                        ],
                        [
                            'label' => 'Add New Lead',
                            'route' => 'leads.create.manual',
                            'permission' => 'leads.create',
                            'active' => ['leads.create', 'leads.create.manual'],
                        ],
                        [
                            'label' => 'Import Leads (Bulk)',
                            'route' => 'leads.create.bulk',
                            'permission' => 'leads.import',
                            'active' => ['leads.create.bulk'],
                        ],
                        [
                            'label' => 'Trash Leads',
                            'route' => 'leads.trash',
                            'permission' => 'leads.delete',
                            'active' => ['leads.trash'],
                        ],
                    ],
                ],

                [
                    'label' => 'Follow-Up Calendar',
                    'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'route' => 'leads.calendar',
                    'permission' => 'followups.view',
                    'active' => ['leads.calendar'],
                ],

            ],
        ],

        // ---- Social Media & Marketing ---------------------------------
        [
            'label' => 'Social Media',
            'items' => [
                [
                    'label' => 'Content Calendar',
                    'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'route' => 'content.calendar',
                    'permission' => 'content.view',
                    'active' => ['content.calendar*'],
                ],
                [
                    'label' => 'Content Dashboard',
                    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    'route' => 'content.dashboard',
                    'permission' => 'content.view',
                    'active' => ['content.dashboard'],
                ],
                [
                    'label' => 'Content Posts',
                    'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z',
                    'route' => 'content.posts.index',
                    'permission' => 'content.view',
                    'active' => ['content.posts.*'],
                ],
                [
                    'label' => 'Publishing Queue',
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'route' => 'content.queue',
                    'permission' => 'content.publish',
                    'active' => ['content.queue'],
                ],
                [
                    'label' => 'Brands & Accounts',
                    'icon' => 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14',
                    'route' => 'brands.index',
                    'permission' => 'content.brands',
                    'active' => ['brands.*'],
                ],
            ],
        ],
        [
            'label' => 'Accounting',
            'items' => [

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
                    'label' => 'Costing Employees',
                    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    'route' => 'admin.reports.costing',
                    'permission' => 'reports.costing',
                    'active' => ['admin.reports.costing'],
                ],

            ],
        ],

        // ---- Administration -------------------------------------------
        [
            'label' => 'Administration',
            'items' => [

                [
                    'label' => 'Roles & Permissions',
                    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'route' => 'admin.roles.index',
                    'permission' => 'roles.view',
                    'active' => ['admin.roles.*'],
                ],

                [
                    'label' => 'Users',
                    'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                    'route' => 'admin.users.index',
                    'permission' => 'users.view',
                    'active' => ['admin.users.*'],
                    'children' => [
                        [
                            'label' => 'All Users',
                            'route' => 'admin.users.index',
                            'permission' => 'users.view',
                            'active' => ['admin.users.index', 'admin.users.edit'],
                        ],
                        [
                            'label' => 'Add User',
                            'route' => 'admin.users.create',
                            'permission' => 'users.create',
                            'active' => ['admin.users.create'],
                        ],
                    ],
                ],

                [
                    'label' => 'Departments',
                    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                    'route' => 'admin.departments.index',
                    'permission' => 'departments.view',
                    'active' => ['admin.departments.*'],
                ],

                [
                    'label' => 'Attendance',
                    'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'permission' => 'attendance.view',
                    'active' => ['admin.attendance.*'],
                    'children' => [
                        [
                            'label' => 'Daily Sheet',
                            'route' => 'admin.attendance.index',
                            'permission' => 'attendance.view',
                            'active' => ['admin.attendance.index', 'admin.attendance.edit'],
                        ],
                        [
                            'label' => 'Mark Attendance',
                            'route' => 'admin.attendance.create',
                            'permission' => 'attendance.create',
                            'active' => ['admin.attendance.create'],
                        ],
                        [
                            'label' => 'Monthly Summary',
                            'route' => 'admin.attendance.monthly',
                            'permission' => 'attendance.view',
                            'active' => ['admin.attendance.monthly'],
                        ],
                        [
                            'label' => 'Timing Settings',
                            'route' => 'admin.attendance.settings',
                            'permission' => 'attendance.create',
                            'active' => ['admin.attendance.settings'],
                        ],
                    ],
                ],

                [
                    'label' => 'Leave Management',
                    'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'permission' => 'leaves.view',
                    'active' => ['admin.leaves.*'],
                    'children' => [
                        [
                            'label' => 'Leave Requests',
                            'route' => 'admin.leaves.index',
                            'permission' => 'leaves.view',
                            'active' => ['admin.leaves.index', 'admin.leaves.edit'],
                        ],
                        [
                            'label' => 'Record Leave',
                            'route' => 'admin.leaves.create',
                            'permission' => 'leaves.create',
                            'active' => ['admin.leaves.create'],
                        ],
                        [
                            'label' => 'Leave Balances',
                            'route' => 'admin.leaves.balances',
                            'permission' => 'leaves.view',
                            'active' => ['admin.leaves.balances'],
                        ],
                        [
                            'label' => 'Leave Types',
                            'route' => 'admin.leaves.types',
                            'permission' => 'leaves.settings',
                            'active' => ['admin.leaves.types'],
                        ],
                        [
                            'label' => 'Company Holidays',
                            'route' => 'admin.holidays.index',
                            'permission' => 'holidays.view',
                            'active' => ['admin.holidays.*'],
                        ],
                    ],
                ],

                [
                    'label' => 'Payroll',
                    'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                    'permission' => 'payroll.view',
                    'active' => ['payrolls.*', 'employee-salaries.*'],
                    'children' => [
                        [
                            'label' => 'All Payrolls',
                            'route' => 'payrolls.index',
                            'permission' => 'payroll.view',
                            'active' => ['payrolls.index', 'payrolls.show', 'payrolls.edit'],
                        ],
                        [
                            'label' => 'Generate Payroll',
                            'route' => 'payrolls.create',
                            'permission' => 'payroll.create',
                            'active' => ['payrolls.create'],
                        ],
                        [
                            'label' => 'Salary Settings',
                            'route' => 'employee-salaries.index',
                            'permission' => 'payroll.view',
                            'active' => ['employee-salaries.*'],
                        ],
                    ],
                ],

                [
                    'label' => 'Audit Log',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m3 7h-5m3 4h-3m-6-3h.01M9 16h.01',
                    'route' => 'admin.audit-logs.index',
                    'permission' => 'audit_logs.view',
                    'active' => ['admin.audit-logs.*'],
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