<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CostingReportController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
|
| Loaded from routes/web.php inside the existing authenticated group, so these
| inherit `auth:sanctum` and `active` (the deactivated-user check) and sit
| behind the same session handling as the rest of the app.
|
| Permissions:
|   /admin/users        users.view, users.create, users.edit
|   /admin/departments  departments.view, departments.create, departments.edit
|   /admin/roles        roles.view, roles.edit
|   /admin/audit-logs   audit_logs.view
|   /admin/settings     settings.view
|
| Every mutating action is additionally re-checked inside the controller, so
| hiding a button is never the only thing standing between a user and a change.
|
| Note: there is no delete route. The User model has no SoftDeletes and every
| business table references users, so a hard delete would orphan data. Users
| are deactivated instead, which is reversible.
|
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // ---- Users -------------------------------------------------------------
    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:users.view')->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('permission:users.create')->name('users.create');
    Route::post('users', [UserController::class, 'store'])
        ->middleware('permission:users.create')->name('users.store');
    Route::get('users/{user}', [UserController::class, 'show'])
        ->name('users.show');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:users.edit')->name('users.edit');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.edit')->name('users.update');
    Route::post('users/{user}/toggle', [UserController::class, 'toggleStatus'])
        ->middleware('permission:users.edit')->name('users.toggle');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->middleware('permission:users.edit')->name('users.reset-password');

    // ---- Departments -------------------------------------------------------
    // No delete route: users.department stores the department name as a string,
    // so removing a row would strand its users with a department that no
    // longer exists. Deactivate instead, which is reversible.
    //
    // show must be registered before the {department}/edit pair so "edit" is
    // not swallowed as a department slug.
    Route::get('departments', [DepartmentController::class, 'index'])
        ->middleware('permission:departments.view')->name('departments.index');
    Route::get('departments/create', [DepartmentController::class, 'create'])
        ->middleware('permission:departments.create')->name('departments.create');
    Route::post('departments', [DepartmentController::class, 'store'])
        ->middleware('permission:departments.create')->name('departments.store');
    Route::get('departments/{department}', [DepartmentController::class, 'show'])
        ->middleware('permission:departments.view')->name('departments.show');
    Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])
        ->middleware('permission:departments.edit')->name('departments.edit');
    Route::match(['put', 'patch'], 'departments/{department}', [DepartmentController::class, 'update'])
        ->middleware('permission:departments.edit')->name('departments.update');

    // ---- Roles and permissions ---------------------------------------------
    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')->name('roles.index');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:roles.edit')->name('roles.edit');
    Route::match(['put', 'patch'], 'roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.edit')->name('roles.update');

    // ---- Audit log (read only) ---------------------------------------------
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->middleware('permission:audit_logs.view')->name('audit-logs.index');

    // ---- Attendance (Employee Attendance Management) -----------------------
    Route::get('attendance', [AttendanceController::class, 'index'])
        ->middleware('permission:attendance.view')->name('attendance.index');
    Route::get('attendance/monthly', [AttendanceController::class, 'monthly'])
        ->middleware('permission:attendance.view')->name('attendance.monthly');
    Route::post('attendance/mark-absents', [AttendanceController::class, 'markAbsents'])
        ->middleware('permission:attendance.create')->name('attendance.mark-absents');
    Route::get('attendance/settings', [AttendanceController::class, 'settings'])
        ->middleware('permission:attendance.create')->name('attendance.settings');
    Route::post('attendance/settings', [AttendanceController::class, 'updateSettings'])
        ->middleware('permission:attendance.create')->name('attendance.settings.update');
    Route::post('attendance/auto-punch-out', [AttendanceController::class, 'triggerAutoPunchOut'])
        ->middleware('permission:attendance.create')->name('attendance.auto-punch-out');
    Route::get('attendance/create', [AttendanceController::class, 'create'])
        ->middleware('permission:attendance.create')->name('attendance.create');
    Route::post('attendance', [AttendanceController::class, 'store'])
        ->middleware('permission:attendance.create')->name('attendance.store');
    Route::get('attendance/{attendance}/edit', [AttendanceController::class, 'edit'])
        ->middleware('permission:attendance.edit')->name('attendance.edit');
    Route::match(['put', 'patch'], 'attendance/{attendance}', [AttendanceController::class, 'update'])
        ->middleware('permission:attendance.edit')->name('attendance.update');
    Route::delete('attendance/{attendance}', [AttendanceController::class, 'destroy'])
        ->middleware('permission:attendance.delete')->name('attendance.destroy');

    // ---- Holidays Management (HR Portal) -----------------------------------
    Route::get('holidays', [HolidayController::class, 'index'])
        ->middleware('permission:holidays.view')->name('holidays.index');
    Route::post('holidays', [HolidayController::class, 'store'])
        ->middleware('permission:holidays.create')->name('holidays.store');
    Route::match(['put', 'patch'], 'holidays/{holiday}', [HolidayController::class, 'update'])
        ->middleware('permission:holidays.edit')->name('holidays.update');
    Route::delete('holidays/{holiday}', [HolidayController::class, 'destroy'])
        ->middleware('permission:holidays.delete')->name('holidays.destroy');

    // ---- Leave Management --------------------------------------------------
    Route::get('leaves', [LeaveController::class, 'index'])
        ->middleware('permission:leaves.view')->name('leaves.index');
    Route::get('leaves/balances', [LeaveController::class, 'balances'])
        ->middleware('permission:leaves.view')->name('leaves.balances');
    Route::post('leaves/balances/{balance}', [LeaveController::class, 'updateBalance'])
        ->middleware('permission:leaves.settings')->name('leaves.balances.update');
    Route::get('leaves/types', [LeaveController::class, 'types'])
        ->middleware('permission:leaves.settings')->name('leaves.types');
    Route::post('leaves/types', [LeaveController::class, 'storeType'])
        ->middleware('permission:leaves.settings')->name('leaves.types.store');
    Route::match(['put', 'patch'], 'leaves/types/{type}', [LeaveController::class, 'updateType'])
        ->middleware('permission:leaves.settings')->name('leaves.types.update');
    Route::get('leaves/create', [LeaveController::class, 'create'])
        ->middleware('permission:leaves.create')->name('leaves.create');
    Route::post('leaves', [LeaveController::class, 'store'])
        ->middleware('permission:leaves.create')->name('leaves.store');
    Route::get('leaves/{leave}/edit', [LeaveController::class, 'edit'])
        ->middleware('permission:leaves.edit')->name('leaves.edit');
    Route::match(['put', 'patch'], 'leaves/{leave}', [LeaveController::class, 'update'])
        ->middleware('permission:leaves.edit')->name('leaves.update');
    Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve'])
        ->middleware('permission:leaves.approve')->name('leaves.approve');
    Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject'])
        ->middleware('permission:leaves.approve')->name('leaves.reject');
    Route::delete('leaves/{leave}', [LeaveController::class, 'destroy'])
        ->middleware('permission:leaves.delete')->name('leaves.destroy');

    // ---- Employee Costing & Company Reports (Owner / Admin) ----------------
    Route::get('reports/costing', [CostingReportController::class, 'index'])
        ->middleware('permission:reports.costing')->name('reports.costing');
    Route::post('employee-salaries/{salary}/approve', [CostingReportController::class, 'approveSalary'])
        ->middleware('permission:reports.costing')->name('salaries.approve');
    Route::post('employee-salaries/{salary}/reject', [CostingReportController::class, 'rejectSalary'])
        ->middleware('permission:reports.costing')->name('salaries.reject');

    // ---- Settings (placeholder) --------------------------------------------
    Route::get('settings', [SettingsController::class, 'index'])
        ->middleware('permission:settings.view')->name('settings.index');
});