{{--
 | Shared create/edit form fields for a user.
 | $user   : null on create, the User model on edit
 | $roles  : Spatie roles for the dropdown
 | $managers: eligible managers (admins + managers, excluding self)
 | $departments : allowed department values
 --}}

@if ($errors->any())
    <div class="mb-5 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside space-y-0.5 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
    {{-- Name --}}
    <div>
        <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
            Name *</label>
        <input type="text" name="name" id="name" required value="{{ old('name', $user?->name) }}"
            class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
            Email *</label>
        <input type="email" name="email" id="email" required value="{{ old('email', $user?->email) }}"
            class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
    </div>

    {{-- Password --}}
    <div>
        <label for="password" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
            Password {{ $user ? '' : '*' }}</label>
        <input type="password" name="password" id="password" {{ $user ? '' : 'required' }} minlength="8"
            placeholder="{{ $user ? 'Leave blank to keep the current password' : 'At least 8 characters' }}"
            class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
    </div>

    {{-- Confirm password --}}
    <div>
        <label for="password_confirmation"
            class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
            Confirm Password {{ $user ? '' : '*' }}</label>
        <input type="password" name="password_confirmation" id="password_confirmation" {{ $user ? '' : 'required' }}
            minlength="8"
            class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
    </div>

    {{-- Role --}}
    <div>
        <label for="role" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
            Role *</label>
        <select name="role" id="role" required
            class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            <option value="">Choose a role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}"
                    {{ old('role', $user?->getRoleNames()->first()) === $role->name ? 'selected' : '' }}>
                    {{ ucfirst($role->name) }}</option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            Admin always has full access. Other roles take effect as soon as permissions are set.
        </p>
    </div>

    {{-- Department --}}
    <div>
        <label for="department" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
            Department</label>
        <select name="department" id="department"
            class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            <option value="">None</option>
            @foreach ($departments as $department)
                <option value="{{ $department }}" {{ old('department', $user?->department) === $department ? 'selected' : '' }}>
                    {{ ucfirst($department) }}</option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            Required for sales, marketing, development and hr. Not used by admin or manager.
        </p>
    </div>

    {{-- Manager --}}
    <div>
        <label for="manager_id" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
            Manager</label>
        <select name="manager_id" id="manager_id"
            class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            <option value="">No manager</option>
            @foreach ($managers as $manager)
                <option value="{{ $manager->id }}" {{ (int) old('manager_id', $user?->manager_id) === $manager->id ? 'selected' : '' }}>
                    {{ $manager->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Active toggle --}}
    <div class="flex items-end pb-2">
        <label for="is_active" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                @checked(old('is_active', $user?->is_active ?? true))
                class="rounded border-gray-300 dark:border-gray-600 text-gray-500 focus:ring-gray-500">
            <span class="font-semibold">Account is active</span>
        </label>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit"
        class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm shadow-md transition-all duration-200">
        {{ $user ? 'Save Changes' : 'Create User' }}
    </button>
    <a href="{{ route('admin.users.index') }}"
        class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200">
        Cancel
    </a>
</div>