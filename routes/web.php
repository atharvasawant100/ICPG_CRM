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

// Protected routes (admin authentication only)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notification routes
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

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Customer routes
    Route::get('customer', function (Request $request) {
        return redirect()->route('customers.index', $request->query());
    });
    Route::resource('customers', CustomerController::class);

    // Lead routes – allow /leads/ (trailing slash) to load leads index
    Route::get('leads/', function (Request $request) {
        return redirect()->route('leads.index', $request->query());
    });
    Route::get('leads/calendar', [LeadFollowupController::class, 'calendar'])->name('leads.calendar');
    Route::get('leads/create/manual', [LeadController::class, 'createManual'])->name('leads.create.manual');
    Route::get('leads/create/bulk', [LeadController::class, 'createBulk'])->name('leads.create.bulk');
    Route::post('leads/create/bulk', [LeadController::class, 'bulkImportStore'])->name('leads.bulk-import.store');
    Route::get('leads/create/bulk/template', [LeadController::class, 'downloadTemplate'])->name('leads.bulk-import.template');
    Route::resource('leads', LeadController::class);
    Route::get('leads/dashboard/stats', [LeadController::class, 'dashboard'])->name('leads.dashboard');

    Route::get('leads/trash/list', [LeadController::class, 'trash'])->name('leads.trash');
    Route::post('leads/{id}/restore', [LeadController::class, 'restore'])->name('leads.restore');
    Route::delete('leads/{id}/force-delete', [LeadController::class, 'forceDelete'])->name('leads.force-delete');
    Route::post('leads/{lead}/track-action', [LeadController::class, 'trackAction'])->name('leads.track-action');

    // Lead notes
    Route::post('leads/{lead}/notes', [LeadNoteController::class, 'store'])->name('leads.notes.store');
    Route::delete('leads/{lead}/notes/{note}', [LeadNoteController::class, 'destroy'])->name('leads.notes.destroy');

    // Lead follow-ups
    Route::post('leads/{lead}/followups', [LeadFollowupController::class, 'store'])->name('leads.followups.store');
    Route::put('leads/{lead}/followups/{followup}', [LeadFollowupController::class, 'update'])->name('leads.followups.update');
    Route::post('leads/{lead}/followups/{followup}/complete', [LeadFollowupController::class, 'complete'])->name('leads.followups.complete');
    Route::delete('leads/{lead}/followups/{followup}', [LeadFollowupController::class, 'destroy'])->name('leads.followups.destroy');

    // Lead conversion
    Route::post('leads/{lead}/convert', [LeadController::class, 'convertToCustomer'])->name('leads.convert');

    // Expense routes — concrete paths must be registered before the resource so they are not
    // captured by GET expenses/{expense} (e.g. pending list, approve).
    Route::middleware(['admin'])->group(function () {
        Route::get('expenses/', function (Request $request) {
            return redirect()->route('expenses.index', $request->query());
        });
        Route::get('expenses/pending/list', [ExpenseController::class, 'pending'])->name('expenses.pending');
        Route::put('expenses/{expense}/approve', [ExpenseApprovalController::class, 'update'])->name('expenses.approve');
        Route::resource('expenses', ExpenseController::class);
    });

    // Expense category routes (Admin only)
    Route::middleware(['admin'])->group(function () {
        Route::get('/expense-categories', [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
        Route::post('/expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
        Route::put('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
        Route::delete('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');
        Route::get('/expenses/reports/analytics', [ExpenseController::class, 'reports'])->name('expenses.reports');
    });

    // Invoice routes
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
        Route::post('/{invoice}/send', [InvoiceController::class, 'send'])->name('send');
        Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
        Route::get('/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('pdf');

        // Payments
        Route::post('/{invoice}/payments', [InvoicePaymentController::class, 'store'])->name('payments.store');
        Route::delete('/payments/{payment}', [InvoicePaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // Recurring invoice routes
    Route::prefix('recurring-invoices')->name('recurring-invoices.')->group(function () {
        Route::get('/', [RecurringInvoiceTemplateController::class, 'index'])->name('index');
        Route::get('/create', [RecurringInvoiceTemplateController::class, 'create'])->name('create');
        Route::post('/', [RecurringInvoiceTemplateController::class, 'store'])->name('store');
        Route::get('/{template}', [RecurringInvoiceTemplateController::class, 'show'])->name('show');
        Route::get('/{template}/edit', [RecurringInvoiceTemplateController::class, 'edit'])->name('edit');
        Route::put('/{template}', [RecurringInvoiceTemplateController::class, 'update'])->name('update');
        Route::delete('/{template}', [RecurringInvoiceTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{template}/toggle', [RecurringInvoiceTemplateController::class, 'toggle'])->name('toggle');
    });

    // Services Routes
    Route::resource('services', ServiceController::class);
});
