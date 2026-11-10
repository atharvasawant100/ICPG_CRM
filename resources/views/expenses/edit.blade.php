@extends('layouts.app')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')

@section('content')
<div class="w-full">

    <!-- Single Full-Width Card Container matching Standard Form UI -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container with Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('expenses.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Expenses</a>
                    <span>/</span>
                    <a href="{{ route('expenses.show', $expense) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $expense->category?->name ?? 'Expense' }}</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Edit</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Edit Expense</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Update expense categorization, amount, receipt documentation, or remarks.
                </p>
            </div>

            <div class="flex items-center gap-2.5 self-start sm:self-auto">
                <a href="{{ route('expenses.show', $expense) }}"
                    class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View Expense
                </a>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="mb-8 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="space-y-1">
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-200">Please correct the following errors:</h3>
                        <ul class="text-xs text-red-700 dark:text-red-300 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" class="space-y-7">
            @csrf
            @method('PUT')

            <!-- 2-Column Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <select name="category_id" id="category_id" required onchange="toggleAiToolField(true)"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('category_id') border-red-500 @enderror">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-name="{{ strtolower($category->name) }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Tax and expense accounting classification</p>
                </div>

                <!-- Expense Date -->
                <div>
                    <label for="expense_date" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Expense Date <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="date" name="expense_date" id="expense_date"
                            value="{{ old('expense_date', $expense->expense_date ? $expense->expense_date->format('Y-m-d') : '') }}" max="{{ date('Y-m-d') }}" required
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('expense_date') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Date the purchase or payment occurred</p>
                </div>

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Amount (₹) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <span class="text-sm font-bold text-gray-400">₹</span>
                        </div>
                        <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0.01"
                            required placeholder="0.00"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('amount') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Total cost incurred</p>
                </div>

                <!-- AI Tool Name -->
                @php
                    $isAiCategory = ($expense->category && str_contains(strtolower($expense->category->name), 'ai tool')) || old('ai_tool') || $expense->ai_tool;
                @endphp
                <div id="ai_tool_container" class="{{ $isAiCategory ? '' : 'hidden' }}">
                    <label for="ai_tool" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        AI Tool Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-blue-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <input type="text" name="ai_tool" id="ai_tool" value="{{ old('ai_tool', $expense->ai_tool) }}"
                            placeholder="e.g. Claude, ChatGPT, GitHub Copilot"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Name of the AI service or license</p>
                </div>

                <!-- Receipt Upload -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">
                            Receipt / Invoice Document <span class="text-gray-400 font-normal">(Optional)</span>
                        </label>
                        @if($expense->receipt)
                            <a href="{{ asset('storage/' . $expense->receipt) }}" target="_blank"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                View Current Receipt
                            </a>
                        @endif
                    </div>
                    <div id="receipt-dropzone"
                        onclick="document.getElementById('receipt').click()"
                        class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 rounded-xl p-5 text-center cursor-pointer bg-gray-50/50 dark:bg-gray-700/30 hover:bg-blue-50/20 dark:hover:bg-blue-900/10 transition-all group">
                        
                        <input type="file" name="receipt" id="receipt" accept="image/*,.pdf" class="hidden" onchange="handleReceiptSelected(this)">

                        <div id="dropzone-prompt" class="space-y-1">
                            <div class="w-10 h-10 mx-auto rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                <span class="text-blue-600 dark:text-blue-400 group-hover:underline">Click to replace receipt</span> or drag & drop
                            </div>
                            <p class="text-[11px] text-gray-400">
                                {{ $expense->receipt ? 'Leave empty to preserve existing file' : 'PDF, PNG, JPG or WebP (Up to 5MB)' }}
                            </p>
                        </div>

                        <div id="dropzone-preview" class="hidden items-center justify-between p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-left">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-8 h-8 rounded bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p id="preview-filename" class="text-xs font-bold text-gray-900 dark:text-white truncate">receipt.pdf</p>
                                    <p id="preview-filesize" class="text-[10px] text-gray-500">1.2 MB</p>
                                </div>
                            </div>
                            <button type="button" onclick="event.stopPropagation(); clearReceiptSelection();"
                                class="p-1 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors ml-2" title="Remove file">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Attach proof of payment or tax bill</p>
                </div>

                <!-- Description / Purpose -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Description / Purpose <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <textarea name="description" id="description" rows="4"
                            placeholder="Explain the purpose of this expense..."
                            class="w-full p-4 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400">{{ old('description', $expense->description) }}</textarea>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Detailed notes for internal accounting</p>
                </div>

            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('expenses.show', $expense) }}"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                    Cancel
                </a>
                <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                        Update Expense
                    </button>
                </div>
            </div>

        </form>

    </div>
</div>

<!-- Dropzone Script -->
<script>
    function handleReceiptSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const prompt = document.getElementById('dropzone-prompt');
            const preview = document.getElementById('dropzone-preview');
            const filename = document.getElementById('preview-filename');
            const filesize = document.getElementById('preview-filesize');

            filename.textContent = file.name;
            filesize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

            prompt.classList.add('hidden');
            preview.classList.remove('hidden');
            preview.classList.add('flex');
        }
    }

    function clearReceiptSelection() {
        const input = document.getElementById('receipt');
        const prompt = document.getElementById('dropzone-prompt');
        const preview = document.getElementById('dropzone-preview');

        input.value = '';
        preview.classList.remove('flex');
        preview.classList.add('hidden');
        prompt.classList.remove('hidden');
    }

    const dropzone = document.getElementById('receipt-dropzone');
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('border-blue-500', 'bg-blue-50/40');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('border-blue-500', 'bg-blue-50/40');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                const input = document.getElementById('receipt');
                input.files = files;
                handleReceiptSelected(input);
            }
        }, false);
    }

    function toggleAiToolField(isUserChange = false) {
        const select = document.getElementById('category_id');
        const container = document.getElementById('ai_tool_container');
        const input = document.getElementById('ai_tool');
        if (!select || !container) return;

        const selectedOption = select.options[select.selectedIndex];
        const categoryName = selectedOption ? (selectedOption.getAttribute('data-name') || selectedOption.text || '').toLowerCase() : '';

        if (categoryName.includes('ai tool')) {
            container.classList.remove('hidden');
            if (input) input.setAttribute('required', 'required');
        } else {
            container.classList.add('hidden');
            if (input) {
                input.removeAttribute('required');
                if (isUserChange) {
                    input.value = '';
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleAiToolField(false);
    });
</script>
@endsection
