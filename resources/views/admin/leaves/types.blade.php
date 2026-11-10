@extends('layouts.app')

@section('title', 'Leave Types & Settings')
@section('page-title', 'Leave Types & Settings')

@section('content')
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Leave Types & Quotas</h2>
        </div>


    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Types List (2 cols) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-white text-base">Active Leave Types</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Leave Type</th>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Annual Quota</th>
                            <th class="px-4 py-3">Pay Rule</th>
                            <th class="px-4 py-3 text-center">Requests</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($leaveTypes as $type)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition">
                                <td class="px-4 py-3.5 font-semibold text-gray-900 dark:text-white">
                                    {{ $type->name }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex px-2 py-0.5 rounded font-mono text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                                        {{ $type->code }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 font-bold text-gray-900 dark:text-white">
                                    <span class="text-base text-indigo-600 dark:text-indigo-400">{{ (float)$type->days_allowed_per_year }}</span>
                                    <span class="text-xs font-normal text-gray-500">days / year</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $type->is_paid ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                        {{ $type->is_paid ? 'Paid Leave' : 'Unpaid / Loss of Pay' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $type->requests_count }}
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <button type="button"
                                        onclick="openEditModal({{ json_encode($type) }})"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">
                                        Edit Quota
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Leave Type Form (1 col) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-bold text-gray-800 dark:text-white text-base mb-1">Add Leave Type</h3>
            <p class="text-xs text-gray-400 mb-4">Define a new category and its annual allowance.</p>

            <form method="POST" action="{{ route('admin.leaves.types.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Bereavement Leave"
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Short Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" required placeholder="e.g. BL" maxlength="20"
                        class="w-full text-sm uppercase border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Annual Allowance (Days) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.5" name="days_allowed_per_year" required value="5" min="0" max="365"
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Compensation <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-4 text-sm mt-1">
                        <label class="inline-flex items-center">
                            <input type="radio" name="is_paid" value="1" checked class="text-gray-800 focus:ring-gray-400 mr-1.5">
                            Paid
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="is_paid" value="0" class="text-gray-800 focus:ring-gray-400 mr-1.5">
                            Unpaid (LOP)
                        </label>
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm shadow-md shadow-blue-500/20 transition-all duration-200">
                        Add Leave Type
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Leave Type Modal -->
    <div id="editModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-md w-full p-6 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700 mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Leave Type</h3>
                    <p class="text-xs text-gray-400" id="editModalSubtitle">Update quota and properties</p>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="editForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_name" name="name" required
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Annual Allowance (Number of Days) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-lg shadow-sm">
                        <input type="number" step="0.5" id="edit_days" name="days_allowed_per_year" required min="0" max="365"
                            class="w-full text-base font-bold border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 pr-16 text-indigo-600 dark:text-indigo-400">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs text-gray-400">
                            days / year
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">This sets the default annual entitlement for employees.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pay Rule <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-4 text-sm mt-1">
                        <label class="inline-flex items-center">
                            <input type="radio" id="edit_paid_1" name="is_paid" value="1" class="text-gray-800 focus:ring-gray-400 mr-1.5">
                            Paid Leave
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" id="edit_paid_0" name="is_paid" value="0" class="text-gray-800 focus:ring-gray-400 mr-1.5">
                            Unpaid / Loss of Pay
                        </label>
                    </div>
                </div>

                <div class="p-3 bg-indigo-50/60 dark:bg-indigo-950/30 rounded-xl border border-indigo-100 dark:border-indigo-900/50">
                    <label class="inline-flex items-start text-xs text-indigo-900 dark:text-indigo-200 cursor-pointer">
                        <input type="checkbox" name="update_existing_balances" value="1" checked
                            class="rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 mr-2 mt-0.5">
                        <span>
                            <strong>Sync to existing {{ now()->year }} balances</strong><br>
                            Update total allocated days for all employees in {{ now()->year }} to this new quota.
                        </span>
                    </label>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded-lg text-sm shadow-md shadow-blue-500/20 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(type) {
            const form = document.getElementById('editForm');
            form.action = '/admin/leaves/types/' + type.id;
            document.getElementById('editModalSubtitle').innerText = 'Code: ' + type.code;
            document.getElementById('edit_name').value = type.name;
            document.getElementById('edit_days').value = parseFloat(type.days_allowed_per_year);

            if (type.is_paid) {
                document.getElementById('edit_paid_1').checked = true;
            } else {
                document.getElementById('edit_paid_0').checked = true;
            }

            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close on ESC key or backdrop click
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditModal();
        });
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>
@endsection
