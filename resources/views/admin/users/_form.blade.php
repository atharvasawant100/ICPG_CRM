{{--
 | Shared create/edit form fields for a user.
 |
 | $user               : null on create, the User model on edit
 | $departments        : department names for the dropdown
 | $designations       : department name => [slug => label], drives the
 |                       dependent dropdown without a second request
 | $defaultDesignations: the list shown before a department is chosen
 |
 | There is deliberately no Role field and no Manager field. Access is derived
 | from (department, designation) by App\Rbac\Designation, and the manager comes
 | from the department. Both are decided in the request, never in the browser.
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

    {{-- Designation. Options depend on the department chosen below. --}}
    <div>
        <label for="designation"
            class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">
            Designation</label>
        <select name="designation" id="designation"
            data-default-options='@json($defaultDesignations)'
            class="shadow-sm border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-gray-300 dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
            <option value="">None</option>
            {{-- Rendered server-side so the correct list is present before any
                 JavaScript runs; the script below takes over on change. --}}
            @php
                $initialDesignations = $user?->department
                    ? ($designations[$user->department] ?? $defaultDesignations)
                    : $defaultDesignations;
                $selectedDesignation = old('designation', $user?->designation);
            @endphp
            @foreach ($initialDesignations as $slug => $label)
                <option value="{{ $slug }}" {{ $selectedDesignation === $slug ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            The options change with the department.
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
                <option value="{{ $department }}"
                    {{ old('department', $user?->department) === $department ? 'selected' : '' }}>
                    {{ ucfirst($department) }}
                </option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
            Decides what the user can reach, so leave it blank only for an
            account you have not configured yet.
        </p>
    </div>
</div>

{{-- is_active is intentionally not a form field. New users are active, and
     deactivation happens from the Users list, which also ends their session. --}}

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

<script>
    /*
     | Dependent Designation dropdown.
     |
     | The whole map ships with the page, so changing department re-renders the
     | designation list instantly with no request. A designation that is no
     | longer offered is cleared, so a stale value cannot be submitted.
     */
    (function() {
        var designationsByDepartment = @json($designations);
        var designationSelect = document.getElementById('designation');
        var departmentSelect = document.getElementById('department');

        if (!designationSelect || !departmentSelect) return;

        var defaultOptions = JSON.parse(designationSelect.dataset.defaultOptions || '{}');

        function refresh(keepCurrent) {
            var department = departmentSelect.value;
            var options = designationsByDepartment[department] || defaultOptions;

            var current = keepCurrent ? designationSelect.value : '';
            designationSelect.innerHTML = '';

            var none = document.createElement('option');
            none.value = '';
            none.textContent = 'None';
            designationSelect.appendChild(none);

            Object.keys(options).forEach(function(slug) {
                var option = document.createElement('option');
                option.value = slug;
                option.textContent = options[slug];
                designationSelect.appendChild(option);
            });

            // Only keep the old value when it is still on offer.
            designationSelect.value = Object.prototype.hasOwnProperty.call(options, current) ? current : '';
        }

        departmentSelect.addEventListener('change', function() {
            refresh(false);
        });
    })();
</script>