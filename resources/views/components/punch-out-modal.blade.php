<!-- Punch Out Modal: Checklist of daily tasks before clocking out -->
<div id="punch-out-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="punch-out-modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" onclick="closePunchOutModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all w-full max-w-lg border border-gray-100 dark:border-gray-700">
            <form id="punch-out-form" action="{{ route('attendance.punch-out') }}" method="POST">
                @csrf
                <div class="p-5 sm:p-6 space-y-4">
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-rose-500 to-amber-500 text-white flex items-center justify-center shadow-md shadow-rose-500/20 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </span>
                            <div>
                                <h3 id="punch-out-modal-title" class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">
                                    Evening Shift Punch-Out
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @if(auth()->user()->hasTasks())
                                    Tick the tasks you have done today before clocking out.
                                    @else
                                    Confirm your shift punch-out for today.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button type="button" onclick="closePunchOutModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 p-1.5 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    @if(auth()->user()->hasTasks())
                    <!-- Tasks List Container -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Tasks Worked On / Completed Today
                            </label>
                            <button type="button" id="toggle-all-tasks-btn" onclick="toggleSelectAllPunchOutTasks()" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold hidden">
                                Select All
                            </button>
                        </div>
                        
                        <!-- Loading State -->
                        <div id="punch-out-tasks-loading" class="p-6 text-center text-xs text-gray-400">
                            <svg class="w-6 h-6 animate-spin mx-auto text-indigo-600 mb-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Loading your assigned tasks...</span>
                        </div>

                        <!-- Empty State -->
                        <div id="punch-out-tasks-empty" class="hidden p-4 rounded-2xl bg-gray-50 dark:bg-gray-700/50 text-center text-xs text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-700">
                            No active tasks currently assigned to you. You can still clock out for the day.
                        </div>

                        <!-- Task Items List -->
                        <div id="punch-out-tasks-list" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                    @endif

                    <!-- Daily Shift Work Notes -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Daily Summary / Accomplishments (Optional)
                        </label>
                        <textarea name="work_notes" rows="2" placeholder="Brief outline of deliverables or handoff notes for today..."
                            class="w-full text-xs sm:text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 p-2.5"></textarea>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="p-4 bg-gray-50 dark:bg-gray-750 border-t border-gray-100 dark:border-gray-700 flex flex-col-reverse sm:flex-row items-center justify-between gap-2.5">
                    <button type="button" onclick="closePunchOutModal()"
                        class="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                        Cancel
                    </button>
                    
                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        @if(auth()->user()->hasTasks())
                        <button type="submit" name="skip_tasks" value="1"
                            class="px-3.5 py-2 text-xs font-semibold rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 transition">
                            Punch Out (No Tasks)
                        </button>
                        @endif
                        <button type="submit"
                            class="px-5 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-rose-600 to-amber-600 hover:from-rose-500 hover:to-amber-500 text-white shadow-md hover:shadow-lg transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Confirm Punch Out</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openPunchOutModal() {
        const modal = document.getElementById('punch-out-modal');
        if (!modal) return;
        
        modal.classList.remove('hidden');

        @if(! auth()->user()->hasTasks())
        return;
        @endif

        const loading = document.getElementById('punch-out-tasks-loading');
        const empty = document.getElementById('punch-out-tasks-empty');
        const list = document.getElementById('punch-out-tasks-list');
        const selectAllBtn = document.getElementById('toggle-all-tasks-btn');

        loading.classList.remove('hidden');
        empty.classList.add('hidden');
        if (selectAllBtn) selectAllBtn.classList.add('hidden');
        list.innerHTML = '';

        // Fetch user's active tasks
        fetch('{{ route("tasks.my-active") }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            const tasks = data.tasks || [];

            if (tasks.length === 0) {
                empty.classList.remove('hidden');
                return;
            }

            if (selectAllBtn) selectAllBtn.classList.remove('hidden');

            tasks.forEach(task => {
                const isCompletedAlready = !!task.is_completed_today;
                const item = document.createElement('div');
                item.className = 'p-3 rounded-2xl border transition-all cursor-pointer ' + 
                    (isCompletedAlready ? 'border-emerald-300 dark:border-emerald-700 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-750 hover:border-indigo-300 dark:hover:border-indigo-600');
                item.id = 'punch-out-card-' + task.id;

                item.innerHTML = `
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="completed_task_ids[]" value="${task.id}"
                            ${isCompletedAlready ? 'checked' : ''}
                            onchange="onPunchOutTaskToggled(this, ${task.id})"
                            class="punch-out-task-chk rounded-lg border-gray-300 dark:border-gray-600 text-emerald-600 focus:ring-emerald-500 w-5 h-5 mt-0.5 flex-shrink-0">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                ${task.project_name ? `<span class="inline-flex items-center px-1.5 py-0.2 rounded-md text-[10px] font-semibold bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700">📁 ${escapeHtml(task.project_name)}</span>` : ''}
                                <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                    ${escapeHtml(task.title)}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                <span class="capitalize font-semibold text-xs ${task.priority_classes}">
                                    ${task.priority}
                                </span>
                                <span>&bull;</span>
                                <span id="task-state-label-${task.id}" class="font-medium ${isCompletedAlready ? 'text-emerald-600 dark:text-emerald-400 font-bold' : ''}">
                                    ${isCompletedAlready ? '✓ Completed' : task.status}
                                </span>
                                ${task.due_date ? `<span>&bull;</span><span>Due: ${task.due_date}</span>` : ''}
                            </div>
                        </div>
                    </label>
                    <input type="hidden" name="worked_task_ids[]" value="${task.id}" id="worked-task-hidden-${task.id}">
                `;
                list.appendChild(item);
            });
        })
        .catch(err => {
            loading.classList.add('hidden');
            empty.textContent = 'Unable to load tasks at this time. You can still punch out.';
            empty.classList.remove('hidden');
        });
    }

    function onPunchOutTaskToggled(checkbox, taskId) {
        const card = document.getElementById('punch-out-card-' + taskId);
        const stateLabel = document.getElementById('task-state-label-' + taskId);
        if (checkbox.checked) {
            if (card) {
                card.className = 'p-3 rounded-2xl border transition-all cursor-pointer border-emerald-300 dark:border-emerald-700 bg-emerald-50/40 dark:bg-emerald-950/20';
            }
            if (stateLabel) {
                stateLabel.textContent = '✓ Completed today';
                stateLabel.className = 'font-bold text-emerald-600 dark:text-emerald-400';
            }
        } else {
            if (card) {
                card.className = 'p-3 rounded-2xl border transition-all cursor-pointer border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-750 hover:border-indigo-300 dark:hover:border-indigo-600';
            }
            if (stateLabel) {
                stateLabel.textContent = 'In progress';
                stateLabel.className = 'font-medium text-gray-500 dark:text-gray-400';
            }
        }
    }

    function toggleSelectAllPunchOutTasks() {
        const checkboxes = document.querySelectorAll('.punch-out-task-chk');
        const btn = document.getElementById('toggle-all-tasks-btn');
        if (!checkboxes.length) return;

        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => {
            cb.checked = anyUnchecked;
            const taskId = cb.value;
            onPunchOutTaskToggled(cb, taskId);
        });

        if (btn) {
            btn.textContent = anyUnchecked ? 'Deselect All' : 'Select All';
        }
    }

    function closePunchOutModal() {
        const modal = document.getElementById('punch-out-modal');
        if (modal) modal.classList.add('hidden');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
