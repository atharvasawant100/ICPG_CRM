@extends('layouts.app')

@section('title', 'Raise Confidential Ticket - Direct to Admin')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tickets.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Tickets</span>
        </a>
        <span>/</span>
        <span class="font-semibold text-gray-800 dark:text-gray-200">Raise Confidential Ticket</span>
    </div>

    <!-- Confidentiality Reassurance Banner -->
    <div class="bg-gradient-to-r from-indigo-500/10 via-purple-500/15 to-blue-500/10 dark:from-indigo-950/60 dark:via-purple-950/50 dark:to-blue-950/40 rounded-3xl p-5 sm:p-6 border-2 border-indigo-300 dark:border-indigo-800 shadow-sm relative overflow-hidden">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 text-xl font-bold shadow-sm">
                🛡️
            </div>
            <div>
                <h2 class="text-base sm:text-lg font-black text-gray-900 dark:text-white">
                    Direct Channel to Company Administration
                </h2>
                <p class="text-xs sm:text-sm text-indigo-900 dark:text-indigo-200 mt-1 leading-relaxed">
                    This ticket is routed <strong>strictly and exclusively to Company Administration (Admin)</strong>.
                    Your department manager will <strong>never be notified</strong>, will <strong>not have access</strong> to this ticket, and cannot see that you submitted it.
                </p>
                <div class="flex items-center gap-4 mt-3 text-[11px] font-semibold text-indigo-700 dark:text-indigo-300 flex-wrap">
                    <span class="flex items-center gap-1">✓ Manager Bypassed</span>
                    <span class="flex items-center gap-1">✓ Direct Review by Top Management</span>
                    <span class="flex items-center gap-1">✓ Retaliation Protection</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Subject -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                    Subject / Summary <span class="text-red-500">*</span>
                </label>
                <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="255"
                    placeholder="e.g. Missing requirements from manager for task while facing leadership inquiries"
                    class="w-full px-4 py-2.5 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                @error('subject')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category & Priority -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select name="category" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
                        @foreach (\App\Models\InternalTicket::CATEGORIES as $val => $lbl)
                            <option value="{{ $val }}" {{ old('category') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                        Urgency / Priority <span class="text-red-500">*</span>
                    </label>
                    <select name="priority" required class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
                        <option value="high" {{ old('priority', 'high') === 'high' ? 'selected' : '' }}>High (Active Blocker / Blame)</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent (Critical Escalation)</option>
                        <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                    @error('priority')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Optional Linked Task -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                    Linked Task (Optional)
                </label>
                <select name="task_id" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500">
                    <option value="">No Specific Task (General Department / Company Concern)</option>
                    @if ($preselectedTask && ! $myTasks->contains('id', $preselectedTask->id))
                        <option value="{{ $preselectedTask->id }}" selected>
                            {{ $preselectedTask->formatted_id }} — {{ $preselectedTask->title }} ({{ ucfirst($preselectedTask->priority) }})
                        </option>
                    @endif
                    @foreach ($myTasks as $task)
                        <option value="{{ $task->id }}" {{ (old('task_id') == $task->id || (isset($preselectedTask) && $preselectedTask->id == $task->id)) ? 'selected' : '' }}>
                            {{ $task->formatted_id }} — {{ $task->title }} ({{ ucfirst($task->priority) }} • {{ ucfirst(str_replace('_', ' ', $task->status)) }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">If you are facing blockers, missing requirements, or unfair scolding regarding a specific task, link it here so Admin can immediately inspect it.</p>
            </div>

            <!-- Detailed Description -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                    Detailed Explanation & Facts <span class="text-red-500">*</span>
                </label>
                <textarea name="description" rows="5" required
                    placeholder="Provide full context: What requirements or inputs were missing from your manager? What communication took place? When did you request assistance or inputs? What bottleneck or department politics occurred?"
                    class="w-full px-4 py-3 text-sm rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Attachments -->
            <div>
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                    Supporting Proof & Screenshots (Optional)
                </label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-4 sm:p-5 text-center bg-gray-50/50 dark:bg-gray-900/30">
                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <input type="file" name="attachments[]" multiple
                        class="block w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/40 dark:file:text-indigo-300 hover:file:bg-indigo-100 cursor-pointer">
                    <p class="text-[11px] text-gray-400 mt-2">Upload screenshots, chat logs, email PDFs, or notes. Max 10MB per file.</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
                <a href="{{ route('tickets.index') }}"
                    class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Submit Ticket Directly to Admin</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

