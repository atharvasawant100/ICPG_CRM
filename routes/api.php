<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| IMPORTANT: Prefix API route names with `api.` to avoid clashing with web
| resource route names like `customers.show` / `leads.index`, etc.
|
| These routes carry exactly the same permissions as their web equivalents, so
| a token client is never more privileged than a browser session.
|
| `active` logs out / blocks a deactivated user and revokes their tokens.
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
});

// IMPORTANT: Prefix API route names with `api.` to avoid clashing with web
// resource route names like `customers.show` / `leads.index`, etc.
Route::middleware(['auth:sanctum', 'active'])->name('api.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ---- Leads --------------------------------------------------------------
    Route::get('leads/trash/list', [\App\Http\Controllers\Api\LeadController::class, 'trash'])
        ->middleware('permission:leads.view');
    Route::post('leads/{id}/restore', [\App\Http\Controllers\Api\LeadController::class, 'restore'])
        ->middleware('permission:leads.edit');
    Route::delete('leads/{id}/force-delete', [\App\Http\Controllers\Api\LeadController::class, 'forceDelete'])
        ->middleware('permission:leads.delete');
    Route::post('leads/{id}/convert', [\App\Http\Controllers\Api\LeadController::class, 'convertToCustomer'])
        ->middleware('permission:leads.convert');

    Route::apiResource('leads', \App\Http\Controllers\Api\LeadController::class)->only(['index', 'show'])
        ->middleware('permission:leads.view');
    Route::apiResource('leads', \App\Http\Controllers\Api\LeadController::class)->only(['store'])
        ->middleware('permission:leads.create');
    Route::apiResource('leads', \App\Http\Controllers\Api\LeadController::class)->only(['update'])
        ->middleware('permission:leads.edit');
    Route::apiResource('leads', \App\Http\Controllers\Api\LeadController::class)->only(['destroy'])
        ->middleware('permission:leads.delete');
    // (apiResource has no create route, so no /{lead}/create collision here.)

    // ---- Customers ----------------------------------------------------------
    Route::apiResource('customers', \App\Http\Controllers\Api\CustomerController::class)->only(['index', 'show'])
        ->middleware('permission:customers.view');
    Route::apiResource('customers', \App\Http\Controllers\Api\CustomerController::class)->only(['store'])
        ->middleware('permission:customers.create');
    Route::apiResource('customers', \App\Http\Controllers\Api\CustomerController::class)->only(['update'])
        ->middleware('permission:customers.edit');
    Route::apiResource('customers', \App\Http\Controllers\Api\CustomerController::class)->only(['destroy'])
        ->middleware('permission:customers.delete');

    // ---- Expenses -----------------------------------------------------------
    Route::get('expenses/pending', [\App\Http\Controllers\Api\ExpenseController::class, 'pending'])
        ->middleware('permission:expenses.approve');
    Route::put('expenses/{id}/process', [\App\Http\Controllers\Api\ExpenseController::class, 'approve'])
        ->middleware('permission:expenses.approve');

    Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseController::class)->only(['index', 'show'])
        ->middleware('permission:expenses.view');
    Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseController::class)->only(['store'])
        ->middleware('permission:expenses.create');
    Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseController::class)->only(['update'])
        ->middleware('permission:expenses.edit');
    Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseController::class)->only(['destroy'])
        ->middleware('permission:expenses.delete');
});