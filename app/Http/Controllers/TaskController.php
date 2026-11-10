<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\TaskHelpRequest;
use App\Models\User;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display Task lists (My Tasks, Created By Me, Team Tasks, Overdue, Daily Done).
     */
    public function index(Request $request, TaskService $taskService)
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();
        $isManager = $user->isAdmin() || $user->isManager() || $user->can('tasks.create') || $user->subordinates()->exists();

        // Active tab: 'my' (default), 'created', 'team', 'overdue', 'daily'
        $tab = $request->get('tab', 'my');
        $validTabs = ['my', 'created', 'overdue', 'daily'];
        if ($isManager) {
            $validTabs[] = 'team';
        }
        if (! in_array($tab, $validTabs, true)) {
            $tab = 'my';
        }

        // View mode: 'list' (default), 'cards', or 'kanban'
        $view = $request->get('view', 'list');
        if (! in_array($view, ['list', 'cards', 'kanban'], true)) {
            $view = 'list';
        }

        $query = Task::with(['assignee', 'creator', 'project', 'assignments', 'activityLogs']);

        // Scope by tab
        if ($tab === 'my') {
            $query->where('assigned_to', $user->id);
        } elseif ($tab === 'created') {
            $query->where('assigned_by', $user->id);
        } elseif ($tab === 'daily') {
            $query->where('status', 'completed')
                ->whereDate('completed_at', Carbon::today());
            if (! $isManager) {
                $query->where('assigned_to', $user->id);
            }
        } elseif ($tab === 'overdue') {
            $query->overdue();
            if (! $isManager) {
                $query->where('assigned_to', $user->id);
            }
        } elseif ($tab === 'team' && $isManager) {
            if (! $user->isAdmin()) {
                $eligibleEmployees = $taskService->eligibleDelegates($user);
                $teamMemberIds = $eligibleEmployees->pluck('id')->push($user->id)->unique()->all();
                $query->where(function ($q) use ($teamMemberIds, $user) {
                    $q->whereIn('assigned_to', $teamMemberIds)
                      ->orWhere('assigned_by', $user->id);
                });
            }
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter: Priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Filter: Assignee (for team / manager views)
        if ($request->filled('assigned_to') && in_array($tab, ['team', 'created', 'overdue'], true)) {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        // Filter: Project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        // Filter: Department
        if ($request->filled('department')) {
            $deptVal = strtolower((string) $request->input('department'));
            $query->where(function ($q) use ($deptVal) {
                $q->whereRaw('LOWER(department) = ?', [$deptVal])
                  ->orWhereHas('assignee', function ($uq) use ($deptVal) {
                      $uq->whereRaw('LOWER(department) = ?', [$deptVal]);
                  });
            });
        }

        // Search: Task ID, title, project name, description, or outcomes
        if ($request->filled('search')) {
            $rawSearch = trim($request->input('search'));
            $search = '%' . $rawSearch . '%';
            $numericId = preg_replace('/[^0-9]/', '', $rawSearch);

            $query->where(function ($q) use ($search, $numericId, $rawSearch) {
                $q->where('title', 'like', $search)
                  ->orWhere('project_name', 'like', $search)
                  ->orWhere('description', 'like', $search)
                  ->orWhere('expected_outcomes', 'like', $search);

                if (is_numeric($rawSearch)) {
                    $q->orWhere('id', (int) $rawSearch);
                } elseif ($numericId !== '' && is_numeric($numericId)) {
                    $q->orWhere('id', (int) $numericId);
                }
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (in_array($sortBy, ['created_at', 'due_date', 'priority', 'status', 'progress', 'title'], true)) {
            if ($sortBy === 'priority') {
                $query->orderByRaw("FIELD(priority, 'urgent', 'critical', 'high', 'medium', 'low') {$sortDir}");
            } else {
                $query->orderBy($sortBy, $sortDir);
            }
        } else {
            $query->latest();
        }

        // Per-page pagination
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $tasks = $query->paginate($perPage)->withQueryString();

        // Calculate summary counters for header badges
        $counters = [
            'my_total' => Task::where('assigned_to', $user->id)->count(),
            'my_in_progress' => Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
            'my_in_review' => Task::where('assigned_to', $user->id)->where('status', 'in_review')->count(),
            'my_overdue' => Task::where('assigned_to', $user->id)->overdue()->count(),
            'created_by_me' => Task::where('assigned_by', $user->id)->count(),
            'pending_help' => TaskHelpRequest::where('helper_id', $user->id)->where('status', 'pending')->count(),
        ];

        // For delegation and creation options
        $eligibleEmployees = $taskService->eligibleDelegates($user);
        $projects = Project::forUser($user)->orderBy('title')->get(['id', 'title', 'department']);

        // Collect all distinct active departments
        $departments = Department::where('is_active', true)->orderBy('name')->pluck('name')
            ->merge(Task::whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department'))
            ->map(fn ($d) => strtolower(trim((string) $d)))
            ->unique()
            ->filter()
            ->values();

        // Department task counters for quick-classification pill bar
        $departmentCounters = [];
        $totalCompanyTasks = 0;
        if ($isManager) {
            $deptCountsRaw = Task::select('department', DB::raw('count(*) as total'))
                ->whereNotNull('department')
                ->where('department', '!=', '')
                ->groupBy('department')
                ->pluck('total', 'department')
                ->all();

            foreach ($departments as $dept) {
                $lowerD = strtolower($dept);
                $departmentCounters[$lowerD] = 0;
                foreach ($deptCountsRaw as $rawDept => $cnt) {
                    if (strtolower((string) $rawDept) === $lowerD) {
                        $departmentCounters[$lowerD] += (int) $cnt;
                    }
                }
            }

            $totalCompanyTasks = Task::count();
        }

        return view('tasks.index', compact(
            'tasks',
            'tab',
            'view',
            'counters',
            'isManager',
            'eligibleEmployees',
            'projects',
            'departments',
            'departmentCounters',
            'totalCompanyTasks'
        ));
    }

    /**
     * Store a newly created task.
     * Logged-in user is automatically creator and default assignee.
     */
    public function store(Request $request, TaskService $taskService)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expected_outcomes' => 'nullable|string',
            'priority' => ['sometimes', 'required', Rule::in(['low', 'medium', 'high', 'critical', 'urgent'])],
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'project_id' => 'nullable|exists:projects,id',
            'review_required' => 'nullable|boolean',
            'assigned_to' => 'nullable|exists:users,id',
            'assignment_notes' => 'nullable|string|max:1000',
            'department' => 'nullable|string|max:60',
        ]);

        $task = $taskService->createTask($request->user(), $validated);

        if ($request->filled('redirect_to') && $request->redirect_to === 'show') {
            return redirect()->route('tasks.show', $task)
                ->with('success', "Task \"{$task->title}\" created successfully.");
        }

        return redirect()->route('tasks.index')
            ->with('success', "Task \"{$task->title}\" created successfully.");
    }

    /**
     * Display detailed task view.
     */
    public function show(Task $task, TaskService $taskService)
    {
        $this->authorize('view', $task);

        $task->load([
            'assignee',
            'creator',
            'project',
            'assignments.assigner',
            'assignments.assignee',
            'activityLogs.user',
            'workLogs.user',
            'collaborators.user',
            'collaborators.addedBy',
            'helpRequests.requester',
            'helpRequests.helper',
            'comments.user',
            'comments.replies.user',
            'attachments.user',
            'reviewActions.reviewer',
        ]);

        $eligibleEmployees = $taskService->eligibleDelegates(auth()->user(), $task);
        $projects = Project::forUser(auth()->user())->orderBy('title')->get(['id', 'title', 'department']);

        return view('tasks.show', compact('task', 'eligibleEmployees', 'projects'));
    }

    /**
     * Update task details.
     */
    public function update(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'expected_outcomes' => 'nullable|string',
            'priority' => ['sometimes', 'required', Rule::in(['low', 'medium', 'high', 'critical', 'urgent'])],
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
            'project_id' => 'nullable|exists:projects,id',
            'review_required' => 'nullable|boolean',
            'assigned_to' => 'nullable|exists:users,id',
            'department' => 'nullable|string|max:60',
            'status' => ['nullable', 'string', Rule::in(['open', 'pending', 'in_progress', 'blocked', 'on_hold', 'in_review', 'completed', 'cancelled'])],
        ]);

        $task = $taskService->updateTask($task, $request->user(), $validated);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Delegate task to another eligible employee.
     */
    public function delegate(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('delegate', $task);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $task = $taskService->delegateTask(
            $task,
            $request->user(),
            (int) $validated['assigned_to'],
            $validated['notes'] ?? null
        );

        return redirect()->route('tasks.show', $task)
            ->with('success', "Task successfully delegated to {$task->assignee->name}.");
    }

    /**
     * Escalate an active task to Urgent priority by Administrator with a required directive.
     */
    public function escalate(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('escalate', $task);

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:1000',
        ]);

        $task = $taskService->escalateTask(
            $task,
            $request->user(),
            trim($validated['reason'])
        );

        $assigneeName = $task->assignee ? $task->assignee->name : 'the assignee';

        return redirect()->back()
            ->with('success', "Task has been escalated to Urgent and direct escalation notice dispatched to {$assigneeName}.");
    }

    /**
     * Request rework on a task (even after it is completed).
     * Authorized for Task Creator, Department Manager, and Administrators.
     */
    public function rework(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('rework', $task);

        $validated = $request->validate([
            'notes' => 'nullable|string|min:3|max:2000',
            'rework_notes' => 'nullable|string|min:3|max:2000',
            'due_date' => 'nullable|date',
        ]);

        $notes = trim((string) ($request->input('rework_notes') ?: $request->input('notes') ?: ''));
        if (mb_strlen($notes) < 3) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'rework_notes' => 'Rework instructions must be at least 3 characters.',
            ]);
        }

        $task = $taskService->requestRework(
            $task,
            $request->user(),
            $notes,
            $validated['due_date'] ?? null
        );

        $assigneeName = $task->assignee ? $task->assignee->name : 'the assignee';

        return redirect()->route('tasks.show', $task)
            ->with('success', "Task has been sent back for re-work and notification dispatched to {$assigneeName}.");
    }

    /**
     * Request help from an eligible colleague.
     */
    public function requestHelp(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('requestHelp', $task);

        $validated = $request->validate([
            'helper_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $taskService->requestHelp($task, $request->user(), (int) $validated['helper_id'], $validated['message']);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Help request dispatched successfully.');
    }

    /**
     * Helper responds (accept / reject) to help request.
     */
    public function respondHelp(Request $request, Task $task, TaskHelpRequest $helpRequest, TaskService $taskService)
    {
        $this->authorize('respondHelp', [$task, $helpRequest]);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'rejected'])],
            'response_notes' => 'nullable|string|max:1000',
        ]);

        $taskService->respondHelp($helpRequest, $request->user(), $validated['status'], $validated['response_notes'] ?? null);

        $action = $validated['status'] === 'accepted' ? 'accepted' : 'declined';
        return redirect()->route('tasks.show', $task)
            ->with('success', "Help request {$action}.");
    }

    /**
     * Add collaborator to task.
     */
    public function addCollaborator(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('manageCollaborators', $task);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|string|max:50',
        ]);

        $taskService->addCollaborator($task, $request->user(), (int) $validated['user_id'], $validated['role'] ?? 'collaborator');

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Collaborator added successfully.');
    }

    /**
     * Remove collaborator from task.
     */
    public function removeCollaborator(Request $request, Task $task, int $collaboratorId, TaskService $taskService)
    {
        $this->authorize('manageCollaborators', $task);

        $taskService->removeCollaborator($task, $request->user(), $collaboratorId);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Collaborator removed successfully.');
    }

    /**
     * Post a comment or reply.
     */
    public function addComment(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('comment', $task);

        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:task_comments,id',
        ]);

        $taskService->addComment($task, $request->user(), $validated['comment'], $validated['parent_id'] ?? null);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Comment posted.');
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Request $request, Task $task, TaskComment $comment)
    {
        $user = $request->user();
        if (! $user->isAdmin() && (int) $comment->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized to delete this comment.');
        }

        $comment->delete();

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Comment deleted.');
    }

    /**
     * Upload an attachment.
     */
    public function uploadAttachment(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('uploadAttachment', $task);

        $request->validate([
            'file' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg,webp,svg,gif,zip,rar,txt,json',
        ]);

        $taskService->uploadAttachment($task, $request->user(), $request->file('file'));

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))
                ->with('success', 'File attached successfully.');
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'File attached successfully.');
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(Request $request, Task $task, TaskAttachment $attachment)
    {
        $this->authorize('view', $task);

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'File not found on storage.');
        }

        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(Request $request, Task $task, TaskAttachment $attachment, TaskService $taskService)
    {
        $this->authorize('deleteAttachment', [$task, $attachment]);

        $taskService->deleteAttachment($attachment, $request->user());

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Attachment deleted.');
    }

    /**
     * Submit task for review.
     */
    public function submitForReview(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('submitReview', $task);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $taskService->submitForReview($task, $request->user(), $validated['notes'] ?? null);

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task has been submitted for review.');
    }

    /**
     * Reviewer decides (approve / request changes).
     */
    public function decideReview(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('decideReview', $task);

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'changes_requested'])],
            'notes' => 'nullable|string|max:1000',
        ]);

        $taskService->decideReview($task, $request->user(), $validated['decision'], $validated['notes'] ?? null);

        $label = $validated['decision'] === 'approved' ? 'approved' : 'returned for changes';
        return redirect()->route('tasks.show', $task)
            ->with('success', "Task has been {$label}.");
    }

    /**
     * Update task status (and optional progress).
     */
    public function updateStatus(Request $request, Task $task, TaskService $taskService)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'blocked', 'on_hold', 'in_review', 'completed', 'cancelled'])],
            'progress' => 'nullable|integer|min:0|max:100',
            'comment' => 'nullable|string|max:1000',
        ]);

        $task = $taskService->updateStatus(
            $task,
            $request->user(),
            $validated['status'],
            isset($validated['progress']) ? (int) $validated['progress'] : null,
            $validated['comment'] ?? null
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $task->status,
                'status_label' => $task->statusLabel(),
                'status_classes' => $task->statusBadgeClasses(),
                'progress' => $task->progress,
                'is_overdue' => $task->isOverdue(),
                'overdue_badge' => $task->overdueBadgeLabel(),
                'message' => 'Status updated successfully.',
            ]);
        }

        return redirect()->back()->with('success', "Task status updated to {$task->statusLabel()}.");
    }

    /**
     * Quick toggle complete / open.
     */
    public function toggleComplete(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $completed = $task->toggleCompleted();
        $label = $completed ? 'completed' : 're-opened';

        if ($completed) {
            try {
                app(\App\Services\NotificationDispatcher::class)->taskCompleted($task, $request->user());
            } catch (\Throwable) {
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            $payload = [
                'success' => true,
                'task_id' => $task->id,
                'is_completed' => $completed,
                'completed' => $completed,
                'status' => $task->status,
                'message' => "Task marked as {$label}.",
            ];

            if ($task->project_id && $task->project) {
                $proj = $task->project;
                $payload['project_id'] = $proj->id;
                $payload['project_stats'] = [
                    'total' => $proj->tasks()->count(),
                    'completed' => $proj->tasks()->where('status', 'completed')->count(),
                    'percentage' => $proj->completionPercentage(),
                ];
            }

            return response()->json($payload);
        }

        return redirect()->back()->with('success', "Task marked as {$label}.");
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $title = $task->title;
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', "Task \"{$title}\" has been deleted.");
    }

    /**
     * Fetch the authenticated user's active tasks for the Punch Out modal.
     */
    public function myActiveTasks(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $tasks = Task::where('assigned_to', $user->id)
            ->where(function ($q) use ($today) {
                $q->whereIn('status', ['open', 'in_progress', 'blocked', 'on_hold', 'in_review'])
                  ->orWhere(function ($sub) use ($today) {
                      $sub->where('status', 'completed')
                          ->whereDate('completed_at', $today);
                  });
            })
            ->select('id', 'title', 'project_name', 'status')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'tasks' => $tasks->map(fn ($t) => [
                'id' => $t->id,
                'title' => $t->title,
                'project_name' => $t->project_name ?? '—',
                'status' => $t->status,
                'status_label' => $t->statusLabel(),
            ]),
        ]);
    }
}
