<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a project and its work tasks checklist.
     */
    public function show(Request $request, Project $project)
    {
        $this->authorize('viewAny', Task::class);

        $user = $request->user();

        if (! $project->canBeAccessedBy($user)) {
            abort(403, 'Access denied. You do not have permission to view projects from another department.');
        }

        $isManager = $user->isAdmin() || $user->isManager() || $user->can('tasks.create') || $user->subordinates()->exists();

        // Assignable staff for Add/Edit task modals
        $employeeQuery = User::active()->with('roles')
            ->where(function ($q) {
                $q->whereNull('department')
                    ->orWhereRaw('LOWER(department) != ?', ['sales']);
            });

        if (! $user->isAdmin()) {
            $employeeQuery->where(function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                    ->orWhere('id', $user->id);
                if ($user->department) {
                    $q->orWhere('department', $user->department);
                }
            });
        } else {
            $employeeQuery->where(function ($q) use ($user) {
                $q->whereDoesntHave('roles', fn ($r) => $r->where('name', \App\Rbac\Rbac::ADMIN_ROLE))
                    ->orWhere('id', $user->id);
            });
        }

        $employees = $employeeQuery->orderBy('name')->get();

        // Business rule: Regular employees cannot assign tasks to managers or administrators
        if (! $user->isAdmin() && ! $user->isManager()) {
            $employees = $employees->filter(function ($emp) use ($user) {
                if ($emp->id === $user->id) {
                    return true;
                }
                if ($user->manager_id && (int) $emp->id === (int) $user->manager_id) {
                    return false;
                }
                if ($emp->isManager() || $emp->isAdmin()) {
                    return false;
                }
                return true;
            })->values();
        }
        $departments = Department::where('is_active', true)->orderBy('name')->pluck('name');
        if ($departments->isEmpty()) {
            $departments = User::active()->whereNotNull('department')->distinct()->pluck('department');
        }

        // Tasks query for this project
        $taskQuery = $project->tasks()->with(['assignee', 'creator', 'workLogs', 'attachments.user']);

        // Non-admins only see tasks belonging to their department/team
        if (! $user->isAdmin()) {
            $teamMemberIds = $employees->pluck('id')->push($user->id)->unique()->all();
            $taskQuery->where(function ($q) use ($teamMemberIds, $user) {
                $q->whereIn('assigned_to', $teamMemberIds)
                  ->orWhere('assigned_by', $user->id);
                if ($user->department) {
                    $q->orWhere('department', $user->department);
                }
            });
        }

        if ($request->filled('status')) {
            $taskQuery->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $taskQuery->where('priority', $request->input('priority'));
        }

        if ($request->filled('assigned_to')) {
            $taskQuery->where('assigned_to', $request->input('assigned_to'));
        }

        if ($request->filled('search')) {
            $rawSearch = trim($request->input('search'));
            $search = '%' . $rawSearch . '%';
            $numericId = preg_replace('/[^0-9]/', '', $rawSearch);

            $taskQuery->where(function ($q) use ($search, $numericId, $rawSearch) {
                $q->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search);

                if (is_numeric($rawSearch)) {
                    $q->orWhere('id', (int) $rawSearch);
                } elseif ($numericId !== '' && is_numeric($numericId)) {
                    $q->orWhere('id', (int) $numericId);
                }
            });
        }

        // Metrics scoped to this user's visible tasks
        $statsQuery = (clone $taskQuery);
        $totalTasks = (clone $statsQuery)->count();
        $completedTasks = (clone $statsQuery)->where('status', 'completed')->count();
        $pendingTasks = (clone $statsQuery)->where('status', 'pending')->count();
        $inProgressTasks = (clone $statsQuery)->where('status', 'in_progress')->count();
        $completion = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $tasks = $taskQuery->orderByRaw("CASE 
                WHEN status = 'in_progress' THEN 1 
                WHEN status = 'pending' THEN 2 
                WHEN status = 'completed' THEN 3 
                ELSE 4 END")
            ->orderByRaw("due_date IS NULL, due_date ASC")
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('projects.show', compact(
            'project',
            'tasks',
            'employees',
            'departments',
            'isManager',
            'perPage',
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'inProgressTasks',
            'completion'
        ));
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'client_name' => 'nullable|string|max:150',
            'department' => 'nullable|string|max:60',
            'color' => 'nullable|string|in:indigo,violet,emerald,amber,rose,sky',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:active,completed,on_hold,archived',
        ]);

        $user = $request->user();
        $department = $user->isAdmin() ? ($validated['department'] ?? null) : $user->department;

        $project = Project::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'department' => $department,
            'color' => $validated['color'] ?? 'indigo',
            'due_date' => $validated['due_date'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'created_by' => $user->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'project' => $project,
                'message' => "Project \"{$project->title}\" created successfully.",
            ]);
        }

        return redirect()->route('tasks.index')->with('success', "Project \"{$project->title}\" created successfully.");
    }

    /**
     * Update an existing project.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('create', Task::class);

        $user = $request->user();
        if (! $project->canBeManagedBy($user)) {
            abort(403, 'Access denied. You can only edit projects belonging to your department.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'client_name' => 'nullable|string|max:150',
            'department' => 'nullable|string|max:60',
            'color' => 'nullable|string|in:indigo,violet,emerald,amber,rose,sky',
            'due_date' => 'nullable|date',
            'status' => 'required|in:active,completed,on_hold,archived',
        ]);

        if (! $user->isAdmin() && $project->department) {
            // Keep the original department if user is not admin
            unset($validated['department']);
        }

        $project->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'project' => $project,
                'message' => "Project \"{$project->title}\" updated successfully.",
            ]);
        }

        return back()->with('success', "Project \"{$project->title}\" updated successfully.");
    }

    /**
     * Delete a project.
     */
    public function destroy(Request $request, Project $project)
    {
        $this->authorize('create', Task::class);

        if (! $project->canBeManagedBy($request->user())) {
            abort(403, 'Access denied. You can only delete projects belonging to your department.');
        }

        $title = $project->title;
        $project->delete();

        return redirect()->route('tasks.index')->with('success', "Project \"{$title}\" deleted successfully.");
    }
}
