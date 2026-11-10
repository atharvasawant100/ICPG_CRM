@extends('layouts.app')

@section('title', 'Create Lead')
@section('page-title', 'Create Lead')

@section('content')
<div class="w-full">

    <!-- Single Full-Width Card Container -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200/80 dark:border-gray-700 p-6 sm:p-8 md:p-10 transition-colors duration-200">

        <!-- Header Inside the Container -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-8 border-b border-gray-100 dark:border-gray-700/80">
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    <a href="{{ route('leads.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Leads</a>
                    <span>/</span>
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">New Lead</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Add New Lead</h1>
            </div>

        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="mb-8 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
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

        <!-- Form Elements -->
        <form action="{{ route('leads.store') }}" method="POST" class="space-y-7">
            @csrf

            <!-- 2-Column Responsive Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Lead Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Lead / Contact Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            placeholder="e.g. Rahul Sharma"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('name') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Primary point of contact for this opportunity</p>
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            placeholder="e.g. prospect@company.com"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('email') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Used for sales outreach, proposals, and communication</p>
                </div>

                <!-- Phone / Mobile Number -->
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Phone / Mobile Number
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            placeholder="e.g. +91 98765 43210"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('phone') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Direct contact phone number or WhatsApp line</p>
                </div>

                <!-- Company Name -->
                <div>
                    <label for="company" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Company / Organization
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <input type="text" name="company" id="company" value="{{ old('company') }}"
                            placeholder="e.g. Acme Innovations Pvt Ltd"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 @error('company') border-red-500 @enderror">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">The company or business brand this lead represents</p>
                </div>

                <!-- Lead Source -->
                <div>
                    <label for="source" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Lead Source
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-indigo-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <select name="source" id="source"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('source') border-red-500 @enderror">
                            <option value="">Select Lead Source</option>
                            <option value="Website" {{ old('source') === 'Website' ? 'selected' : '' }}>Website</option>
                            <option value="Referral" {{ old('source') === 'Referral' ? 'selected' : '' }}>Referral</option>
                            <option value="Social Media" {{ old('source') === 'Social Media' ? 'selected' : '' }}>Social Media</option>
                            <option value="Email Campaign" {{ old('source') === 'Email Campaign' ? 'selected' : '' }}>Email Campaign</option>
                            <option value="Cold Call" {{ old('source') === 'Cold Call' ? 'selected' : '' }}>Cold Call</option>
                            <option value="Trade Show" {{ old('source') === 'Trade Show' ? 'selected' : '' }}>Trade Show</option>
                            <option value="Advertisement" {{ old('source') === 'Advertisement' ? 'selected' : '' }}>Advertisement</option>
                            <option value="Partner" {{ old('source') === 'Partner' ? 'selected' : '' }}>Partner</option>
                            <option value="Other" {{ old('source') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Where or how this prospective client originated</p>
                </div>

                <!-- Lead Status -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Pipeline Status
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <select name="status" id="status"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer @error('status') border-red-500 @enderror">
                            <option value="new" {{ old('status', 'new') === 'new' ? 'selected' : '' }}>New (Uncontacted)</option>
                            <option value="contacted" {{ old('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                            <option value="follow_up" {{ old('status') === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                            <option value="qualified" {{ old('status') === 'qualified' ? 'selected' : '' }}>Qualified</option>
                            <option value="converted" {{ old('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="won" {{ old('status') === 'won' ? 'selected' : '' }}>Won / Closed</option>
                            <option value="lost" {{ old('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                        </select>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Initial stage of the lead in your sales pipeline</p>
                </div>

                @if(isset($assignableUsers) && $assignableUsers->isNotEmpty())
                    <!-- Assign To (Manager / Admin assignment) -->
                    <div class="md:col-span-2">
                        <label for="assigned_to" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Assign to Sales Representative
                        </label>
                        <div class="relative rounded-xl shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-purple-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <select name="assigned_to" id="assigned_to"
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-gray-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all cursor-pointer @error('assigned_to') border-red-500 @enderror">
                                <option value="">-- Assign to Me / Unassigned --</option>
                                @foreach($assignableUsers as $u)
                                    <option value="{{ $u->id }}" {{ old('assigned_to') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Lead will appear in this employee's pipeline and calendar</p>
                    </div>
                @endif

            </div>

            <!-- Bottom Action Bar -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('leads.index') }}"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors order-2 sm:order-1">
                    Cancel
                </a>
                <div class="flex items-center gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <button type="submit"
                        class="w-full sm:w-auto px-7 py-3 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 transition-all shadow-md shadow-blue-500/20 active:scale-[0.98] text-center">
                        Create Lead
                    </button>
                </div>
            </div>

        </form>

    </div>

</div>
@endsection