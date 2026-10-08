@extends('layouts.app')

@section('title', 'Bulk Import Leads')
@section('page-title', 'Bulk Import Leads')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6 flex flex-row justify-between items-center">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Bulk Import Leads</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Upload an Excel (.xlsx) or CSV file to import
                    multiple leads</p>
            </div>
            <a href="{{ route('leads.create') }}"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 text-sm font-medium transition-colors inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back
            </a>
        </div>

        <!-- Step 1: Download Template & Upload -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-5 sm:p-6 md:p-8 border border-gray-100 dark:border-gray-700">

            <!-- Download Template -->
            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Download the template first</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Use our pre-formatted Excel template to
                            ensure your data
                            imports correctly.</p>
                        <a href="{{ route('leads.bulk-import.template') }}"
                            class="inline-flex items-center gap-1.5 mt-2 text-sm font-semibold text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-100 bg-white dark:bg-blue-800/30 px-3 py-1.5 rounded-lg border border-blue-300 dark:border-blue-700 hover:border-blue-400 dark:hover:border-blue-600 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download Template
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <form action="{{ route('leads.bulk-import.store') }}" method="POST" enctype="multipart/form-data"
                id="bulk-import-form">
                @csrf

                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-3">Upload Excel / CSV
                        File</label>

                    <!-- Drag & Drop Zone -->
                    <div id="drop-zone"
                        class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-gray-400 dark:hover:border-gray-500 transition-colors cursor-pointer bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700">
                        <input type="file" name="file" id="file-input" accept=".xlsx,.xls,.csv" class="hidden" required>
                        <div id="upload-placeholder">
                            <svg class="mx-auto w-12 h-12 text-gray-400 dark:text-gray-500 mb-3" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                            <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Drag & drop your file here</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">or <span
                                    class="text-blue-600 dark:text-blue-400 font-medium">click to
                                    browse</span></p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Supported: .xlsx, .xls, .csv (Max 5MB)</p>
                        </div>
                        <div id="file-selected" class="hidden">
                            <svg class="mx-auto w-10 h-10 text-green-500 dark:text-green-400 mb-2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm font-semibold text-green-700 dark:text-green-400" id="file-name"></p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" id="file-size"></p>
                            <button type="button" id="remove-file"
                                class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 mt-2 font-medium">Remove</button>
                        </div>
                    </div>

                    @error('file')
                        <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Template Format Info -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Expected Columns:</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            <span class="text-gray-600 dark:text-gray-300"><strong>Name</strong> / full_name
                                (required)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            <span class="text-gray-600 dark:text-gray-300"><strong>Email</strong> (required)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-500 rounded-full"></span>
                            <span class="text-gray-600 dark:text-gray-300">Phone / phone_number</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-500 rounded-full"></span>
                            <span class="text-gray-600 dark:text-gray-300">Company / website</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-500 rounded-full"></span>
                            <span class="text-gray-600 dark:text-gray-300">Source / platform</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-gray-400 dark:bg-gray-500 rounded-full"></span>
                            <span class="text-gray-600 dark:text-gray-300">Status / lead_status</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">Meta / Facebook lead exports
                        (<code class="text-[11px]">full_name</code>, <code class="text-[11px]">phone_number</code>, form
                        questions, etc.) are supported. Extra columns are saved as editable text on the lead.</p>
                </div>

                @if(session('import_errors'))
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <h4 class="text-sm font-bold text-red-700 dark:text-red-400 mb-2">Import Errors:</h4>
                        <ul class="text-xs text-red-600 dark:text-red-300 space-y-1 max-h-40 overflow-y-auto">
                            @foreach(session('import_errors') as $error)
                                <li class="flex items-start gap-1.5">
                                    <span class="mt-0.5">•</span>
                                    <span>{{ $error }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('import_summary'))
                    <div
                        class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <h4 class="text-sm font-bold text-green-700 dark:text-green-400 mb-2">Import Summary:</h4>
                        <div class="text-sm text-green-600 dark:text-green-300">
                            <p>✅ {{ session('import_summary.success') }} leads imported successfully</p>
                            @if(session('import_summary.duplicates') > 0)
                                <p>ℹ️ {{ session('import_summary.duplicates') }} already existed (skipped)</p>
                            @endif
                            @if(session('import_summary.skipped') > 0)
                                <p>⚠️ {{ session('import_summary.skipped') }} rows skipped due to errors</p>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('leads.create') }}"
                        class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 font-medium transition-colors">Cancel</a>
                    <button type="submit" id="import-btn"
                        class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-500 hover:to-green-400 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                        Import Leads
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
            dropZone.classList.add('border-blue-400', 'bg-blue-50');
        });
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-blue-400', 'bg-blue-50');
        });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-400', 'bg-blue-50');
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