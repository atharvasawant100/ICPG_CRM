<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesByPermission;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Rbac\Rbac;
use App\Services\UserManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use AuthorizesByPermission;
    public function __construct(private readonly UserManagementService $users) {}

    /**
     * List users with search, filters and pagination.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('users.view');

        $query = User::query()->with(['manager', 'roles'])->latest();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->string('role')->trim()->value()) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($department = $request->string('department')->trim()->value()) {
            $query->where('department', $department);
        }

        if ($status = $request->string('status')->trim()->value()) {
            $status === 'active'
                ? $query->where('is_active', true)
                : $query->where('is_active', false);
        }

        $users = $query->paginate(15)->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'departments' => ['sales', 'marketing', 'development', 'hr'],
        ]);
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        $this->authorizePermission('users.create');

        return view('admin.users.create', $this->formOptions(null));
    }

    /**
     * Create a user.
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorizePermission('users.create');

        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed by the model cast
            'department' => $data['department'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Role goes through the single sync wrapper, never users.role directly.
        $this->users->assignRole($user, $data['role']);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created successfully.");
    }

    /**
     * Show the edit form.
     */
    public function edit(User $user)
    {
        $this->authorizePermission('users.edit');

        return view('admin.users.edit', $this->formOptions($user));
    }

    /**
     * Update a user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorizePermission('users.edit');

        $data = $request->validated();

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'department' => $data['department'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Only touch the password when one was actually supplied.
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $wasActive = (bool) $user->getOriginal('is_active');
        $previousRole = $user->getRoleNames()->first();

        $user->save();

        // Role always goes through the wrapper, which keeps users.role in sync.
        // Comparing against the pre-update value avoids a pointless write.
        if ($previousRole !== $data['role']) {
            $this->users->assignRole($user, $data['role']);
        }

        // Deactivating must also end any active session.
        if ($wasActive && ! $user->is_active) {
            $this->users->revokeSessions($user);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} updated successfully.");
    }

    /**
     * Toggle a user's active state.
     */
    public function toggleStatus(Request $request, User $user)
    {
        $this->authorizePermission('users.edit');

        $activate = $request->boolean('activate');

        // Rule 1: cannot deactivate yourself.
        if (! $activate && $request->user()->id === $user->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        // Rule 2: never remove the last active admin.
        if (! $activate && $this->users->wouldOrphanAdmins($user, false)) {
            return back()->with('error', 'This is the last active admin. Promote another admin first.');
        }

        $this->users->setActive($user, $activate);

        return back()->with('success', $activate
            ? "{$user->name} reactivated successfully."
            : "{$user->name} deactivated and signed out.");
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorizePermission('users.edit');

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'must_change' => ['sometimes', 'boolean'],
        ], [
            'password.required' => 'Please enter a new password.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The two passwords do not match.',
        ]);

        $this->users->resetPassword(
            $user,
            $validated['password'],
            $request->boolean('must_change')
        );

        return back()->with('success', "Password reset for {$user->name}. They have been signed out.");
    }

    /**
     * Shared select options for the create and edit forms.
     */
    private function formOptions(?User $user): array
    {
        // Managers are admins, plus anyone whose role can have reports.
        $managers = User::query()
            ->active()
            ->where(function ($q) use ($user) {
                $q->whereHas('roles', fn ($r) => $r->where('name', Rbac::ADMIN_ROLE))
                    ->orWhereHas('roles', fn ($r) => $r->where('name', 'manager'));
            })
            ->when($user, fn ($q) => $q->whereKeyNot($user->id))
            ->orderBy('name')
            ->get();

        return [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'managers' => $managers,
            'departments' => ['sales', 'marketing', 'development', 'hr'],
        ];
    }
}
