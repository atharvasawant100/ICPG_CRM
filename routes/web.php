<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\ContentCalendarController;
use App\Http\Controllers\ContentPostController;
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
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\EmployeeSalaryController;
use App\Http\Controllers\RecurringInvoiceTemplateController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SalesTrackingController;
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

// Password reset routes (OTP based via no-reply@iconicpages.com)
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetOtpEmail'])->name('password.email');
Route::post('/forgot-password/resend', [ForgotPasswordController::class, 'resendOtp'])->name('password.resend');
Route::get('/reset-password', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.update');

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Fallback avatar image route (ensures avatar images always load with proper headers)
Route::get('/storage/avatars/{filename}', function (string $filename) {
    $path = 'avatars/' . $filename;
    if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
        abort(404);
    }
    return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
})->where('filename', '[A-Za-z0-9_\-\.]+')->name('avatar.serve');

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

    // Avatar photo management (Self, HR, and Admin)
    Route::post('/users/{user}/avatar', [\App\Http\Controllers\UserAvatarController::class, 'update'])
        ->name('users.avatar.update');
    Route::delete('/users/{user}/avatar', [\App\Http\Controllers\UserAvatarController::class, 'destroy'])
        ->name('users.avatar.destroy');
    Route::post('/profile/avatar', function (\Illuminate\Http\Request $request) {
        return app(\App\Http\Controllers\UserAvatarController::class)->update($request, $request->user());
    })->name('profile.avatar.update');
    Route::delete('/profile/avatar', function (\Illuminate\Http\Request $request) {
        return app(\App\Http\Controllers\UserAvatarController::class)->destroy($request, $request->user());
    })->name('profile.avatar.destroy');

    // Attendance punch in & punch out (available to all staff)
    Route::post('/attendance/punch-in', [\App\Http\Controllers\AttendancePunchController::class, 'punchIn'])
        ->name('attendance.punch-in');
    Route::post('/attendance/punch-out', [\App\Http\Controllers\AttendancePunchController::class, 'punchOut'])
        ->name('attendance.punch-out');
    Route::post('/attendance/tab-close-logout', [\App\Http\Controllers\AttendancePunchController::class, 'tabCloseLogout'])
        ->name('attendance.tab-close-logout');
    Route::post('/attendance/heartbeat', [\App\Http\Controllers\AttendancePunchController::class, 'heartbeat'])
        ->name('attendance.heartbeat');

    // ---- Employee Self-Service: My Attendance & My Leaves -------------------
    Route::get('/my-attendance', function (\Illuminate\Http\Request $request) {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.attendance.index')->with('info', 'Admin accounts do not track personal attendance.');
        }
        abort_unless(\Illuminate\Support\Facades\Gate::allows('my_attendance.view'), 403);
        return app(\App\Http\Controllers\Employee\MyAttendanceController::class)->index($request);
    })->name('my-attendance.index');

    Route::get('/my-leaves', function (\Illuminate\Http\Request $request) {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.leaves.index')->with('info', 'Admin accounts do not have personal leave allocations.');
        }
        abort_unless(\Illuminate\Support\Facades\Gate::allows('my_leaves.view'), 403);
        return app(\App\Http\Controllers\Employee\MyLeaveController::class)->index($request);
    })->name('my-leaves.index');

    Route::post('/my-leaves', function (\Illuminate\Http\Request $request) {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.leaves.index')->with('info', 'Admin accounts do not apply for leaves.');
        }
        abort_unless(\Illuminate\Support\Facades\Gate::allows('my_leaves.create'), 403);
        return app(\App\Http\Controllers\Employee\MyLeaveController::class)->store($request);
    })->name('my-leaves.store');

    // ---- Employee Self-Service: My Expenses (Reimbursement Requests) --------
    Route::get('/my-expenses', function (\Illuminate\Http\Request $request) {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('expenses.index')->with('info', 'Admin accounts review and manage organization expenses.');
        }
        abort_unless(\Illuminate\Support\Facades\Gate::allows('my_expenses.view'), 403);
        return app(\App\Http\Controllers\Employee\MyExpenseController::class)->index($request);
    })->name('my-expenses.index');

    Route::post('/my-expenses', function (\Illuminate\Http\Request $request) {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('expenses.index')->with('info', 'Admin accounts do not submit personal reimbursement requests.');
        }
        abort_unless(\Illuminate\Support\Facades\Gate::allows('my_expenses.create'), 403);
        return app(\App\Http\Controllers\Employee\MyExpenseController::class)->store($request);
    })->name('my-expenses.store');

    Route::delete('/my-expenses/{expense}', function (\App\Models\Expense $expense, \Illuminate\Http\Request $request) {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('expenses.index');
        }
        abort_unless(\Illuminate\Support\Facades\Gate::allows('my_expenses.view'), 403);
        return app(\App\Http\Controllers\Employee\MyExpenseController::class)->destroy($expense, $request);
    })->name('my-expenses.destroy');

    // ---- Notifications ----------------------------------------------------
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('/notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])
        ->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])
        ->name('notifications.destroy');

    Route::post('/notifications/{id}/mark-as-read', function ($id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.mark-as-read');

    // Poll: dispatch due follow-up reminders and return fresh unread notifications
    Route::get('/notifications/poll', function (\App\Services\FollowupReminderService $reminders, \App\Services\NotificationFeed $feed) {
        $reminders->dispatchDueReminders();

        // Check overdue tasks (throttled every 5 minutes during web sessions)
        if (\Illuminate\Support\Facades\Cache::add('last_task_overdue_poll_check', true, now()->addMinutes(5))) {
            try {
                app(\App\Services\TaskOverdueService::class)->checkAndDispatchOverdueNotifications();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Task overdue poll check error: ' . $e->getMessage());
            }
        }

        $user = auth()->user();

        // The feed decides what this user may see. An employee only ever gets
        // task assignments and leave approvals.
        $unread = $feed->unread($user, 10);
        $unreadCount = $feed->unreadCount($user);
        $upcomingCount = $feed->upcomingCount($user);

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

    // ---- Team (Managers & Supervised Views) ---------------------------------
    Route::get('/team', [\App\Http\Controllers\TeamController::class, 'index'])
        ->middleware('permission:team.view')
        ->name('team.index');
    Route::get('/team/{user}', [\App\Http\Controllers\TeamController::class, 'show'])
        ->middleware('permission:team.view')
        ->name('team.show');

    // ---- Task Pool & Daily Tasks --------------------------------------------
    Route::get('/tasks/my-active', [\App\Http\Controllers\TaskController::class, 'myActiveTasks'])
        ->name('tasks.my-active');
    Route::get('/tasks', [\App\Http\Controllers\TaskController::class, 'index'])
        ->name('tasks.index');
    Route::post('/tasks', [\App\Http\Controllers\TaskController::class, 'store'])
        ->name('tasks.store');
    Route::get('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'show'])
        ->name('tasks.show');
    Route::match(['put', 'patch'], '/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'update'])
        ->name('tasks.update');
    Route::post('/tasks/{task}/toggle', [\App\Http\Controllers\TaskController::class, 'toggleComplete'])
        ->name('tasks.toggle');
    Route::patch('/tasks/{task}/status', [\App\Http\Controllers\TaskController::class, 'updateStatus'])
        ->name('tasks.status');
    Route::post('/tasks/{task}/delegate', [\App\Http\Controllers\TaskController::class, 'delegate'])
        ->name('tasks.delegate');
    Route::post('/tasks/{task}/escalate', [\App\Http\Controllers\TaskController::class, 'escalate'])
        ->name('tasks.escalate');
    Route::post('/tasks/{task}/rework', [\App\Http\Controllers\TaskController::class, 'rework'])
        ->name('tasks.rework');

    // ---- Task Collaboration, Help Requests, Comments, Attachments & Reviews --
    Route::post('/tasks/{task}/help-requests', [\App\Http\Controllers\TaskController::class, 'requestHelp'])
        ->name('tasks.help-requests.store');
    Route::post('/tasks/{task}/help-requests/{helpRequest}/respond', [\App\Http\Controllers\TaskController::class, 'respondHelp'])
        ->name('tasks.help-requests.respond');
    Route::post('/tasks/{task}/collaborators', [\App\Http\Controllers\TaskController::class, 'addCollaborator'])
        ->name('tasks.collaborators.store');
    Route::delete('/tasks/{task}/collaborators/{collaborator}', [\App\Http\Controllers\TaskController::class, 'removeCollaborator'])
        ->name('tasks.collaborators.destroy');
    Route::post('/tasks/{task}/comments', [\App\Http\Controllers\TaskController::class, 'addComment'])
        ->name('tasks.comments.store');
    Route::delete('/tasks/{task}/comments/{comment}', [\App\Http\Controllers\TaskController::class, 'deleteComment'])
        ->name('tasks.comments.destroy');
    Route::post('/tasks/{task}/attachments', [\App\Http\Controllers\TaskController::class, 'uploadAttachment'])
        ->name('tasks.attachments.store');
    Route::get('/tasks/{task}/attachments/{attachment}/download', [\App\Http\Controllers\TaskController::class, 'downloadAttachment'])
        ->name('tasks.attachments.download');
    Route::delete('/tasks/{task}/attachments/{attachment}', [\App\Http\Controllers\TaskController::class, 'deleteAttachment'])
        ->name('tasks.attachments.destroy');
    Route::post('/tasks/{task}/review/submit', [\App\Http\Controllers\TaskController::class, 'submitForReview'])
        ->name('tasks.review.submit');
    Route::post('/tasks/{task}/review/decide', [\App\Http\Controllers\TaskController::class, 'decideReview'])
        ->name('tasks.review.decide');

    Route::delete('/tasks/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])
        ->name('tasks.destroy');

    // ---- Confidential Internal Grievances & Direct Admin Tickets ------------
    Route::get('/tickets', [\App\Http\Controllers\InternalTicketController::class, 'index'])
        ->name('tickets.index');
    Route::get('/tickets/create', [\App\Http\Controllers\InternalTicketController::class, 'create'])
        ->name('tickets.create');
    Route::post('/tickets', [\App\Http\Controllers\InternalTicketController::class, 'store'])
        ->name('tickets.store');
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\InternalTicketController::class, 'show'])
        ->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\InternalTicketController::class, 'reply'])
        ->name('tickets.reply');
    Route::post('/tickets/{ticket}/resolve', [\App\Http\Controllers\InternalTicketController::class, 'resolve'])
        ->name('tickets.resolve');
    Route::get('/tickets/{ticket}/attachments/{attachment}/download', [\App\Http\Controllers\InternalTicketController::class, 'downloadAttachment'])
        ->name('tickets.attachments.download');

    // ---- Manager Performance Tracking & Monthly Report ----------------------
    Route::get('/reports/performance', [\App\Http\Controllers\PerformanceReportController::class, 'index'])
        ->name('reports.performance');

    // ---- Projects ------------------------------------------------------------
    Route::get('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'show'])
        ->name('projects.show');
    Route::post('/projects', [\App\Http\Controllers\ProjectController::class, 'store'])
        ->name('projects.store');
    Route::match(['put', 'patch'], '/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'update'])
        ->name('projects.update');
    Route::delete('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'destroy'])
        ->name('projects.destroy');

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

    // ---- Sales Tracking -----------------------------------------------------
    Route::get('sales-tracking', [SalesTrackingController::class, 'index'])
        ->middleware('permission:sales_tracking.view')->name('sales-tracking.index');

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

    Route::get('leads/bulk-reassign', fn () => redirect()->route('leads.index'));
    Route::post('leads/bulk-reassign', [LeadController::class, 'bulkReassign'])
        ->middleware('permission:leads.reassign')->name('leads.bulk-reassign');
    Route::get('leads/bulk-distribute', fn () => redirect()->route('leads.index'));
    Route::post('leads/bulk-distribute', [LeadController::class, 'bulkDistribute'])
        ->middleware('permission:leads.reassign')->name('leads.bulk-distribute');

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
    Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])
        ->middleware('permission:leads.edit')->name('leads.status.update');
    Route::get('leads/{lead}/reassign', fn (\App\Models\Lead $lead) => redirect()->route('leads.show', $lead));
    Route::post('leads/{lead}/reassign', [LeadController::class, 'reassign'])
        ->middleware('permission:leads.reassign')->name('leads.reassign');
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
    Route::post('followups/{followup}/reschedule', [LeadFollowupController::class, 'reschedule'])
        ->middleware('permission:followups.edit')->name('followups.reschedule');
    Route::post('leads/{lead}/followups/{followup}/reschedule', [LeadFollowupController::class, 'reschedule'])
        ->middleware('permission:followups.edit')->name('leads.followups.reschedule');
    Route::post('followups/{followup}/cancel', [LeadFollowupController::class, 'cancel'])
        ->middleware('permission:followups.edit')->name('followups.cancel');
    Route::post('leads/{lead}/followups/{followup}/cancel', [LeadFollowupController::class, 'cancel'])
        ->middleware('permission:followups.edit')->name('leads.followups.cancel');
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
        Route::post('/{template}/generate', [RecurringInvoiceTemplateController::class, 'generate'])
            ->middleware('permission:recurring_invoices.edit')->name('generate');
    });

    // ---- Payroll ------------------------------------------------------------
    Route::prefix('payrolls')->name('payrolls.')->group(function () {
        Route::get('/', [PayrollController::class, 'index'])
            ->middleware('permission:payroll.view')->name('index');
        Route::get('/create', [PayrollController::class, 'create'])
            ->middleware('permission:payroll.create')->name('create');
        Route::post('/', [PayrollController::class, 'store'])
            ->middleware('permission:payroll.create')->name('store');
        Route::get('/{payroll}', [PayrollController::class, 'show'])
            ->middleware('permission:payroll.view')->name('show');
        Route::get('/{payroll}/edit', [PayrollController::class, 'edit'])
            ->middleware('permission:payroll.edit')->name('edit');
        Route::put('/{payroll}', [PayrollController::class, 'update'])
            ->middleware('permission:payroll.edit')->name('update');
        Route::delete('/{payroll}', [PayrollController::class, 'destroy'])
            ->middleware('permission:payroll.delete')->name('destroy');
        Route::get('/{payroll}/pdf', [PayrollController::class, 'pdf'])
            ->middleware('permission:payroll.view')->name('pdf');
        Route::post('/{payroll}/pay', [PayrollController::class, 'markAsPaid'])
            ->middleware('permission:payroll.edit')->name('pay');
    });

    // ---- Employee Salary Structures -----------------------------------------
    Route::prefix('employee-salaries')->name('employee-salaries.')->group(function () {
        Route::get('/', [EmployeeSalaryController::class, 'index'])
            ->middleware('permission:payroll.view')->name('index');
        Route::get('/{user}/edit', [EmployeeSalaryController::class, 'edit'])
            ->middleware('permission:payroll.edit')->name('edit');
        Route::put('/{user}', [EmployeeSalaryController::class, 'update'])
            ->middleware('permission:payroll.edit')->name('update');
    });

    // ---- Admin modules ------------------------------------------------------
    // Loaded here so they inherit auth:sanctum + active from this group.
    require __DIR__ . '/admin.php';

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

    // ---- Social Media Content Calendar & Brands -----------------------------
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/dashboard', [ContentCalendarController::class, 'dashboard'])
            ->middleware('permission:content.view')->name('dashboard');
        Route::get('/calendar', [ContentCalendarController::class, 'calendar'])
            ->middleware('permission:content.view')->name('calendar');
        Route::get('/calendar/feed', [ContentCalendarController::class, 'feed'])
            ->middleware('permission:content.view')->name('calendar.feed');
        Route::get('/queue', [ContentCalendarController::class, 'queue'])
            ->middleware('permission:content.publish')->name('queue');

        // Posts CRUD
        Route::get('/posts', [ContentPostController::class, 'index'])
            ->middleware('permission:content.view')->name('posts.index');
        Route::get('/posts/create', [ContentPostController::class, 'create'])
            ->middleware('permission:content.create')->name('posts.create');
        Route::post('/posts', [ContentPostController::class, 'store'])
            ->middleware('permission:content.create')->name('posts.store');
        Route::get('/posts/{post}', [ContentPostController::class, 'show'])
            ->middleware('permission:content.view')->name('posts.show');
        Route::get('/posts/{post}/edit', [ContentPostController::class, 'edit'])
            ->middleware('permission:content.edit')->name('posts.edit');
        Route::match(['put', 'patch'], '/posts/{post}', [ContentPostController::class, 'update'])
            ->middleware('permission:content.edit')->name('posts.update');
        Route::delete('/posts/{post}', [ContentPostController::class, 'destroy'])
            ->middleware('permission:content.delete')->name('posts.destroy');

        // Post workflow actions
        Route::patch('/posts/{post}/reschedule', [ContentPostController::class, 'reschedule'])
            ->middleware('permission:content.edit')->name('posts.reschedule');
        Route::post('/posts/{post}/media', [ContentPostController::class, 'uploadMedia'])
            ->middleware('permission:content.edit')->name('posts.media.upload');
        Route::delete('/posts/{post}/media/{media}', [ContentPostController::class, 'deleteMedia'])
            ->middleware('permission:content.edit')->name('posts.media.delete');
        Route::get('/posts/{post}/media/{media}/download', [ContentPostController::class, 'downloadMedia'])
            ->middleware('permission:content.view')->name('posts.media.download');

        Route::post('/posts/{post}/submit-review', [ContentPostController::class, 'submitReview'])
            ->middleware('permission:content.edit')->name('posts.submit-review');
        Route::post('/posts/{post}/decide-review', [ContentPostController::class, 'decideReview'])
            ->middleware('permission:content.review')->name('posts.decide-review');
        Route::post('/posts/{post}/publish', [ContentPostController::class, 'publish'])
            ->middleware('permission:content.publish')->name('posts.publish');
        Route::post('/posts/{post}/fail', [ContentPostController::class, 'fail'])
            ->middleware('permission:content.publish')->name('posts.fail');

        // Task integration
        Route::post('/posts/{post}/tasks', [ContentPostController::class, 'createLinkedTask'])
            ->middleware('permission:content.assign')->name('posts.tasks.store');
    });

    // Brands CRUD
    Route::resource('brands', BrandController::class);

    // Social Accounts
    Route::post('social-accounts', [SocialAccountController::class, 'store'])
        ->middleware('permission:content.accounts')->name('social-accounts.store');
    Route::match(['put', 'patch'], 'social-accounts/{socialAccount}', [SocialAccountController::class, 'update'])
        ->middleware('permission:content.accounts')->name('social-accounts.update');
    Route::delete('social-accounts/{socialAccount}', [SocialAccountController::class, 'destroy'])
        ->middleware('permission:content.accounts')->name('social-accounts.destroy');
});