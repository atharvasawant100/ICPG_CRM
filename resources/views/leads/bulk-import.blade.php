@extends('layouts.app')

@section('title', 'Bulk Import Leads')
@section('page-title', 'Bulk Import Leads')

@section('content')
<div class="w-full">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80 gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                    <a href="{{ route('leads.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Leads</a>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <a href="{{ route('leads.create') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Add Lead</a>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">Bulk Import</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Bulk Import Leads
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Upload an Excel or CSV spreadsheet to import multiple prospect contacts simultaneously.
                </p>
            </div>
            <div>
                <a href="{{ route('leads.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Change Add Method</span>
                </a>
            </div>
        </div>

        <!-- Download Template Banner -->
        <div class="mb-8 p-5 bg-blue-50/70 dark:bg-blue-900/20 border border-blue-200/80 dark:border-blue-800 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-blue-900 dark:text-blue-200">Download the verified Excel template</h3>
                    <p class="text-xs text-blue-700/90 dark:text-blue-300 mt-0.5">Use our pre-configured column schema to ensure your prospect contacts map and import cleanly.</p>
                </div>
            </div>
            <div>
                <a href="{{ route('leads.bulk-import.template') }}"
                    class="inline-flex items-center gap-2 text-xs font-bold text-blue-700 dark:text-blue-200 bg-white dark:bg-blue-800/40 px-4 py-2.5 rounded-xl border border-blue-200 dark:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-800/60 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download XLSX Template</span>
                </a>
            </div>
        </div>

        @if(session('import_summary'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <h4 class="text-sm font-bold text-green-800 dark:text-green-300 mb-2">Import Summary:</h4>
                <div class="text-xs text-green-700 dark:text-green-300 space-y-1">
                    <p>✓ {{ session('import_summary.success') }} leads imported successfully</p>
                    @if(session('import_summary.duplicates') > 0)
                        <p>ℹ {{ session('import_summary.duplicates') }} already existed (skipped)</p>
                    @endif
                    @if(session('import_summary.skipped') > 0)
                        <p>⚠ {{ session('import_summary.skipped') }} rows skipped due to errors</p>
                    @endif
                </div>
            </div>
        @endif

        @if(session('import_errors'))
            <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4">
                <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">Import Errors:</h4>
                <ul class="text-xs text-red-700 dark:text-red-300 space-y-1 max-h-40 overflow-y-auto list-disc list-inside">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">There were some problems with your file</h3>
                        <ul class="mt-2 list-disc list-inside text-xs text-red-700 dark:text-red-300 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Upload Form -->
        <form action="{{ route('leads.bulk-import.store') }}" method="POST" enctype="multipart/form-data" id="bulk-import-form" class="space-y-8">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Upload Excel or CSV File <span class="text-red-500">*</span></label>

                <!-- Drag & Drop Zone -->
                <div id="drop-zone"
                    class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-10 text-center hover:border-blue-500 dark:hover:border-blue-400 transition-colors cursor-pointer bg-gray-50/60 dark:bg-gray-750 hover:bg-blue-50/40 dark:hover:bg-blue-900/10">
                    <input type="file" name="file" id="file-input" accept=".xlsx,.xls,.csv" class="hidden" required>
                    <div id="upload-placeholder">
                        <div class="mx-auto w-14 h-14 rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Drag & drop your file here</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">or <span class="text-blue-600 dark:text-blue-400 font-semibold underline underline-offset-2">browse files</span> from your computer</p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-2">Supported: .xlsx, .xls, .csv up to 5MB</p>
                    </div>
                    <div id="file-selected" class="hidden">
                        <div class="mx-auto w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400 flex items-center justify-center mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white" id="file-name"></p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" id="file-size"></p>
                        <button type="button" id="remove-file"
                            class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 mt-3 font-semibold">Remove file</button>
                    </div>
                </div>

                @error('file')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Template Format Info -->
            <div class="p-5 bg-gray-50/70 dark:bg-gray-750 rounded-2xl border border-gray-200/80 dark:border-gray-700">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">Expected Columns in Spreadsheet:</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        <span class="text-gray-700 dark:text-gray-300"><strong>Name</strong> (required)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        <span class="text-gray-700 dark:text-gray-300"><strong>Email</strong> (required)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                        <span class="text-gray-600 dark:text-gray-400">Phone / phone_number</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                        <span class="text-gray-600 dark:text-gray-400">Company / organization</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                        <span class="text-gray-600 dark:text-gray-400">Source / platform</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                        <span class="text-gray-600 dark:text-gray-400">Status / pipeline_stage</span>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 border-t border-gray-200/70 dark:border-gray-700 pt-3">
                    Meta / Facebook lead exports (<code class="text-[11px] font-mono bg-gray-200/70 dark:bg-gray-700 px-1 py-0.5 rounded">full_name</code>, <code class="text-[11px] font-mono bg-gray-200/70 dark:bg-gray-700 px-1 py-0.5 rounded">phone_number</code>) are automatically parsed.
                </p>
            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('leads.create') }}"
                    class="order-2 sm:order-1 text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" id="import-btn"
                    class="order-1 sm:order-2 w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none"
                    disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Import Leads</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const placeholder = document.getElementById('upload-placeholder');
    const fileSelected = document.getElementById('file-selected');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const removeFile = document.getElementById('remove-file');
    const importBtn = document.getElementById('import-btn');

    // Click to browse
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag & Drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50/60');
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50/60');
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50/60');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            showFileInfo(files[0]);
        }
    });

    // File selected via input
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            showFileInfo(e.target.files[0]);
        }
    });

    function showFileInfo(file) {
        const sizeInKB = (file.size / 1024).toFixed(1);
        const sizeDisplay = sizeInKB > 1024 ? (sizeInKB / 1024).toFixed(1) + ' MB' : sizeInKB + ' KB';

        fileName.textContent = file.name;
        fileSize.textContent = sizeDisplay;
        placeholder.classList.add('hidden');
        fileSelected.classList.remove('hidden');
        importBtn.disabled = false;
    }

    // Remove file
    removeFile.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.value = '';
        placeholder.classList.remove('hidden');
        fileSelected.classList.add('hidden');
        importBtn.disabled = true;
    });
</script>
@endsection