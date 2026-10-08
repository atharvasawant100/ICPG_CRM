{{--
 | Sidebar.
 |
 | Structure and styling are unchanged from before. Every item, sub-item and
 | section header comes from config/navigation.php via App\Support\Navigation,
 | so adding a role later never requires editing this file.
 |
 | Navigation::sections() has already dropped items the user cannot see,
 | parents whose children are all hidden, and empty sections.
 --}}
@php
    $navUser = auth()->user();
    $navSections = \App\Support\Navigation::sections($navUser);
    $expenseScopeLabel = \App\Support\Navigation::expenseScopeLabel($navUser);
@endphp

<aside
    class="w-full h-full bg-gradient-to-b from-gray-800 to-gray-900 text-white flex flex-col shadow-2xl">
    <!-- Logo -->
    <div
        class="p-4 sm:p-5 md:p-6 border-b border-gray-700/50 flex items-center justify-between bg-gray-800/50 backdrop-blur-sm flex-shrink-0">
        <img src="https://iconicpages.com/wp-content/uploads/2025/12/IMG_3440.png" alt="Iconic Pages Logo"
            class="h-8 sm:h-10 md:h-12 w-auto object-contain">
        <!-- Close button for mobile -->
        <button onclick="toggleSidebar()"
            class="lg:hidden text-gray-300 hover:text-white p-2 rounded-lg hover:bg-gray-700/50 transition-colors z-10">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-3 sm:p-4 overflow-y-auto overflow-x-hidden -webkit-overflow-scrolling-touch">
        <ul class="space-y-1.5">
            @foreach ($navSections as $section)
                @if (! empty($section['label']))
                    <li class="px-4 pt-4 pb-1.5 first:pt-0">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                            {{ $section['label'] }}
                        </span>
                    </li>
                @endif

                @foreach ($section['items'] as $item)
                    <li>
                        @if (empty($item['_children']))
                            <a href="{{ $item['_href'] }}" data-nav-id="{{ $item['_id'] }}"
                                class="block px-4 py-2.5 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ $item['_active'] ? 'bg-gray-700/70 shadow-md' : '' }}">
                                <span class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="{{ $item['icon'] }}"></path>
                                    </svg>
                                    <span class="flex-1">{{ $item['label'] }}</span>
                                    @if (! empty($item['badge']))
                                        <span
                                            class="ml-2 px-1.5 py-0.5 rounded-full bg-teal-500/20 text-teal-300 text-[10px] font-bold uppercase tracking-wide">
                                            New
                                        </span>
                                    @endif
                                </span>
                            </a>
                        @else
                            <button type="button" onclick="toggleNavMenu('{{ $item['_id'] }}')"
                                data-nav-toggle="{{ $item['_id'] }}"
                                class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ $item['_active'] ? 'bg-gray-700/70 shadow-md' : '' }}">
                                <span class="flex items-center min-w-0">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="{{ $item['icon'] }}"></path>
                                    </svg>
                                    <span class="flex-1 text-left">{{ $item['label'] }}</span>
                                    @if (! empty($item['badge']))
                                        <span
                                            class="ml-2 mr-1 px-1.5 py-0.5 rounded-full bg-teal-500/20 text-teal-300 text-[10px] font-bold uppercase tracking-wide">
                                            New
                                        </span>
                                    @endif
                                </span>
                                <svg id="{{ $item['_id'] }}-arrow"
                                    class="w-4 h-4 flex-shrink-0 transition-transform duration-200 {{ $item['_active'] ? 'rotate-90' : '' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>

                            <ul id="{{ $item['_id'] }}-submenu"
                                class="mt-1.5 ml-4 space-y-1 {{ $item['_active'] ? '' : 'hidden' }}"
                                style="max-height: none;">
                                @foreach ($item['_children'] as $child)
                                    @php
                                        // One label is resolved at render time: the expenses
                                        // entry follows the user's data scope, not a role name.
                                        $childLabel = $child['label'] === 'expenses.label'
                                            ? $expenseScopeLabel
                                            : $child['label'];
                                    @endphp
                                    <li>
                                        <a href="{{ $child['_href'] }}" data-nav-id="{{ $child['_id'] }}"
                                            onclick="event.stopPropagation()"
                                            class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ $child['_active'] ? 'bg-gray-700/70' : '' }}">
                                            {{ $childLabel }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            @endforeach
        </ul>
    </nav>

    <!-- User Info & Logout -->
    <div class="p-4 border-t border-gray-700/50 bg-gray-800/50 backdrop-blur-sm flex-shrink-0">
        <a href="{{ route('profile.show') }}"
            class="block w-full text-left px-4 py-2.5 rounded-lg hover:bg-gray-700/50 transition-all duration-200 text-sm font-medium mb-2 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profile
        </a>
        <form action="{{ route('logout') }}" method="POST" id="logout-form">
            @csrf
            <button type="submit"
                class="w-full text-left px-4 py-2.5 rounded-lg hover:bg-gray-700/50 transition-all duration-200 text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>

<script>
    // One toggle function for every collapsible menu. The previous version had
    // three near-identical functions (toggleLeadsMenu, toggleExpensesMenu,
    // toggleInvoicesMenu); the sidebar is config-driven now, so one covers all.
    function toggleNavMenu(id) {
        var submenu = document.getElementById(id + '-submenu');
        var arrow = document.getElementById(id + '-arrow');
        if (!submenu) return;

        submenu.classList.toggle('hidden');
        if (arrow) arrow.classList.toggle('rotate-90');
    }
</script>