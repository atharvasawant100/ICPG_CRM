<?php

use App\Http\Controllers\Admin\AuditLogController;
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
|   /admin/users       users.view, users.create, users.edit
|   /admin/roles       roles.view, roles.edit
|   /admin/audit-logs  audit_logs.view
|   /admin/settings    settings.view
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
    Route::get('users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:users.edit')->name('users.edit');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.edit')->name('users.update');
    Route::post('users/{user}/toggle', [UserController::class, 'toggleStatus'])
        ->middleware('permission:users.edit')->name('users.toggle');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->middleware('permission:users.edit')->name('users.reset-password');

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

    // ---- Settings (placeholder) --------------------------------------------
    Route::get('settings', [SettingsController::class, 'index'])
        ->middleware('permission:settings.view')->name('settings.index');
});