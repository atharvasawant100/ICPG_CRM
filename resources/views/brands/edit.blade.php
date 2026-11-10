@extends('layouts.app')

@section('title', 'Edit Brand - ' . $brand->name)
@section('page-title', 'Edit Brand')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Edit Brand: {{ $brand->name }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Update brand information, client association, or active status.</p>
        </div>

        <a href="{{ route('brands.show', $brand) }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 shadow-sm transition-all">
            Back to Brand
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

    <form action="{{ route('brands.update', $brand) }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
        @csrf
        @method('PUT')

        <!-- Client -->
        <div>
            <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Client (Customer) *</label>
            <select name="client_id" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ old('client_id', $brand->client_id) == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->company_name ?? 'No company' }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Brand Name -->
        <div>
            <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Brand Name *</label>
            <input type="text" name="name" value="{{ old('name', $brand->name) }}" required
                class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Industry</label>
                <input type="text" name="industry" value="{{ old('industry', $brand->industry) }}"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
            </div>

            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Website URL</label>
                <input type="url" name="website" value="{{ old('website', $brand->website) }}"
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">
            </div>
        </div>

        <div>
            <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Description / Guidelines</label>
            <textarea name="description" rows="3" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2.5">{{ old('description', $brand->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Badge Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="primary_color" value="{{ old('primary_color', $brand->primary_color ?? '#3b82f6') }}" class="w-10 h-10 rounded-xl border-0 p-0 cursor-pointer">
                    <span class="text-xs text-slate-400">Color for calendar items</span>
                </div>
            </div>

            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Status</label>
                <label class="flex items-center gap-2 mt-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Active Brand</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700">
            @can('delete', $brand)
            <button type="button" onclick="if(confirm('Delete this brand and all associated records?')) document.getElementById('delete-brand-form').submit();" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                Delete Brand
            </button>
            @else
            <div></div>
            @endcan

            <div class="flex items-center gap-2">
                <a href="{{ route('brands.show', $brand) }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Save Changes</button>
            </div>
        </div>
    </form>

    <form id="delete-brand-form" action="{{ route('brands.destroy', $brand) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>
@endsection

