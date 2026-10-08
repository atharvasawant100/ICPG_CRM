@props(['status'])
@php
    $statusColors = [
        'draft' => 'bg-zinc-200 text-zinc-900 dark:bg-zinc-600 dark:text-zinc-50',
        'sent' => 'bg-blue-100 text-blue-900 dark:bg-blue-800 dark:text-blue-50',
        'partial' => 'bg-amber-100 text-amber-900 dark:bg-amber-800 dark:text-amber-50',
        'paid' => 'bg-green-100 text-green-900 dark:bg-green-800 dark:text-green-50',
        'overdue' => 'bg-red-100 text-red-900 dark:bg-red-800 dark:text-red-50',
        'cancelled' => 'bg-rose-200 text-rose-950 dark:bg-rose-900 dark:text-rose-50',
    ];
    $color = $statusColors[$status] ?? 'bg-zinc-200 text-zinc-900 dark:bg-zinc-600 dark:text-zinc-50';
@endphp
<span {{ $attributes->merge(['class' => 'px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ' . $color]) }}>
    {{ ucfirst($status) }}
</span>
