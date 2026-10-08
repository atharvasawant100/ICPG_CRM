<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseApprovalController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadNoteController;
use App\Http\Controllers\LeadFollowupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\RecurringInvoiceTemplateController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

// Test email route (for debugging)
Route::get('/test-email', function () {
    try {
        Mail::raw('This is a test email from ICPG Office CRM.', function ($message) {
            $message->from(config('mail.from.address'), config('mail.from.name'))
                ->to('hiteshbhagwat97@gmail.com')
                ->subject('Test Email - ICPG Office');
        });

        return 'Test email sent successfully! Check hiteshbhagwat97@gmail.com';
    } catch (\Exception $e) {
        return 'Email failed: ' . $e->getMessage();
    }
});


// Public routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
|
| `active` logs out and blocks any user whose is_active is false. It is
| registered before `permission` so a deactivated user never reaches a
| permission check.
|
| Spatie's permission middleware treats a comma-separated list as AND, so each
| permission is attached per route (or per same-permission action group) rather
| than as one blanket list per module. Resources are therefore split with
| ->only() by action so each group carries exactly one permission.
|
| The `admin` alias is deliberately not used for module access: access is
| expressed purely as permissions, so a non-admin role holding the right
| permission is not locked out.
|
*/
Route::middleware(['auth:sanctum', 'active'])->group(function () {

    // ---- Profile, logout, notifications: open to any authenticated user ----
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::post('/notifications/{id}/mark-as-read', function ($id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.mark-as-read');

    // Poll: dispatch due follow-up reminders and return fresh unread notifications
    Route::get('/notifications/poll', function (\App\Services\FollowupReminderService $reminders) {
        $reminders->dispatchDueReminders();

        $user = auth()->user();
        $unread = $user->unreadNotifications()->latest()->limit(10)->get();
        $upcomingCount = \App\Models\RecurringInvoiceTemplate::active()
            ->where('next_generation_date', '<=', \Carbon\Carbon::today()->addDays(3))
            ->count();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'upcoming_count' => $upcomingCount,
            'total_count' => $unreadCount + $upcomingCount,
            'notifications' => $unread->map(fn ($n) => [
                'id' => $n->id,
                'message' => $n->data['message'] ?? 'New notification',
                'url' => $n->data['url'] ?? null,
                'type' => $n->data['type'] ?? null,
                'created_at' => $n->created_at->diffForHumans(),
            ]),
        ]);
    })->name('notifications.poll');

    // ---- Dashboard ----------------------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    // ---- Customers ----------------------------------------------------------
    Route::get('customer', function (Request $request) {
        return redirect()->route('customers.index', $request->query());
    })->middleware('permission:customers.view');

    /*
     | Declared explicitly rather than via ->resource()->only(...).
     |
     | Splitting one resource across several ->only() groups re-registers the
     | same URI set per group, which makes `customers/create` fall through to
     | the `{customer}` show route and 404 with "no query results for model".
     | Listing each route once, in order, avoids that entirely.
     */
    Route::get('customers', [CustomerController::class, 'index'])
        ->middleware('permission:customers.view')->name('customers.index');
    Route::get('customers/create', [CustomerController::class, 'create'])
        ->middleware('permission:customers.create')->name('customers.create');
    Route::post('customers', [CustomerController::class, 'store'])
        ->middleware('permission:customers.create')->name('customers.store');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])
        ->middleware('permission:customers.view')->name('customers.show');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])
        ->middleware('permission:customers.edit')->name('customers.edit');
    Route::match(['put', 'patch'], 'customers/{customer}', [CustomerController::class, 'update'])
        ->middleware('permission:customers.edit')->name('customers.update');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])
        ->middleware('permission:customers.delete')->name('customers.destroy');

    // ---- Leads: concrete paths before the parameterized ones ---------------
    Route::get('leads/', function (Request $request) {
        return redirect()->route('leads.index', $request->query());
    })->middleware('permission:leads.view');

    Route::get('leads/calendar', [LeadFollowupController::class, 'calendar'])
        ->middleware('permission:followups.view')->name('leads.calendar');

    // /leads/create must precede /leads/{lead} so "create" is not read as an id.
    Route::get('leads/create', [LeadController::class, 'create'])
        ->middleware('permission:leads.create')->name('leads.create');
    Route::get('leads/create/manual', [LeadController::class, 'createManual'])
        ->middleware('permission:leads.create')->name('leads.create.manual');

    // Bulk import is its own capability, distinct from leads.create.
    Route::get('leads/create/bulk', [LeadController::class, 'createBulk'])
        ->middleware('permission:leads.import')->name('leads.create.bulk');
    Route::post('leads/create/bulk', [LeadController::class, 'bulkImportStore'])
        ->middleware('permission:leads.import')->name('leads.bulk-import.store');
    Route::get('leads/create/bulk/template', [LeadController::class, 'downloadTemplate'])
        ->middleware('permission:leads.import')->name('leads.bulk-import.template');

    Route::get('leads/dashboard/stats', [LeadController::class, 'dashboard'])
        ->middleware('permission:leads.view')->name('leads.dashboard');
    Route::get('leads/trash/list', [LeadController::class, 'trash'])
        ->middleware('permission:leads.view')->name('leads.trash');

    Route::get('leads', [LeadController::class, 'index'])
        ->middleware('permission:leads.view')->name('leads.index');
    Route::post('leads', [LeadController::class, 'store'])
        ->middleware('permission:leads.create')->name('leads.store');
    Route::get('leads/{lead}', [LeadController::class, 'show'])
        ->middleware('permission:leads.view')->name('leads.show');
    Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])
        ->middleware('permission:leads.edit')->name('leads.edit');
    Route::match(['put', 'patch'], 'leads/{lead}', [LeadController::class, 'update'])
        ->middleware('permission:leads.edit')->name('leads.update');
    Route::delete('leads/{lead}', [LeadController::class, 'destroy'])
        ->middleware('permission:leads.delete')->name('leads.destroy');

    Route::post('leads/{id}/restore', [LeadController::class, 'restore'])
        ->middleware('permission:leads.edit')->name('leads.restore');
    Route::delete('leads/{id}/force-delete', [LeadController::class, 'forceDelete'])
        ->middleware('permission:leads.delete')->name('leads.force-delete');
    Route::post('leads/{lead}/track-action', [LeadController::class, 'trackAction'])
        ->middleware('permission:leads.edit')->name('leads.track-action');

    // ---- Lead notes ---------------------------------------------------------
    Route::post('leads/{lead}/notes', [LeadNoteController::class, 'store'])
        ->middleware('permission:leads.notes.create')->name('leads.notes.store');
    Route::delete('leads/{lead}/notes/{note}', [LeadNoteController::class, 'destroy'])
        ->middleware('permission:leads.notes.delete')->name('leads.notes.destroy');

    // ---- Lead follow-ups ----------------------------------------------------
    Route::post('leads/{lead}/followups', [LeadFollowupController::class, 'store'])
        ->middleware('permission:followups.create')->name('leads.followups.store');
    Route::put('leads/{lead}/followups/{followup}', [LeadFollowupController::class, 'update'])
        ->middleware('permission:followups.edit')->name('leads.followups.update');
    Route::post('leads/{lead}/followups/{followup}/complete', [LeadFollowupController::class, 'complete'])
        ->middleware('permission:followups.edit')->name('leads.followups.complete');
    Route::delete('leads/{lead}/followups/{followup}', [LeadFollowupController::class, 'destroy'])
        ->middleware('permission:followups.delete')->name('leads.followups.destroy');

    // ---- Lead conversion ----------------------------------------------------
    Route::post('leads/{lead}/convert', [LeadController::class, 'convertToCustomer'])
        ->middleware('permission:leads.convert')->name('leads.convert');

    // ---- Expenses -----------------------------------------------------------
    // Concrete paths must be registered before the resource so they are not
    // captured by GET expenses/{expense} (e.g. pending list, reports).
    Route::get('expenses/', function (Request $request) {
        return redirect()->route('expenses.index', $request->query());
    })->middleware('permission:expenses.view');

    Route::get('expenses/pending/list', [ExpenseController::class, 'pending'])
        ->middleware('permission:expenses.approve')->name('expenses.pending');
    Route::get('expenses/reports/analytics', [ExpenseController::class, 'reports'])
        ->middleware('permission:expenses.export')->name('expenses.reports');
    Route::put('expenses/{expense}/approve', [ExpenseApprovalController::class, 'update'])
        ->middleware('permission:expenses.approve')->name('expenses.approve');

    Route::get('expenses', [ExpenseController::class, 'index'])
        ->middleware('permission:expenses.view')->name('expenses.index');
    Route::get('expenses/create', [ExpenseController::class, 'create'])
        ->middleware('permission:expenses.create')->name('expenses.create');
    Route::post('expenses', [ExpenseController::class, 'store'])
        ->middleware('permission:expenses.create')->name('expenses.store');
    Route::get('expenses/{expense}', [ExpenseController::class, 'show'])
        ->middleware('permission:expenses.view')->name('expenses.show');
    Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])
        ->middleware('permission:expenses.edit')->name('expenses.edit');
    Route::match(['put', 'patch'], 'expenses/{expense}', [ExpenseController::class, 'update'])
        ->middleware('permission:expenses.edit')->name('expenses.update');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->middleware('permission:expenses.delete')->name('expenses.destroy');

    // ---- Expense categories (own module) ------------------------------------
    Route::get('/expense-categories', [ExpenseCategoryController::class, 'index'])
        ->middleware('permission:expense_categories.view')->name('expense-categories.index');
    Route::post('/expense-categories', [ExpenseCategoryController::class, 'store'])
        ->middleware('permission:expense_categories.create')->name('expense-categories.store');
    Route::put('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])
        ->middleware('permission:expense_categories.edit')->name('expense-categories.update');
    Route::delete('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])
        ->middleware('permission:expense_categories.delete')->name('expense-categories.destroy');

    // ---- Invoices -----------------------------------------------------------
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])
            ->middleware('permission:invoices.view')->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])
            ->middleware('permission:invoices.create')->name('create');
        Route::post('/', [InvoiceController::class, 'store'])
            ->middleware('permission:invoices.create')->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])
            ->middleware('permission:invoices.view')->name('show');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])
            ->middleware('permission:invoices.edit')->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])
            ->middleware('permission:invoices.edit')->name('update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])
            ->middleware('permission:invoices.delete')->name('destroy');
        Route::post('/{invoice}/send', [InvoiceController::class, 'send'])
            ->middleware('permission:invoices.send')->name('send');
        Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel'])
            ->middleware('permission:invoices.cancel')->name('cancel');
        // The single-invoice PDF download rides on invoices.export.
        Route::get('/{invoice}/pdf', [InvoiceController::class, 'pdf'])
            ->middleware('permission:invoices.export')->name('pdf');

        // Payments are financial writes, kept separate from invoices.edit.
        Route::post('/{invoice}/payments', [InvoicePaymentController::class, 'store'])
            ->middleware('permission:invoices.payments.create')->name('payments.store');
        Route::delete('/payments/{payment}', [InvoicePaymentController::class, 'destroy'])
            ->middleware('permission:invoices.payments.delete')->name('payments.destroy');
    });

    // ---- Recurring invoices -------------------------------------------------
    Route::prefix('recurring-invoices')->name('recurring-invoices.')->group(function () {
        Route::get('/', [RecurringInvoiceTemplateController::class, 'index'])
            ->middleware('permission:recurring_invoices.view')->name('index');
        Route::get('/create', [RecurringInvoiceTemplateController::class, 'create'])
            ->middleware('permission:recurring_invoices.create')->name('create');
        Route::post('/', [RecurringInvoiceTemplateController::class, 'store'])
            ->middleware('permission:recurring_invoices.create')->name('store');
        Route::get('/{template}', [RecurringInvoiceTemplateController::class, 'show'])
            ->middleware('permission:recurring_invoices.view')->name('show');
        Route::get('/{template}/edit', [RecurringInvoiceTemplateController::class, 'edit'])
            ->middleware('permission:recurring_invoices.edit')->name('edit');
        Route::put('/{template}', [RecurringInvoiceTemplateController::class, 'update'])
            ->middleware('permission:recurring_invoices.edit')->name('update');
        Route::delete('/{template}', [RecurringInvoiceTemplateController::class, 'destroy'])
            ->middleware('permission:recurring_invoices.delete')->name('destroy');
        Route::post('/{template}/toggle', [RecurringInvoiceTemplateController::class, 'toggle'])
            ->middleware('permission:recurring_invoices.toggle')->name('toggle');
    });

    // ---- Services -----------------------------------------------------------
    Route::get('services', [ServiceController::class, 'index'])
        ->middleware('permission:services.view')->name('services.index');
    Route::get('services/create', [ServiceController::class, 'create'])
        ->middleware('permission:services.create')->name('services.create');
    Route::post('services', [ServiceController::class, 'store'])
        ->middleware('permission:services.create')->name('services.store');
    Route::get('services/{service}', [ServiceController::class, 'show'])
        ->middleware('permission:services.view')->name('services.show');
    Route::get('services/{service}/edit', [ServiceController::class, 'edit'])
        ->middleware('permission:services.edit')->name('services.edit');
    Route::match(['put', 'patch'], 'services/{service}', [ServiceController::class, 'update'])
        ->middleware('permission:services.edit')->name('services.update');
    Route::delete('services/{service}', [ServiceController::class, 'destroy'])
        ->middleware('permission:services.delete')->name('services.destroy');
});