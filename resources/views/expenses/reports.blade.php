@extends('layouts.app')

@section('title', 'Expense Reports')
@section('page-title', 'Expense Reports & Analytics')

@section('content')
    <!-- Date Range Filter -->
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 mb-4 sm:mb-5 md:mb-6 border border-gray-100 dark:border-gray-700">
        <form method="GET" action="{{ route('expenses.reports') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="date_from" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Date
                        From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label for="date_to" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Date
                        To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 text-sm leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-xl text-sm shadow-md hover:shadow-lg transition-all duration-200 w-full">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Apply Filter</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 md:gap-5 lg:gap-6 mb-4 sm:mb-5 md:mb-6">
        <div
            class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
            <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm font-medium mb-2">Total Expenses</p>
            <p class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">
                ₹{{ number_format($totalExpenses, 2) }}</p>
        </div>
        <div
            class="bg-gradient-to-br from-green-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-green-100 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
            <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm font-medium mb-2">Approved</p>
            <p class="text-xl sm:text-2xl md:text-3xl font-bold text-green-600 dark:text-green-400">
                ₹{{ number_format($totalApproved, 2) }}</p>
        </div>
        <div
            class="bg-gradient-to-br from-yellow-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-yellow-100 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
            <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm font-medium mb-2">Pending</p>
            <p class="text-xl sm:text-2xl md:text-3xl font-bold text-yellow-600 dark:text-yellow-400">
                ₹{{ number_format($totalPending, 2) }}</p>
        </div>
        <div
            class="bg-gradient-to-br from-red-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-red-100 dark:border-gray-700 hover:shadow-xl transition-shadow duration-300">
            <p class="text-gray-600 dark:text-gray-400 text-xs sm:text-sm font-medium mb-2">Rejected</p>
            <p class="text-xl sm:text-2xl md:text-3xl font-bold text-red-600 dark:text-red-400">
                ₹{{ number_format($totalRejected, 2) }}</p>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5 md:gap-6 mb-4 sm:mb-5 md:mb-6">
        <!-- Expenses by Category Pie Chart -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Expenses by Category</h3>
            <canvas id="categoryChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Expenses by Status Pie Chart -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Expenses by Status</h3>
            <canvas id="statusChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Monthly Trends Chart -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Monthly Expense Trends</h3>
            <canvas id="monthlyChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <script>
        // Chart colors
        const colors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
            '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
        ];

        // Expenses by Category
        @if($expensesByCategory->count() > 0)
            const categoryData = {
                labels: @json($expensesByCategory->pluck('category.name')),
                datasets: [{
                    data: @json($expensesByCategory->pluck('total')),
                    backgroundColor: colors.slice(0, {{ $expensesByCategory->count() }}),
                }]
            };

            new Chart(document.getElementById('categoryChart'), {
                type: 'pie',
                data: categoryData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += '₹' + context.parsed.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        @else
            document.getElementById('categoryChart').parentElement.innerHTML = '<p class="text-gray-500 text-center py-8">No data available for the selected period.</p>';
        @endif

            // Expenses by Status
            @if($expensesByStatus->count() > 0)
                const statusData = {
                    labels: @json($expensesByStatus->pluck('status')->map(function ($s) {
                    return ucfirst($s); })->values()),
                    datasets: [{
                        data: @json($expensesByStatus->pluck('total')),
                        backgroundColor: ['#F59E0B', '#10B981', '#EF4444'],
                    }]
                };

                new Chart(document.getElementById('statusChart'), {
                    type: 'pie',
                    data: statusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += '₹' + context.parsed.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            @else
            document.getElementById('statusChart').parentElement.innerHTML = '<p class="text-gray-500 text-center py-8">No data available for the selected period.</p>';
        @endif

            // Monthly Trends
            @if($monthlyTrends->count() > 0)
                const monthlyData = {
                    labels: @json($monthlyTrends->pluck('month')),
                    datasets: [{
                        label: 'Monthly Expenses',
                        data: @json($monthlyTrends->pluck('total')),
                        backgroundColor: '#3B82F6',
                        borderColor: '#2563EB',
                        borderWidth: 2,
                    }]
                };

                new Chart(document.getElementById('monthlyChart'), {
                    type: 'bar',
                    data: monthlyData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return '₹' + context.parsed.y.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return '₹' + value.toLocaleString('en-IN');
                                    }
                                }
                            }
                        }
                    }
                });
            @else
            document.getElementById('monthlyChart').parentElement.innerHTML = '<p class="text-gray-500 text-center py-8">No data available for the selected period.</p>';
        @endif
    </script>
@endsection