@extends('layouts.app')

@section('title', 'Leads Dashboard')
@section('page-title', 'Leads Dashboard & Analytics')

@section('content')
    <style>
        /* Compact stat cards when shown 2-up on small screens */
        @media (max-width: 639px) {
            .stats-grid > div { padding: 0.875rem; }
            .stats-grid > div p.text-3xl { font-size: 1.375rem; line-height: 1.75rem; }
            .stats-grid > div .w-12.h-12 { width: 2.25rem; height: 2.25rem; }
            .stats-grid > div .w-12.h-12 svg { width: 1.125rem; height: 1.125rem; }
        }
    </style>

    <!-- Summary Cards -->
    <div class="stats-grid grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5 mb-4 sm:mb-5 md:mb-6">
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-gray-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Total Leads</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white">{{ number_format($totalLeads) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-blue-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-blue-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">New Leads</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($newLeads) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
            </div>
        </div>
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-green-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-green-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Converted</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($convertedLeads) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-purple-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-purple-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Conversion Rate</p>
                    <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $conversionRate }}%</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Cards -->
    <div class="stats-grid grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5 mb-4 sm:mb-5 md:mb-6">
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-yellow-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-yellow-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Contacted</p>
                    <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ number_format($contactedLeads) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
            </div>
        </div>
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-indigo-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-indigo-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Qualified</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($qualifiedLeads) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
        </div>
        <div
            class="group relative overflow-hidden bg-gradient-to-br {{ $overdueFollowups > 0 ? 'from-red-50 to-white border-red-100' : 'from-orange-50 to-white border-orange-100' }} dark:from-gray-800 dark:to-gray-900 dark:border-gray-700 rounded-2xl shadow-lg p-5 border hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Pending Follow-ups</p>
                    <p class="text-3xl font-bold {{ $overdueFollowups > 0 ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400' }}">
                        {{ number_format($pendingFollowups) }}
                    </p>
                    @if($overdueFollowups > 0)
                        <p class="text-xs text-red-600 dark:text-red-400 mt-1 font-semibold">{{ $overdueFollowups }} overdue</p>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-xl {{ $overdueFollowups > 0 ? 'bg-red-100 dark:bg-red-900/40' : 'bg-orange-100 dark:bg-orange-900/40' }} flex items-center justify-center">
                    <svg class="w-6 h-6 {{ $overdueFollowups > 0 ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div
            class="group relative overflow-hidden bg-gradient-to-br from-teal-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-lg p-5 border border-teal-100 dark:border-gray-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Recent (7 days)</p>
                    <p class="text-3xl font-bold text-teal-600 dark:text-teal-400">{{ number_format($recentLeads) }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/40 flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5 md:gap-6 mb-4 sm:mb-5 md:mb-6">
        <!-- Leads by Status Pie Chart -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Leads by Status</h3>
            @if($totalLeads > 0)
                <canvas id="statusChart" style="max-height: 300px;"></canvas>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No data available</p>
            @endif
        </div>

        <!-- Leads by Source Pie Chart -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Leads by Source</h3>
            @if($leadsBySource->count() > 0)
                <canvas id="sourceChart" style="max-height: 300px;"></canvas>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No data available</p>
            @endif
        </div>

        <!-- Monthly Trends Chart -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Monthly Lead Trends (Last 6 Months)</h3>
            @if($monthlyTrends->count() > 0)
                <canvas id="monthlyChart" style="max-height: 300px;"></canvas>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No data available</p>
            @endif
        </div>

        <!-- Top Sources Bar Chart -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Top Lead Sources</h3>
            @if($topSources->count() > 0)
                <canvas id="topSourcesChart" style="max-height: 300px;"></canvas>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No data available</p>
            @endif
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
        const statusData = {
            labels: @json(array_keys($leadsByStatus)),
            data: @json(array_values($leadsByStatus)),
            total: {{ (int) $totalLeads }},
        };
        const sourceData = {
            labels: @json($leadsBySource->keys()),
            data: @json($leadsBySource->values()),
            total: {{ (int) $leadsBySource->sum() }},
        };
        const monthlyData = {
            labels: @json($monthlyTrends->pluck('month')),
            data: @json($monthlyTrends->pluck('count')),
        };
        const topSourcesData = {
            labels: @json($topSources->keys()),
            data: @json($topSources->values()),
        };

        function pctLabel(total) {
            return function (context) {
                let label = context.label || '';
                if (label) label += ': ';
                label += context.parsed + (total > 0 ? ' (' + Math.round((context.parsed / total) * 100) + '%)' : '');
                return label;
            };
        }

        function initLeadCharts() {
            destroyCharts();
            const c = themeColors();

            const statusEl = document.getElementById('statusChart');
            if (statusEl && statusData.total > 0) {
                chartInstances.status = new Chart(statusEl, {
                    type: 'pie',
                    data: {
                        labels: statusData.labels,
                        datasets: [{
                            data: statusData.data,
                            backgroundColor: ['#3b82f6', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444'],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: c.text } },
                            tooltip: { callbacks: { label: pctLabel(statusData.total) } }
                        }
                    }
                });
            }

            const sourceEl = document.getElementById('sourceChart');
            if (sourceEl && sourceData.labels.length > 0) {
                chartInstances.source = new Chart(sourceEl, {
                    type: 'pie',
                    data: {
                        labels: sourceData.labels,
                        datasets: [{
                            data: sourceData.data,
                            backgroundColor: c.palette.slice(0, sourceData.labels.length),
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: c.text } },
                            tooltip: { callbacks: { label: pctLabel(sourceData.total) } }
                        }
                    }
                });
            }

            const monthlyEl = document.getElementById('monthlyChart');
            if (monthlyEl && monthlyData.labels.length > 0) {
                chartInstances.monthly = new Chart(monthlyEl, {
                    type: 'line',
                    data: {
                        labels: monthlyData.labels,
                        datasets: [{
                            label: 'Leads',
                            data: monthlyData.data,
                            backgroundColor: 'rgba(59, 130, 246, 0.25)',
                            borderColor: '#3b82f6',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.35,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { ticks: { color: c.text }, grid: { color: c.grid } },
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, color: c.text },
                                grid: { color: c.grid }
                            }
                        }
                    }
                });
            }

            const topEl = document.getElementById('topSourcesChart');
            if (topEl && topSourcesData.labels.length > 0) {
                chartInstances.top = new Chart(topEl, {
                    type: 'bar',
                    data: {
                        labels: topSourcesData.labels,
                        datasets: [{
                            label: 'Leads',
                            data: topSourcesData.data,
                            backgroundColor: c.palette.slice(0, topSourcesData.labels.length),
                            borderColor: c.palette.slice(0, topSourcesData.labels.length),
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { ticks: { color: c.text }, grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, color: c.text },
                                grid: { color: c.grid }
                            }
                        }
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', initLeadCharts);

        // Re-render charts when theme toggles
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(m => {
                if (m.attributeName === 'class') {
                    initLeadCharts();
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true });
    })();
    </script>
@endsection