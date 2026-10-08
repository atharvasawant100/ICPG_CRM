<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
});

// IMPORTANT: Prefix API route names with `api.` to avoid clashing with web
// resource route names like `customers.show` / `leads.index`, etc.
Route::middleware(['auth:sanctum', 'admin'])->name('api.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Lead Routes
    Route::get('leads/trash/list', [\App\Http\Controllers\Api\LeadController::class, 'trash']);
    Route::post('leads/{id}/restore', [\App\Http\Controllers\Api\LeadController::class, 'restore']);
    Route::delete('leads/{id}/force-delete', [\App\Http\Controllers\Api\LeadController::class, 'forceDelete']);
    Route::post('leads/{id}/convert', [\App\Http\Controllers\Api\LeadController::class, 'convertToCustomer']);
    Route::apiResource('leads', \App\Http\Controllers\Api\LeadController::class);

    // Customer Routes
    Route::apiResource('customers', \App\Http\Controllers\Api\CustomerController::class);

    // Expense Routes
    Route::get('expenses/pending', [\App\Http\Controllers\Api\ExpenseController::class, 'pending']);
    Route::put('expenses/{id}/process', [\App\Http\Controllers\Api\ExpenseController::class, 'approve']);
    Route::apiResource('expenses', \App\Http\Controllers\Api\ExpenseController::class);
});
