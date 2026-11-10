@if ($paginator->hasPages() || $paginator->total() > 0)
<div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/70 dark:bg-gray-800/40 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
    <!-- Range & Per-Page Controls -->
    <div class="flex flex-wrap items-center gap-3 text-gray-500 dark:text-gray-400">
        <span>
            Showing <strong class="font-semibold text-gray-900 dark:text-white">{{ $paginator->firstItem() ?? ($paginator->total() > 0 ? 1 : 0) }}</strong>
            to <strong class="font-semibold text-gray-900 dark:text-white">{{ $paginator->lastItem() ?? $paginator->total() }}</strong>
            of <strong class="font-semibold text-gray-900 dark:text-white">{{ $paginator->total() }}</strong> {{ $label ?? 'tasks' }}
        </span>

        <span class="text-gray-300 dark:text-gray-600 hidden sm:inline">&bull;</span>

        <div class="flex items-center gap-1.5">
            <label for="pagination_per_page_{{ $label ?? 'tasks' }}" class="text-gray-500 dark:text-gray-400 font-medium">Per page:</label>
            <select id="pagination_per_page_{{ $label ?? 'tasks' }}" onchange="window.location.href = this.value"
                class="text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white py-1 px-2.5 focus:border-indigo-500 focus:ring-indigo-500 font-medium">
                @foreach ([10, 15, 25, 50, 100] as $size)
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}"
                        {{ (int) request('per_page', $paginator->perPage()) === $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Navigation Page Buttons -->
    @if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1 flex-wrap">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700/50 cursor-not-allowed">
                &larr; Prev
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 shadow-2xs transition">
                &larr; Prev
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="px-2 py-1 text-gray-400 dark:text-gray-500">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                            class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg text-xs font-bold bg-indigo-600 text-white shadow-xs">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-indigo-50 dark:hover:bg-gray-600 hover:text-indigo-600 dark:hover:text-indigo-300 border border-gray-200 dark:border-gray-600 transition">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 shadow-2xs transition">
                Next &rarr;
            </a>
        @else
            <span class="inline-flex items-center justify-center px-2.5 py-1.5 rounded-lg text-xs font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700/50 cursor-not-allowed">
                Next &rarr;
            </span>
        @endif
    </nav>
    @else
    <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">
        Page 1 of 1
    </span>
    @endif
</div>
@endif
