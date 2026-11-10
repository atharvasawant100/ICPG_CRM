@extends('layouts.app')

@section('title', 'Client Brands')
@section('page-title', 'Client Brands')

@section('content')
<div class="space-y-4">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Client Brands</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage client brand identities and their linked social media accounts.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('content.calendar') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Calendar</span>
            </a>

            @can('create', App\Models\Brand::class)
            <a href="{{ route('brands.create') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add New Brand</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
        <form method="GET" action="{{ route('brands.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Brand name, industry..."
                    class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
            </div>

            <div>
                <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Client</label>
                <select name="client_id" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->company_name }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition-all">Filter</button>
                <a href="{{ route('brands.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 transition-all">Reset</a>
            </div>
        </form>
    </div>

    <!-- Brands Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($brands as $b)
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm space-y-4 hover:border-blue-400 transition-colors">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-white text-base shadow-sm shrink-0"
                        style="background-color: {{ $b->primary_color ?? '#3b82f6' }}">
                        {{ strtoupper(substr($b->name, 0, 2)) }}
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">
                            <a href="{{ route('brands.show', $b) }}" class="hover:text-blue-600">
                                {{ $b->name }}
                            </a>
                        </h3>
                        <p class="text-xs text-slate-400">Client: {{ $b->client->name ?? 'None' }}</p>
                    </div>
                </div>

                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $b->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-600' }}">
                    {{ $b->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <p class="text-xs text-slate-500 line-clamp-2">{{ $b->description ?? 'No description provided.' }}</p>

            <!-- Connected Accounts Icons -->
            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Social Accounts</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $b->social_accounts_count }} connected</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Total Posts</span>
                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ $b->content_posts_count }} posts</span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700/60 text-xs">
                <a href="{{ route('brands.show', $b) }}" class="font-semibold text-blue-600 hover:text-blue-700">
                    View Brand & Accounts &rsaquo;
                </a>

                @can('update', $b)
                <a href="{{ route('brands.edit', $b) }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    Edit
                </a>
                @endcan
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-12 text-center text-slate-400">
            No brands found. Add client brands to begin scheduling content.
        </div>
        @endforelse
    </div>

    @if($brands->hasPages())
    <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
        {{ $brands->links() }}
    </div>
    @endif

</div>
@endsection

