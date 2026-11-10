{{--
 | Sidebar.
 |
 | Refined modern UI with crisp visual hierarchy, tree guidelines for nested submenus,
 | balanced badge positioning, smooth hover/active states, and a sleek user profile card.
 |
 | Driven by config/navigation.php via App\Support\Navigation.
 --}}
@php
    $navUser = auth()->user();
    $navSections = \App\Support\Navigation::sections($navUser);
    $expenseScopeLabel = \App\Support\Navigation::expenseScopeLabel($navUser);
@endphp

<aside
    class="w-full h-full bg-[#0f172a] text-slate-300 flex flex-col border-r border-slate-800/80 shadow-2xl select-none">
    
    <!-- Brand / Logo Header -->
    <div
        class="h-16 px-5 border-b border-slate-800/80 flex items-center justify-between bg-slate-900/60 backdrop-blur-md flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group transition-opacity hover:opacity-95">
            <img src="https://iconicpages.com/wp-content/uploads/2025/12/IMG_3440.png" alt="Iconic Pages Logo"
                class="h-8 sm:h-9 w-auto object-contain">
        </a>
        <!-- Close button for mobile -->
        <button onclick="toggleSidebar()"
            class="lg:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition-colors z-10"
            aria-label="Close sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation Scroll Area -->
    <nav class="flex-1 px-3 py-4 overflow-y-auto overflow-x-hidden sidebar-scroll">
        <div class="space-y-5">
            @foreach ($navSections as $section)
                <div class="space-y-1">
                    @if (! empty($section['label']))
                        <div class="px-3 pt-3 pb-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400/80">
                                {{ $section['label'] }}
                            </span>
                        </div>
                    @endif

                    <ul class="space-y-1">
                        @foreach ($section['items'] as $item)
                            <li>
                                @if (empty($item['_children']))
                                    {{-- Leaf Item (Direct link, no sub-items) --}}
                                    @php
                                        $isLeafActive = $item['_active'];
                                    @endphp
                                    <a href="{{ $item['_href'] }}" data-nav-id="{{ $item['_id'] }}"
                                        class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all duration-150 {{ $isLeafActive ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                                        <div class="flex items-center min-w-0">
                                            <svg class="w-5 h-5 mr-3 flex-shrink-0 transition-colors {{ $isLeafActive ? 'text-white' : 'text-slate-400 group-hover:text-slate-200' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="{{ $item['icon'] }}"></path>
                                            </svg>
                                            <span class="truncate">{{ $item['label'] }}</span>
                                        </div>
                                    </a>
                                @else
                                    {{-- Parent Item with Collapsible Submenu --}}
                                    @php
                                        $isParentActive = $item['_active'];
                                    @endphp
                                    <button type="button" onclick="toggleNavMenu('{{ $item['_id'] }}')"
                                        data-nav-toggle="{{ $item['_id'] }}"
                                        class="group w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm font-medium transition-all duration-150 {{ $isParentActive ? 'bg-slate-800/80 text-white font-medium' : 'text-slate-300 hover:text-white hover:bg-slate-800/50' }}">
                                        <div class="flex items-center min-w-0">
                                            <svg class="w-5 h-5 mr-3 flex-shrink-0 transition-colors {{ $isParentActive ? 'text-indigo-400' : 'text-slate-400 group-hover:text-slate-200' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="{{ $item['icon'] }}"></path>
                                            </svg>
                                            <span class="truncate text-left">{{ $item['label'] }}</span>
                                        </div>
                                        <div class="flex items-center flex-shrink-0 ml-2">
                                            <svg id="{{ $item['_id'] }}-arrow"
                                                class="w-4 h-4 text-slate-400 group-hover:text-slate-200 transition-transform duration-200 {{ $isParentActive ? 'rotate-90 text-slate-300' : '' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                    </button>

                                    {{-- Submenu Tree with Vertical Guide Line --}}
                                    <ul id="{{ $item['_id'] }}-submenu"
                                        class="mt-1 ml-5 pl-3 border-l border-slate-700/60 space-y-0.5 {{ $isParentActive ? '' : 'hidden' }}">
                                        @foreach ($item['_children'] as $child)
                                            @php
                                                $childLabel = $child['label'] === 'expenses.label'
                                                    ? $expenseScopeLabel
                                                    : $child['label'];
                                                $isChildActive = $child['_active'];
                                            @endphp
                                            <li>
                                                <a href="{{ $child['_href'] }}" data-nav-id="{{ $child['_id'] }}"
                                                    onclick="event.stopPropagation()"
                                                    class="group flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 {{ $isChildActive ? 'bg-indigo-600/20 text-indigo-300 font-semibold border border-indigo-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/40' }}">
                                                    <span class="flex items-center min-w-0">
                                                        <span
                                                            class="w-1.5 h-1.5 rounded-full mr-2 transition-all flex-shrink-0 {{ $isChildActive ? 'bg-indigo-400 ring-2 ring-indigo-400/30' : 'bg-slate-600 group-hover:bg-slate-400' }}"></span>
                                                        <span class="truncate">{{ $childLabel }}</span>
                                                    </span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </nav>

    <!-- User Info & Logout Footer -->
    @php
        $footerInitials = collect(explode(' ', $navUser->name ?? 'User'))
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->implode('');
        $footerRole = $navUser->designation 
            ? ucwords($navUser->designation) 
            : ($navUser->getRoleNames()->first() ? ucfirst($navUser->getRoleNames()->first()) : 'Member');
    @endphp

    <div class="p-3 border-t border-slate-800/80 bg-slate-900/80 backdrop-blur-sm flex-shrink-0 space-y-2">
        <!-- User Profile Card -->
        <a href="{{ route('profile.show') }}"
            class="flex items-center gap-2.5 p-2 rounded-xl bg-slate-800/50 hover:bg-slate-800 border border-slate-700/40 hover:border-slate-700 transition-all duration-150 group"
            title="View Profile">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-sm overflow-hidden ring-1 ring-slate-700/60">
                @if ($navUser->avatar_url)
                    <img src="{{ $navUser->avatar_url }}" alt="{{ $navUser->name }}" class="w-full h-full object-cover" onerror="this.style.display='none'; document.getElementById('sidebar-avatar-fallback').style.display='flex';">
                    <span id="sidebar-avatar-fallback" style="display:none;" class="w-full h-full items-center justify-center">{{ $footerInitials ?: 'U' }}</span>
                @else
                    {{ $footerInitials ?: 'U' }}
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-xs font-semibold text-slate-200 group-hover:text-white truncate transition-colors">
                    {{ $navUser->name ?? 'Profile' }}
                </div>
                <div class="text-[11px] text-slate-400 truncate">
                    {{ $footerRole }}
                </div>
            </div>
            <span class="text-[11px] font-medium text-slate-400 group-hover:text-slate-200 transition-colors flex items-center">
                Profile &rarr;
            </span>
        </a>

        <!-- Logout Action Button -->
        <form action="{{ route('logout') }}" method="POST" id="logout-form">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all duration-150 border border-transparent hover:border-red-500/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>

<style>
    /* Custom sleek scrollbar for sidebar navigation */
    .sidebar-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.25) transparent;
    }
    .sidebar-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .sidebar-scroll::-webkit-scrollbar-thumb {
        background-color: rgba(148, 163, 184, 0.2);
        border-radius: 9999px;
    }
    .sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background-color: rgba(148, 163, 184, 0.4);
    }
</style>

<script>
    function toggleNavMenu(id) {
        var submenu = document.getElementById(id + '-submenu');
        var arrow = document.getElementById(id + '-arrow');
        if (!submenu) return;

        submenu.classList.toggle('hidden');
        if (arrow) arrow.classList.toggle('rotate-90');
    }
</script>