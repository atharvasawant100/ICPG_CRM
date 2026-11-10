@extends('layouts.app')

@section('title', $brand->name . ' - Brand Profile')
@section('page-title', 'Brand Profile')

@section('content')
<div class="space-y-6" x-data="{
    showAccountModal: false,
    editAccountModal: false,
    activeAccount: null,

    openEdit(acc) {
        this.activeAccount = acc;
        this.editAccountModal = true;
    }
}">

    <!-- Brand Header -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-bold text-white text-xl shadow-md shrink-0"
                style="background-color: {{ $brand->primary_color ?? '#3b82f6' }}">
                {{ strtoupper(substr($brand->name, 0, 2)) }}
            </div>

            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $brand->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $brand->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-600' }}">
                        {{ $brand->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
                    <span>Client: <strong class="text-slate-700 dark:text-slate-300">{{ $brand->client->name ?? 'None' }}</strong></span>
                    @if($brand->industry)
                        <span>&bull;</span>
                        <span>Industry: {{ $brand->industry }}</span>
                    @endif
                    @if($brand->website)
                        <span>&bull;</span>
                        <a href="{{ $brand->website }}" target="_blank" class="text-blue-600 hover:underline">Website &nearr;</a>
                    @endif
                </div>

                @if($brand->description)
                <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 max-w-2xl leading-relaxed">
                    {{ $brand->description }}
                </p>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @can('content.create')
            <a href="{{ route('content.posts.create') }}?brand_id={{ $brand->id }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Create Post</span>
            </a>
            @endcan

            @can('update', $brand)
            <a href="{{ route('brands.edit', $brand) }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-200 shadow-sm transition-all">
                Edit Brand
            </a>
            @endcan
        </div>
    </div>

    <!-- Connected Social Accounts (Requirement 3) -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                    Connected Social Accounts ({{ $brand->socialAccounts->count() }})
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Manage connected profiles, account handles, and publishing targets for this brand</p>
            </div>

            @can('content.accounts')
            <button type="button" @click="showAccountModal = true"
                class="px-3.5 py-1.5 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Connect Account</span>
            </button>
            @endcan
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($brand->socialAccounts as $acc)
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-slate-900/40 flex flex-col justify-between space-y-3">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase {{ $acc->platform_badge_class }}">
                            {{ $acc->platform }}
                        </span>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white">{{ $acc->account_name }}</h4>
                            <p class="text-[11px] text-slate-400">{{ $acc->username ? '@' . $acc->username : 'No username' }}</p>
                        </div>
                    </div>

                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                        {{ $acc->connection_status === 'connected' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-slate-200 text-slate-700' }}">
                        {{ $acc->connection_status }}
                    </span>
                </div>

                <div class="text-[11px] text-slate-400 space-y-1">
                    @if($acc->platform_account_id)
                        <div>ID: <span class="font-mono text-slate-600 dark:text-slate-300">{{ $acc->platform_account_id }}</span></div>
                    @endif
                    <div>Connected: {{ $acc->created_at->format('M d, Y') }}</div>
                </div>

                @can('content.accounts')
                <div class="pt-2 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs">
                    <button type="button" @click="openEdit({{ json_encode($acc) }})" class="font-semibold text-blue-600 hover:text-blue-700">
                        Edit Account
                    </button>

                    <form action="{{ route('social-accounts.destroy', $acc) }}" method="POST" onsubmit="return confirm('Disconnect this social account?');" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 hover:text-rose-700 font-semibold text-[11px]">
                            Disconnect
                        </button>
                    </form>
                </div>
                @endcan
            </div>
            @empty
            <div class="col-span-full py-8 text-center text-slate-400 text-xs">
                No social accounts connected for this brand yet. Click "Connect Account" to add Instagram, Facebook, LinkedIn, YouTube, Twitter/X, or other platform profiles.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Posts for this Brand -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                Recent Content Posts ({{ $recentPosts->count() }})
            </h2>

            <a href="{{ route('content.posts.index') }}?brand_id={{ $brand->id }}" class="text-xs font-semibold text-blue-600 hover:underline">
                View All Posts &rsaquo;
            </a>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($recentPosts as $p)
            <div class="py-3 flex items-center justify-between gap-4 text-xs">
                <div class="min-w-0">
                    <a href="{{ route('content.posts.show', $p) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 truncate block">
                        {{ $p->title }}
                    </a>
                    <div class="text-[11px] text-slate-400 mt-0.5">
                        <span>Target: {{ $p->scheduled_date ? Carbon\Carbon::parse($p->scheduled_date)->format('M d, Y') : 'Unscheduled' }}</span>
                        <span>&bull;</span>
                        <span>Assigned: {{ $p->assignee->name ?? 'Unassigned' }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $p->status_badge_class }}">
                        {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                    </span>
                    <a href="{{ route('content.posts.show', $p) }}" class="p-1.5 text-slate-400 hover:text-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-slate-400 text-xs">
                No content posts created for this brand yet.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Connect Social Account Modal (Requirement 3) -->
    <div x-show="showAccountModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-700 shadow-2xl p-6" @click.away="showAccountModal = false">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Connect Social Media Account</h3>
            <p class="text-xs text-slate-500 mt-1">Add an account profile or page identifier for {{ $brand->name }}.</p>

            <form action="{{ route('social-accounts.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="brand_id" value="{{ $brand->id }}">

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Platform *</label>
                    <select name="platform" required class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        @foreach($platforms as $plat)
                            <option value="{{ $plat }}">{{ ucfirst($plat) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Account / Page Name *</label>
                    <input type="text" name="account_name" required placeholder="e.g. Nike Official Instagram"
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Username / Handle</label>
                        <input type="text" name="username" placeholder="e.g. nike"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Platform Account ID</label>
                        <input type="text" name="platform_account_id" placeholder="Optional numeric/text ID"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Connection Status</label>
                    <select name="connection_status" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="connected">Connected / Active</option>
                        <option value="pending">Pending</option>
                        <option value="disconnected">Disconnected</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showAccountModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Save Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Social Account Modal -->
    <div x-show="editAccountModal" x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-700 shadow-2xl p-6" @click.away="editAccountModal = false">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Social Media Account</h3>

            <form :action="'/social-accounts/' + (activeAccount ? activeAccount.id : '')" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Account / Page Name *</label>
                    <input type="text" name="account_name" :value="activeAccount ? activeAccount.account_name : ''" required
                        class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Username / Handle</label>
                        <input type="text" name="username" :value="activeAccount ? activeAccount.username : ''"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Platform Account ID</label>
                        <input type="text" name="platform_account_id" :value="activeAccount ? activeAccount.platform_account_id : ''"
                            class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-600 dark:text-slate-300 text-xs font-semibold mb-1">Connection Status</label>
                    <select name="connection_status" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 px-3 py-2">
                        <option value="connected" :selected="activeAccount && activeAccount.connection_status === 'connected'">Connected / Active</option>
                        <option value="pending" :selected="activeAccount && activeAccount.connection_status === 'pending'">Pending</option>
                        <option value="disconnected" :selected="activeAccount && activeAccount.connection_status === 'disconnected'">Disconnected</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="editAccountModal = false" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm">Update Account</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

