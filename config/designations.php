<?php

/*
|--------------------------------------------------------------------------
| Designations
|--------------------------------------------------------------------------
|
| A designation is the job title shown on the Add/Edit User form: "Intern",
| "Software Developer", "Team Leader", "Manager". Which titles are offered
| depends on the department, so picking a department narrows the dropdown.
|
| The Add User form no longer has a Role field. Access is derived instead:
|
|   department decides the employee role
|       A user in "development" gets the "development" role, whatever their
|       designation. Intern, Software Developer and Team Leader all land
|       here.
|
|   a designation listed in `elevates_to` overrides that
|       "Manager" gives the manager role, which is what makes a department
|       manager's team visible to them.
|
| Changing a title here is a config edit. It needs no migration and no code
| change, and it does not affect anyone already created.
|
| PROVISIONAL: the per-department lists below are placeholders. Replace them
| with the real lists; nothing else needs to change.
|
*/

return [

    /*
     | Titles offered when a department has no list of its own, and when no
     | department has been chosen yet.
     |
     | These are the four you named. Per-department lists go in `departments`
     | below once you send them.
     */
    'default' => [
        'intern' => 'Intern',
        'software developer' => 'Software Developer',
        'team leader' => 'Team Leader',
        'manager' => 'Manager',
    ],

    /*
     | department name (departments.name) => designations.
     |
     | A department with no entry here falls back to `default`.
     |
     | AWAITING YOUR LISTS. Add an entry per department, for example:
     |
     |   'sales' => ['intern' => 'Intern', 'sales executive' => 'Sales Executive', ...],
     |   'hr'    => ['intern' => 'Intern', 'hr executive' => 'HR Executive', ...],
     |
     | Nothing else needs to change: the Add User form reads this file, and the
     | dependent dropdown re-renders from it with no restart.
     */
    'departments' => [
        'development' => [
            'intern' => 'Intern',
            'software developer' => 'Software Developer',
            'senior software developer' => 'Senior Software Developer',
            'team leader' => 'Team Leader',
            'manager' => 'Manager',
        ],
        'hr' => [
            'hr executive' => 'HR Executive',
        ],
        'marketing' => [
            'marketing intern' => 'Marketing Intern',
            'marketing executive' => 'Marketing Executive',
            'marketing manager' => 'Marketing Manager',
        ],
        'sales' => [
            'intern' => 'Intern',
            'business executive' => 'Business Executive',
            'tl' => 'TL',
            'manager' => 'Manager',
        ],
    ],

    /*
     | Designations that grant the manager role instead of the department's
     | employee role.
     */
    'elevates_to' => [
        'manager' => 'manager',
        'marketing manager' => 'manager',
    ],

    /*
     | Employee role to use when a department has no matching Spatie role.
     |
     | Departments are editable, so someone can add "finance" with no finance
     | role behind it. Falling back to the least-privileged existing role keeps
     | the user usable without silently granting them more than they should
     | have. The Add User form warns when this happens.
     */
    'fallback_role' => 'sales',
];