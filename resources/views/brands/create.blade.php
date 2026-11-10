@extends('layouts.app')

@section('title', 'Add New Brand')
@section('page-title', 'Add New Brand')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Add New Client Brand</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Register a brand under an existing client account.</p>
        </div>

        <a href="{{ route('brands.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 shadow-sm transition-all">
            Back
        </a>
    </div>

    @if ($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 text-rose-800 text-xs">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
        @csrf

        <!-- Client -->
        <div>
            <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Client (Customer) *</label>
            <select name="client_id" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                <option value="">Select Existing Client</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ old('client_id', $selectedClientId) == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->company_name ?? 'No company' }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Brand Name -->
        <div>
            <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Brand Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Nike Football, Starbucks Reserve..."
                class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Industry -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Industry</label>
                <input type="text" name="industry" value="{{ old('industry') }}" placeholder="e.g. Fashion, E-commerce, SaaS..."
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
            </div>

            <!-- Website -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Website URL</label>
                <input type="url" name="website" value="{{ old('website') }}" placeholder="https://..."
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Description / Brand Guidelines</label>
            <textarea name="description" rows="3" placeholder="Brand positioning, tone of voice, audience personas..."
                class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Primary Color -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Calendar Badge Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="primary_color" value="{{ old('primary_color', '#3b82f6') }}" class="w-10 h-10 rounded-xl border-0 p-0 cursor-pointer">
                    <span class="text-xs text-slate-400">Used for calendar badges</span>
                </div>
            </div>

            <!-- Logo -->
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Brand Logo (Optional)</label>
                <input type="file" name="logo" accept="image/*"
                    class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 border border-slate-200 rounded-xl p-1.5 bg-slate-50">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
            <a href="{{ route('brands.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Save Brand</button>
        </div>
    </form>

</div>
@endsection

