@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

@php
$formatIndianAmount = function ($num) {
    $num = (float) $num;
    $sign = $num < 0 ? '-' : '';
    $num = abs($num);

    // Prefer readable Indian grouping for dashboard KPIs
    if ($num >= 10000000) {
        return $sign . rtrim(rtrim(number_format($num / 10000000, 2, '.', ''), '0'), '.') . 'Cr';
    }
    if ($num >= 100000) {
        return $sign . rtrim(rtrim(number_format($num / 100000, 2, '.', ''), '0'), '.') . 'L';
    }

    // Under 1 lakh: full amount with Indian-style thousands (e.g. 43,000)
    return $sign . number_format($num, $num == floor($num) ? 0 : 2);
};

$growthBadge = function (?float $pct, bool $invert = false) {
    if ($pct === null) {
        return '<span class="text-xs text-gray-400">—</span>';
    }
    $isUp = $invert ? $pct < 0 : $pct >= 0;
    $color = $isUp
        ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
        : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
    $arrow = $pct >= 0 ? '↑' : '↓';
    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ' . $color . '">'
        . $arrow . ' ' . abs($pct) . '%</span>';
};
@endphp

<style>
    @keyframes dashFadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .dash-animate { animation: dashFadeUp 0.45s ease-out both; }
    .dash-animate-delay-1 { animation-delay: 0.05s; }
    .dash-animate-delay-2 { animation-delay: 0.1s; }

    /* Compact KPI cards when shown 2-up on small screens */
    @media (max-width: 639px) {
        .kpi-grid > * { padding: 0.875rem; }
        .kpi-grid > * p.text-3xl { font-size: 1.375rem; line-height: 1.75rem; }
        .kpi-grid > * .w-12.h-12 { width: 2.25rem; height: 2.25rem; }
        .kpi-grid > * .w-12.h-12 svg { width: 1.125rem; height: 1.125rem; }
    }
    .dash-animate-delay-3 { animation-delay: 0.15s; }
    .chart-tab.active {
        color: rgb(37 99 235);
        border-bottom-color: rgb(37 99 235);
    }
    .dark .chart-tab.active {
        color: rgb(96 165 250);
        border-bottom-color: rgb(96 165 250);
    }
    @media print {
        aside, header, .no-print, .sidebar, nav { display: none !important; }
        main, .print-area { width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .print-only-table { display: block !important; }
        body { background: white !important; }
    }
</style>

<!-- ============================================ -->
<!-- SECTION 1: KEY PERFORMANCE INDICATORS -->
<!-- ============================================ -->
<div class="mb-8 dash-animate">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Key Performance Indicators</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Business health at a glance · vs last month</p>
        </div>
    </div>
    <div class="kpi-grid grid grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3 sm:gap-4">
        <!-- Paid Amount -->
        <div class="group relative overflow-hidden bg-gradient-to-br from-green-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-green-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Amount Collected</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">₹{{ $formatIndianAmount($paidInvoices) }}</p>
                    <div class="mt-2"><span class="text-xs text-gray-500 dark:text-gray-400">All payments received</span></div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>
        <!-- Overdue -->
        <div class="group relative overflow-hidden bg-gradient-to-br from-red-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-red-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Overdue Outstanding</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">₹{{ $formatIndianAmount($overdueInvoices) }}</p>
                    <div class="mt-2"><span class="text-xs text-gray-500 dark:text-gray-400">Unpaid balance past due date</span></div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <!-- Monthly Revenue -->
        <div class="group relative overflow-hidden bg-gradient-to-br from-teal-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-teal-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Monthly Revenue</p>
                    <p class="text-3xl font-bold text-teal-600 dark:text-teal-400">₹{{ $formatIndianAmount($currentMonthRevenue) }}</p>
                    <div class="mt-2">{!! $growthBadge($growth['revenue'] ?? null) !!}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <!-- Monthly Expenses -->
        <div class="group relative overflow-hidden bg-gradient-to-br from-orange-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-orange-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Monthly Expenses</p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">₹{{ $formatIndianAmount($currentMonthExpenses) }}</p>
                    <div class="mt-2">{!! $growthBadge($growth['expenses'] ?? null, true) !!}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </div>
        </div>
        <!-- Total Leads -->
        <a href="{{ route('leads.index') }}" class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-blue-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-blue-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Total Leads</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalLeads) }}</p>
                    <div class="mt-2">{!! $growthBadge($growth['leads'] ?? null) !!}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </a>
        <!-- Converted -->
        <a href="{{ route('leads.index', ['status' => 'converted']) }}" class="group relative block cursor-pointer overflow-hidden bg-gradient-to-br from-emerald-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-emerald-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Converted Leads</p>
                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($convertedLeads) }}</p>
                    <div class="mt-2">{!! $growthBadge($growth['converted'] ?? null) !!}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </a>
        <!-- Conversion Rate -->
        <div class="group relative overflow-hidden bg-gradient-to-br from-purple-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-purple-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Conversion Rate</p>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $conversionRate }}%</p>
                    <div class="mt-2">{!! $growthBadge($growth['conversion'] ?? null) !!}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </div>
        <!-- Total Invoices -->
        <div class="group relative overflow-hidden bg-gradient-to-br from-indigo-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-indigo-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Total Invoices</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($totalInvoices) }}</p>
                    <div class="mt-2">{!! $growthBadge($growth['invoices'] ?? null) !!}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
        </div>
        <!-- Net Profit -->
        <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-cyan-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Net Profit (This Month)</p>
                    <p class="text-3xl font-bold {{ $currentMonthProfit >= 0 ? 'text-cyan-600 dark:text-cyan-400' : 'text-red-600 dark:text-red-400' }}">₹{{ $formatIndianAmount($currentMonthProfit) }}</p>
                    <div class="mt-2">{!! $growthBadge($growth['profit'] ?? null) !!}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION 2: QUICK ACTIONS -->
<!-- ============================================ -->
<div class="mb-8 dash-animate dash-animate-delay-1">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-4">Quick Actions</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <a href="{{ route('leads.create.manual') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">New Lead</span>
        </a>
        <a href="{{ route('invoices.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">New Invoice</span>
        </a>
        <a href="{{ route('expenses.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Add Expense</span>
        </a>
        <a href="{{ route('customers.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Add Customer</span>
        </a>
        <a href="{{ route('leads.calendar') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Follow-up Calendar</span>
        </a>
        <a href="{{ route('recurring-invoices.create') }}" class="flex flex-col items-center justify-center p-5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 text-center">Recurring Invoice</span>
        </a>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION 3: ANALYTICS TABS -->
<!-- ============================================ -->
<div class="mb-8 dash-animate dash-animate-delay-2">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-4">Analytics</h2>
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700 px-2 sm:px-4 overflow-x-auto">
            <div class="flex min-w-max -mb-px">
                <button type="button" onclick="showChartTab('leads')" id="tab-leads" class="chart-tab active px-4 sm:px-6 py-3.5 text-sm font-semibold text-gray-600 dark:text-gray-300 border-b-2 border-transparent whitespace-nowrap">Leads</button>
                <button type="button" onclick="showChartTab('invoices')" id="tab-invoices" class="chart-tab px-4 sm:px-6 py-3.5 text-sm font-semibold text-gray-600 dark:text-gray-300 border-b-2 border-transparent whitespace-nowrap">Invoices</button>
                <button type="button" onclick="showChartTab('expenses')" id="tab-expenses" class="chart-tab px-4 sm:px-6 py-3.5 text-sm font-semibold text-gray-600 dark:text-gray-300 border-b-2 border-transparent whitespace-nowrap">Expenses</button>
                <button type="button" onclick="showChartTab('revenue')" id="tab-revenue" class="chart-tab px-4 sm:px-6 py-3.5 text-sm font-semibold text-gray-600 dark:text-gray-300 border-b-2 border-transparent whitespace-nowrap">Revenue</button>
            </div>
        </div>

        <!-- Leads Tab -->
        <div id="panel-leads" class="chart-panel p-4 sm:p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Lead Conversion Funnel</h3>
                    <div class="space-y-3">
                        @php
                            $funnel = [
                                ['label' => 'New', 'count' => $newLeads, 'color' => 'bg-blue-500'],
                                ['label' => 'Contacted', 'count' => $contactedLeads, 'color' => 'bg-indigo-500'],
                                ['label' => 'Qualified', 'count' => $qualifiedLeads, 'color' => 'bg-purple-500'],
                                ['label' => 'Converted', 'count' => $convertedLeads, 'color' => 'bg-emerald-500'],
                                ['label' => 'Lost', 'count' => $lostLeads, 'color' => 'bg-red-400'],
                            ];
                            $funnelMax = max(array_column($funnel, 'count') ?: [1]) ?: 1;
                        @endphp
                        @foreach($funnel as $step)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $step['label'] }}</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($step['count']) }}</span>
                                </div>
                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full {{ $step['color'] }} rounded-full transition-all duration-700" style="width: {{ min(100, ($step['count'] / $funnelMax) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Lead Sources</h3>
                    <div class="relative h-64">
                        <canvas id="chartLeadSources"></canvas>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Monthly Lead Trend (12 months)</h3>
                <div class="relative h-72">
                    <canvas id="chartLeadTrend"></canvas>
                </div>
            </div>
        </div>

        <!-- Invoices Tab -->
        <div id="panel-invoices" class="chart-panel hidden p-4 sm:p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Paid</p>
                    <p class="text-xl font-bold text-green-600">₹{{ $formatIndianAmount($paidInvoices) }}</p>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4 border border-amber-100 dark:border-amber-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Unpaid</p>
                    <p class="text-xl font-bold text-amber-600">₹{{ $formatIndianAmount($unpaidInvoices) }}</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Pending Collections</p>
                    <p class="text-xl font-bold text-blue-600">₹{{ $formatIndianAmount($pendingInvoices) }}</p>
                </div>
                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Avg Invoice Value</p>
                    <p class="text-xl font-bold text-indigo-600">₹{{ $formatIndianAmount($averageInvoiceValue) }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Invoice Status</h3>
                    <div class="relative h-64"><canvas id="chartInvoiceStatus"></canvas></div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Paid vs Unpaid</h3>
                    <div class="relative h-64"><canvas id="chartPaidUnpaid"></canvas></div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Monthly Invoice Totals</h3>
                    <div class="relative h-64"><canvas id="chartInvoiceTrend"></canvas></div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Top Paying Customers</h3>
                    <div class="space-y-3">
                        @forelse($topPayingCustomers as $i => $cust)
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $cust->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $cust->company ?: $cust->email }}</p>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-green-600 whitespace-nowrap">₹{{ $formatIndianAmount($cust->total_paid) }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-8">No payment data yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Tab -->
        <div id="panel-expenses" class="chart-panel hidden p-4 sm:p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Expenses by Category</h3>
                    <div class="relative h-64"><canvas id="chartExpenseCategory"></canvas></div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Expense Status</h3>
                    <div class="relative h-64"><canvas id="chartExpenseStatus"></canvas></div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Monthly Expense Trend</h3>
                <div class="relative h-72"><canvas id="chartExpenseTrend"></canvas></div>
            </div>
        </div>

        <!-- Revenue Tab -->
        <div id="panel-revenue" class="chart-panel hidden p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                <div class="bg-teal-50 dark:bg-teal-900/20 rounded-xl p-4 border border-teal-100 dark:border-teal-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">This Month Revenue</p>
                    <p class="text-2xl font-bold text-teal-600">₹{{ $formatIndianAmount($currentMonthRevenue) }}</p>
                </div>
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-4 border border-orange-100 dark:border-orange-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">This Month Expenses</p>
                    <p class="text-2xl font-bold text-orange-600">₹{{ $formatIndianAmount($currentMonthExpenses) }}</p>
                </div>
                <div class="bg-cyan-50 dark:bg-cyan-900/20 rounded-xl p-4 border border-cyan-100 dark:border-cyan-800">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Net Profit</p>
                    <p class="text-2xl font-bold {{ $currentMonthProfit >= 0 ? 'text-cyan-600' : 'text-red-600' }}">₹{{ $formatIndianAmount($currentMonthProfit) }}</p>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Profit & Loss (6 months)</h3>
                <div class="relative h-80"><canvas id="chartPnL"></canvas></div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION 4: WIDGETS -->
<!-- ============================================ -->
<div class="mb-8 dash-animate dash-animate-delay-3">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white mb-4">Overview Widgets</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Upcoming Meetings -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-purple-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-purple-500 to-violet-600 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Upcoming Meetings
                </h3>
                <a href="{{ route('leads.calendar') }}" class="text-xs text-purple-100 hover:text-white no-print">Calendar →</a>
            </div>
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                @forelse($upcomingMeetings as $meeting)
                    <a href="{{ route('leads.show', $meeting->lead_id) }}" class="block p-3 rounded-xl bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 hover:shadow-md transition">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $meeting->lead->name ?? 'Lead' }}</p>
                        <p class="text-xs text-purple-700 dark:text-purple-300 mt-1">{{ $meeting->followup_date->format('M d, Y · h:i A') }}</p>
                        @if($meeting->description)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $meeting->description }}</p>
                        @endif
                    </a>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">No upcoming meetings.</p>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Follow-ups -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-blue-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white">Upcoming Follow-ups</h3>
                <a href="{{ route('leads.calendar') }}" class="text-xs text-blue-100 hover:text-white no-print">View all →</a>
            </div>
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                @forelse($upcomingFollowups as $fu)
                    <a href="{{ route('leads.show', $fu->lead_id) }}" class="block p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 hover:shadow-md transition">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $fu->lead->name ?? 'Lead' }}</p>
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">{{ $fu->followup_type }}</span>
                        </div>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">{{ $fu->followup_date->format('M d, Y · h:i A') }}</p>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">No upcoming follow-ups.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-gray-700 to-gray-800">
                <h3 class="text-sm font-bold text-white">Recent Activity</h3>
            </div>
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                @forelse($recentActivities as $activity)
                    <div class="flex gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                        <div class="w-2 h-2 mt-1.5 rounded-full bg-blue-500 flex-shrink-0"></div>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-800 dark:text-gray-100">
                                <span class="font-semibold">{{ $activity->user->name ?? 'User' }}</span>
                                <span class="text-gray-500">· {{ $activity->description }}</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                @if($activity->lead)
                                    <a href="{{ route('leads.show', $activity->lead) }}" class="text-blue-600 hover:underline">{{ $activity->lead->name }}</a> ·
                                @endif
                                {{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-6">No recent activity.</p>
                @endforelse
            </div>
        </div>

        <!-- Business Health -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-cyan-500 to-teal-600">
                <h3 class="text-sm font-bold text-white">Business Health</h3>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Active Customers</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($activeCustomers) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Inactive Customers</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($inactiveCustomers) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Open Pipeline</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($newLeads + $contactedLeads + $interestedLeads + $qualifiedLeads) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Pending Expenses</span>
                    <span class="font-bold text-amber-600">₹{{ $formatIndianAmount($pendingExpenses) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Overdue Amount</span>
                    <span class="font-bold text-red-600">₹{{ $formatIndianAmount($overdueInvoices) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const chartInstances = {};

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function themeColors() {
        const dark = isDark();
        return {
            text: dark ? '#e5e7eb' : '#374151',
            grid: dark ? 'rgba(75,85,99,0.4)' : 'rgba(229,231,235,0.8)',
            palette: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#14b8a6', '#6366f1', '#ec4899', '#84cc16', '#06b6d4'],
        };
    }

    function destroyCharts() {
        Object.keys(chartInstances).forEach(k => {
            chartInstances[k].destroy();
            delete chartInstances[k];
        });
    }

    // Data from server
    const leadSources = {
        labels: @json($leadsBySource->keys()->values()),
        data: @json($leadsBySource->values()->map(fn($v) => (int)$v)->values()),
    };
    const leadTrends = {
        labels: @json($monthlyLeadTrends->pluck('month')),
        data: @json($monthlyLeadTrends->pluck('count')->map(fn($v) => (int)$v)),
    };
    const invoiceStatus = {
        labels: @json(array_keys($invoicesByStatus)),
        data: @json(array_values($invoicesByStatus)),
    };
    const invoiceTrends = {
        labels: @json($monthlyInvoiceTrends->pluck('month')),
        data: @json($monthlyInvoiceTrends->pluck('total')->map(fn($v) => (float)$v)),
    };
    const expenseStatus = {
        labels: @json(array_keys($expensesByStatus)),
        data: @json(array_values($expensesByStatus)),
    };
    const expenseCategory = {
        labels: @json($expensesByCategory->keys()->values()),
        data: @json($expensesByCategory->values()->map(fn($v) => (float)$v)->values()),
    };
    const expenseTrends = {
        labels: @json($monthlyExpenseTrends->pluck('month')),
        data: @json($monthlyExpenseTrends->pluck('total')->map(fn($v) => (float)$v)),
    };
    const pnl = {
        labels: @json($monthlyPnL->pluck('month')),
        revenue: @json($monthlyPnL->pluck('revenue')),
        expenses: @json($monthlyPnL->pluck('expenses')),
        profit: @json($monthlyPnL->pluck('profit')),
    };
    const paidUnpaid = {
        labels: ['Paid', 'Unpaid'],
        data: [{{ (float) $paidInvoices }}, {{ (float) $unpaidInvoices }}],
    };

    function makeDoughnut(id, labels, data) {
        const el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        const c = themeColors();
        chartInstances[id] = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: labels.length ? labels : ['No data'],
                datasets: [{
                    data: data.length ? data : [1],
                    backgroundColor: labels.length ? c.palette : ['#d1d5db'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: c.text, boxWidth: 12, padding: 12 } }
                }
            }
        });
    }

    function makeLine(id, labels, datasets) {
        const el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        const c = themeColors();
        chartInstances[id] = new Chart(el, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { color: c.text }, grid: { color: c.grid } },
                    y: { beginAtZero: true, ticks: { color: c.text }, grid: { color: c.grid } }
                },
                plugins: { legend: { labels: { color: c.text } } }
            }
        });
    }

    function makeBar(id, labels, datasets) {
        const el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        const c = themeColors();
        chartInstances[id] = new Chart(el, {
            type: 'bar',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { color: c.text }, grid: { color: c.grid } },
                    y: { beginAtZero: true, ticks: { color: c.text }, grid: { color: c.grid } }
                },
                plugins: { legend: { labels: { color: c.text } } }
            }
        });
    }

    window.initDashboardCharts = function () {
        destroyCharts();
        const c = themeColors();

        makeDoughnut('chartLeadSources', leadSources.labels, leadSources.data);
        makeLine('chartLeadTrend', leadTrends.labels, [{
            label: 'Leads',
            data: leadTrends.data,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.15)',
            fill: true,
            tension: 0.35,
        }]);

        makeDoughnut('chartInvoiceStatus', invoiceStatus.labels, invoiceStatus.data);
        makeDoughnut('chartPaidUnpaid', paidUnpaid.labels, paidUnpaid.data);
        makeBar('chartInvoiceTrend', invoiceTrends.labels, [{
            label: 'Invoice Totals',
            data: invoiceTrends.data,
            backgroundColor: '#6366f1',
            borderRadius: 6,
        }]);

        makeDoughnut('chartExpenseCategory', expenseCategory.labels, expenseCategory.data);
        makeDoughnut('chartExpenseStatus', expenseStatus.labels, expenseStatus.data);
        makeBar('chartExpenseTrend', expenseTrends.labels, [{
            label: 'Approved Expenses',
            data: expenseTrends.data,
            backgroundColor: '#f59e0b',
            borderRadius: 6,
        }]);

        makeBar('chartPnL', pnl.labels, [
            { label: 'Revenue', data: pnl.revenue, backgroundColor: '#14b8a6', borderRadius: 4 },
            { label: 'Expenses', data: pnl.expenses, backgroundColor: '#f97316', borderRadius: 4 },
            { label: 'Profit', data: pnl.profit, backgroundColor: '#06b6d4', borderRadius: 4 },
        ]);
    };

    window.showChartTab = function (tab) {
        document.querySelectorAll('.chart-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
        const panel = document.getElementById('panel-' + tab);
        const btn = document.getElementById('tab-' + tab);
        if (panel) panel.classList.remove('hidden');
        if (btn) btn.classList.add('active');
        // Charts in hidden panels need resize
        setTimeout(() => {
            Object.values(chartInstances).forEach(ch => ch.resize());
        }, 50);
    };

    document.addEventListener('DOMContentLoaded', function () {
        initDashboardCharts();
    });

    // Re-render charts when theme toggles
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(m => {
            if (m.attributeName === 'class') {
                initDashboardCharts();
            }
        });
    });
    observer.observe(document.documentElement, { attributes: true });
})();
</script>

@endsection
