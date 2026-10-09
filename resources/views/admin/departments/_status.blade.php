{{-- Active/inactive pill for a department, shared by the index and detail pages. --}}
@if ($department->is_active)
    <span
        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
        Active</span>
@else
    <span
        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
        Inactive</span>
@endif