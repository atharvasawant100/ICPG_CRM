{{--
 | Shared create/edit form fields for a user.
 |
 | $user               : null on create, the User model on edit
 | $departments        : department names for the dropdown
 | $designations       : department name => [slug => label], drives the
 |                       dependent dropdown without a second request
 | $defaultDesignations: the list shown before a department is chosen
 --}}

{{-- Validation Errors --}}
@if ($errors->any())
    <div class="mb-8 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Please review the following errors:</h3>
                <ul class="text-xs text-red-700 dark:text-red-300 list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- 2-Column Responsive Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- EMP ID --}}
    <div>
        <label for="emp_id" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
            Employee ID <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(EMP ID)</span>
        </label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                </svg>
            </div>
            <input type="text" name="emp_id" id="emp_id"
                value="{{ old('emp_id', $user?->emp_id ?? ($nextEmpId ?? \App\Models\User::nextEmpId())) }}"
                placeholder="e.g. ICPG_01"
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all uppercase placeholder:text-gray-400 @error('emp_id') border-red-500 @enderror">
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Auto-generated unique ID (e.g. ICPG_01). Editable if required.</p>
    </div>

    {{-- Name --}}
    <div>
        <label for="name" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
            Full Name <span class="text-red-500">*</span>
        </label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <input type="text" name="name" id="name" required value="{{ old('name', $user?->name) }}"
                placeholder="e.g. Rahul Sharma"
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('name') border-red-500 @enderror">
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Official full name for company communications and payroll</p>
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
            Work Email Address <span class="text-red-500">*</span>
        </label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <input type="email" name="email" id="email" required value="{{ old('email', $user?->email) }}"
                placeholder="e.g. employee@company.com"
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('email') border-red-500 @enderror">
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Used for system login credentials and notifications</p>
    </div>

    {{-- Department --}}
    <div>
        <label for="department" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
            Department
        </label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <select name="department" id="department"
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('department') border-red-500 @enderror">
                <option value="">None / Unassigned</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept }}" {{ old('department', $user?->department) === $dept ? 'selected' : '' }}>
                        {{ ucfirst($dept) }}
                    </option>
                @endforeach
            </select>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Determines available designations, reporting manager, and department scope</p>
    </div>

    {{-- Designation. Options depend on department --}}
    <div>
        <label for="designation" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
            Designation (Job Title)
        </label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <select name="designation" id="designation"
                data-default-options='@json($defaultDesignations)'
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('designation') border-red-500 @enderror">
                <option value="">None / Unassigned</option>
                @php
                    $currentDept = old('department', $user?->department);
                    $initialDesignations = $currentDept
                        ? ($designations[$currentDept] ?? $designations[strtolower(trim($currentDept))] ?? $defaultDesignations)
                        : $defaultDesignations;
                    $selectedDesignation = old('designation', $user?->designation);
                @endphp
                @foreach ($initialDesignations as $slug => $label)
                    <option value="{{ $slug }}" {{ $selectedDesignation === $slug ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Options adapt automatically to the selected department</p>
        
        <div id="manager-assignment-badge" class="hidden mt-2.5 p-3 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-700 dark:text-blue-300 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>This user will automatically receive the <strong>Manager Role</strong> for <span id="manager-dept-name" class="font-bold">the selected department</span>.</span>
        </div>
    </div>

    {{-- Grid alignment placeholder --}}
    <div class="hidden md:block"></div>

    {{-- Password --}}
    <div>
        <label for="password" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
            Password {{ $user ? '(Optional)' : '*' }}
        </label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <input type="password" name="password" id="password" {{ $user ? '' : 'required' }} minlength="8"
                placeholder="{{ $user ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}"
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('password') border-red-500 @enderror">
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">{{ $user ? 'Only fill if you want to set a new password' : 'Must be at least 8 characters' }}</p>
    </div>

    {{-- Confirm Password --}}
    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
            Confirm Password {{ $user ? '(Optional)' : '*' }}
        </label>
        <div class="relative rounded-xl shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <input type="password" name="password_confirmation" id="password_confirmation" {{ $user ? '' : 'required' }} minlength="8"
                placeholder="Repeat password"
                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Must match the password entered on the left</p>
    </div>

</div>

<!-- Bottom Action Bar -->
<div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
    <a href="{{ route('admin.users.index') }}"
        class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
        Cancel
    </a>
    <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
        <button type="submit"
            class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
            {{ $user ? 'Save Changes' : 'Create User' }}
        </button>
    </div>
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
            var rawDept = departmentSelect.value || '';
            var dept = rawDept.trim();
            var options = dept !== ''
                ? (designationsByDepartment[dept] || designationsByDepartment[dept.toLowerCase()] || defaultOptions)
                : defaultOptions;

            var current = keepCurrent ? designationSelect.value : '';
            designationSelect.innerHTML = '';

            var none = document.createElement('option');
            none.value = '';
            none.textContent = 'None / Unassigned';
            designationSelect.appendChild(none);

            Object.keys(options).forEach(function(slug) {
                var option = document.createElement('option');
                option.value = slug;
                option.textContent = options[slug];
                designationSelect.appendChild(option);
            });

            // Only keep the old value when it is still on offer.
            designationSelect.value = Object.prototype.hasOwnProperty.call(options, current) ? current : '';
            updateManagerNotice();
        }

        function updateManagerNotice() {
            var badge = document.getElementById('manager-assignment-badge');
            var deptNameSpan = document.getElementById('manager-dept-name');
            if (!badge) return;

            var designation = (designationSelect.value || '').toLowerCase();
            var department = (departmentSelect.value || '').trim();

            var isManager = designation.indexOf('manager') !== -1;

            if (isManager && department) {
                if (deptNameSpan) {
                    deptNameSpan.textContent = department.charAt(0).toUpperCase() + department.slice(1);
                }
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        departmentSelect.addEventListener('change', function() {
            refresh(false);
        });

        designationSelect.addEventListener('change', function() {
            updateManagerNotice();
        });

        // If department is already chosen on load (e.g. edit mode, preselection, or back button)
        if (departmentSelect.value) {
            refresh(true);
        } else {
            updateManagerNotice();
        }
    })();
</script>