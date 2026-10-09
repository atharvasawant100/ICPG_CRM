@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-3 sm:mb-4">Users</h2>

        @can('users.create')
            <a href="{{ route('admin.users.create') }}"
                class="inline-flex items-center bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add User
            </a>
        @endcan
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 mb-4 sm:mb-5 md:mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('admin.users.index') }}"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2">
                <label for="search"
                    class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                    placeholder="Name or email..."
                    class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            </div>

            <div>
                <label for="role"
                    class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Role</label>
                <select name="role" id="role"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="department"
                    class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Department</label>
                <select name="department" id="department"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    <option value="">All departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department }}" {{ request('department') === $department ? 'selected' : '' }}>
                            {{ ucfirst($department) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status"
                    class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Status</label>
                <select name="status" id="status"
                    class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-5 flex gap-3">
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                    Apply Filters
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        @foreach (['Name', 'Designation', 'Role', 'Department', 'Manager', 'Status', 'Created', 'Actions'] as $heading)
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800 dark:text-white">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ \App\Rbac\Designation::label($user->designation) ?? '-' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $roleName = $user->getRoleNames()->first(); @endphp
                                @if ($roleName)
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        {{ ucfirst($roleName) }}</span>
                                @else
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        No role</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->department ? ucfirst($user->department) : '-' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->manager?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($user->is_active)
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active</span>
                                @else
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactive</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->created_at?->format('d M Y') }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @can('users.edit')
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition-colors">
                                            Edit
                                        </a>

                                        @if (auth()->id() !== $user->id)
                                            <form action="{{ route('admin.users.toggle', $user) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('{{ $user->is_active ? 'Deactivate' : 'Reactivate' }} {{ addslashes($user->name) }}?{{ $user->is_active ? ' They will be signed out immediately.' : '' }}');">
                                                @csrf
                                                <input type="hidden" name="activate" value="{{ $user->is_active ? '0' : '1' }}">
                                                <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $user->is_active ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60' : 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:text-green-300 dark:hover:bg-green-900/60' }} transition-colors">
                                                    {{ $user->is_active ? 'Deactivate' : 'Reactivate' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400 italic">This is you</span>
                                        @endif
                                    @endcan

                                    @can('users.edit')
                                        <button type="button"
                                            onclick="openResetPassword({{ $user->id }}, @js($user->name))"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:hover:bg-amber-900/60 transition-colors">
                                            Reset Password
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards: the table would be unreadable at phone width. --}}
        <div class="md:hidden divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($users as $user)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-800 dark:text-white truncate">{{ $user->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</div>
                        </div>
                        @if ($user->is_active)
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 flex-shrink-0">
                                Active</span>
                        @else
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 flex-shrink-0">
                                Inactive</span>
                        @endif
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1 mb-3">
                        @php $roleName = $user->getRoleNames()->first(); @endphp
                        <div>Role: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $roleName ? ucfirst($roleName) : 'None' }}</span></div>
                        <div>Department: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $user->department ? ucfirst($user->department) : '-' }}</span></div>
                        <div>Manager: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $user->manager?->name ?? '-' }}</span></div>
                    </div>

                    @can('users.edit')
                        <div class="flex gap-2 flex-wrap">
                            <a href="{{ route('admin.users.edit', $user) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                Edit</a>
                            @if (auth()->id() !== $user->id)
                                <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="inline"
                                    onsubmit="return confirm('{{ $user->is_active ? 'Deactivate' : 'Reactivate' }} {{ addslashes($user->name) }}?');">
                                    @csrf
                                    <input type="hidden" name="activate" value="{{ $user->is_active ? '0' : '1' }}">
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold {{ $user->is_active ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $user->is_active ? 'Deactivate' : 'Reactivate' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endcan
                </div>
            @empty
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">No users found.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    {{-- Reset password modal --}}
    @can('users.edit')
        <div id="reset-password-modal"
            class="hidden fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 z-[9999] flex items-center justify-center p-4"
            role="dialog" aria-modal="true" aria-labelledby="reset-password-title">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full p-6">
                <h3 id="reset-password-title" class="text-lg font-bold text-gray-800 dark:text-white mb-1">
                    Reset Password</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                    Set a new password for <span id="reset-password-user" class="font-semibold"></span>.
                    They will be signed out of all sessions.
                </p>

                <form id="reset-password-form" method="POST" action="">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="reset-password-new"
                                class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">New Password *</label>
                            <input type="password" name="password" id="reset-password-new" required minlength="8"
                                class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label for="reset-password-confirm"
                                class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Confirm Password *</label>
                            <input type="password" name="password_confirmation" id="reset-password-confirm" required minlength="8"
                                class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="must_change" value="1"
                                class="rounded border-gray-300 dark:border-gray-600 text-gray-500 focus:ring-gray-500">
                            Require a password change on next login
                        </label>
                    </div>

                    <div id="reset-password-errors" class="hidden mt-4"></div>

                    <div class="mt-6 flex gap-3 justify-end">
                        <button type="button" onclick="closeResetPassword()"
                            class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit"
                            class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
                            Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endsection

{{-- No @section('scripts') in layouts.app; scripts are inlined below. --}}
<script>
        function openResetPassword(userId, userName) {
            var modal = document.getElementById('reset-password-modal');
            if (!modal) return;

            document.getElementById('reset-password-form').action = "{{ route('admin.users.index') }}/" + userId + "/reset-password";
            document.getElementById('reset-password-user').textContent = userName;
            document.getElementById('reset-password-new').value = '';
            document.getElementById('reset-password-confirm').value = '';
            document.getElementById('reset-password-errors').classList.add('hidden');

            modal.classList.remove('hidden');
            document.getElementById('reset-password-new').focus();
        }

        function closeResetPassword() {
            var modal = document.getElementById('reset-password-modal');
            if (modal) modal.classList.add('hidden');
        }

        (function () {
            var modal = document.getElementById('reset-password-modal');
            if (!modal) return;

            modal.addEventListener('click', function (e) {
                if (e.target === this) closeResetPassword();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeResetPassword();
            });
        })();
</script>