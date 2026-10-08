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
            <li>
                <a href="{{ route('dashboard') }}"
                    class="block px-4 py-2.5 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-gray-700/70 shadow-md' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </span>
                </a>
            </li>
            <li>
                <a href="{{ url('/customers') }}"
                    class="block px-4 py-2.5 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('customers.*') ? 'bg-gray-700/70 shadow-md' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Customers
                    </span>
                </a>
            </li>
            <!-- Leads Menu with Sub-items -->
            <li>
                <button onclick="toggleLeadsMenu()"
                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('leads.*') ? 'bg-gray-700/70 shadow-md' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Leads
                    </span>
                    <svg id="leads-arrow"
                        class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('leads.*') ? 'rotate-90' : '' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <ul id="leads-submenu" class="mt-1.5 ml-4 space-y-1 {{ request()->routeIs('leads.*') ? '' : 'hidden' }}"
                    style="max-height: none;">
                    <li>
                        <a href="{{ route('leads.dashboard') }}"
                            class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('leads.dashboard') ? 'bg-gray-700/70' : '' }}">
                            Leads Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('leads.create') }}"
                            class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('leads.create*') ? 'bg-gray-700/70' : '' }}">
                            Add New Lead
                        </a>
                    </li>

                    <li>
                        
                        <a href="{{ url('/leads') }}" onclick="event.stopPropagation()"
                            class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('leads.index') ? 'bg-gray-700/70' : '' }}">
                            View all Leads
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('leads.calendar') }}"
                            class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('leads.calendar') ? 'bg-gray-700/70' : '' }}">
                            Follow-up Calendar
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('leads.trash') }}"
                            class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('leads.trash') ? 'bg-gray-700/70' : '' }}">
                            Trash Leads
                        </a>
                    </li>
                </ul>
            </li>
            <!-- Expenses Menu with Sub-items -->
            <li>
                <button onclick="toggleExpensesMenu()"
                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') ? 'bg-gray-700/70 shadow-md' : '' }}">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Expenses
                    </span>
                    <svg id="expenses-arrow"
                        class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') ? 'rotate-90' : '' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                <ul id="expenses-submenu"
                    class="mt-1.5 ml-4 space-y-1 {{ request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*') ? '' : 'hidden' }}"
                    style="max-height: none;">
                    <li>
                        <a href="{{ route('expenses.create') }}"
                            class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('expenses.create') ? 'bg-gray-700/70' : '' }}">
                            Add New Expense
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/expenses') }}" onclick="event.stopPropagation()"
                            class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('expenses.index') && !request()->routeIs('expenses.reports') && !request()->routeIs('expenses.pending') ? 'bg-gray-700/70' : '' }}">
                            {{ auth()->user()->isAdmin() ? 'All Expenses' : 'My Expenses' }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('expenses.pending') }}"
                            class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('expenses.pending') ? 'bg-gray-700/70' : '' }}">
                            Pending Expenses
                        </a>
                    </li>
                    @if(auth()->user()->isAdmin())
                        <li>
                            <a href="{{ url('/expense-categories') }}" onclick="event.stopPropagation()"
                                class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('expense-categories.*') ? 'bg-gray-700/70' : '' }}">
                                Expense Categories
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/expenses/reports/analytics') }}" onclick="event.stopPropagation()"
                                class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('expenses.reports') ? 'bg-gray-700/70' : '' }}">
                                Expense Reports
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
            @if(auth()->user()->isAdmin())
                <!-- Services Menu -->
                <li>
                    <a href="{{ route('services.index') }}"
                        class="block px-4 py-2.5 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('services.*') ? 'bg-gray-700/70 shadow-md' : '' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Services
                        </span>
                    </a>
                </li>
                <!-- Invoices Menu -->
                <li>
                    <button onclick="toggleInvoicesMenu()"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('invoices.*') || request()->routeIs('recurring-invoices.*') ? 'bg-gray-700/70 shadow-md' : '' }}">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Invoices
                        </span>
                        <svg id="invoices-arrow"
                            class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('invoices.*') || request()->routeIs('recurring-invoices.*') ? 'rotate-90' : '' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    <ul id="invoices-submenu"
                        class="mt-1.5 ml-4 space-y-1 {{ request()->routeIs('invoices.*') || request()->routeIs('recurring-invoices.*') ? '' : 'hidden' }}"
                        style="max-height: none;">
                        <li>
                            <a href="{{ route('invoices.index') }}"
                                class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('invoices.*') && !request()->routeIs('recurring-invoices.*') ? 'bg-gray-700/70' : '' }}">
                                All Invoices
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('recurring-invoices.index') }}"
                                class="block px-4 py-2 rounded-lg hover:bg-gray-700/50 transition-all duration-200 {{ request()->routeIs('recurring-invoices.*') ? 'bg-gray-700/70' : '' }}">
                                Recurring Invoices
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
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
        <script>
            // #region agent log
            document.getElementById('logout-form')?.addEventListener('submit', function (e) {
                fetch('http://127.0.0.1:7242/ingest/1a5de4ff-7aa6-4b1b-969d-4704d41cdba1', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ location: 'sidebar.blade.php:form', 'message': 'Logout form submitted', 'data': { 'hasCsrf': !!document.querySelector('[name="_token"]'), 'action': this.action, 'method': this.method }, timestamp: Date.now(), sessionId: 'debug-session', runId: 'run1', hypothesisId: 'A' }) }).catch(() => { });
            });
            // #endregion
        </script>
    </div>
</aside>

<script>
    function toggleLeadsMenu() {
        const submenu = document.getElementById('leads-submenu');
        const arrow = document.getElementById('leads-arrow');
        submenu.classList.toggle('hidden');
        arrow.classList.toggle('rotate-90');
    }

    function toggleExpensesMenu() {
        const submenu = document.getElementById('expenses-submenu');
        const arrow = document.getElementById('expenses-arrow');
        submenu.classList.toggle('hidden');
        arrow.classList.toggle('rotate-90');
    }

    function toggleInvoicesMenu() {
        const submenu = document.getElementById('invoices-submenu');
        const arrow = document.getElementById('invoices-arrow');
        submenu.classList.toggle('hidden');
        arrow.classList.toggle('rotate-90');
    }
</script>