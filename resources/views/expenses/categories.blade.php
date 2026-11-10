@extends('layouts.app')

@section('title', 'Expense Categories')
@section('page-title', 'Expense Categories')

@section('content')
    <div class="mb-4 sm:mb-5 md:mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Expense Categories</h2>
        <button onclick="document.getElementById('add-category-form').classList.toggle('hidden')"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Add Custom Category</span>
        </button>
    </div>

    <!-- Add Category Form -->
    <div id="add-category-form"
        class="hidden bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-5 md:p-6 mb-4 sm:mb-5 md:mb-6 border border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Add Custom Category</h3>
        <form action="{{ route('expense-categories.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                <div>
                    <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Category
                        Name *</label>
                    <input type="text" name="name" id="name" required
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>
                <div>
                    <label for="description"
                        class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Description</label>
                    <input type="text" name="description" id="description"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-xl text-sm shadow-md hover:shadow-lg transition-all duration-200">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Add Category</span>
                </button>
                <button type="button" onclick="document.getElementById('add-category-form').classList.add('hidden')"
                    class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Name</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Description</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Type</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Created By</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-gray-800 dark:text-white">{{ $category->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $category->description ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($category->is_custom)
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Custom
                                    </span>
                                @else
                                    <span
                                        class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Predefined
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $category->creator ? $category->creator->name : 'System' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($category->is_custom)
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->description ?? '' }}')"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</button>
                                        <form action="{{ route('expense-categories.destroy', $category) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Delete this category?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">Delete</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">Cannot edit/delete</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No categories found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="md:hidden space-y-3 p-4">
            @forelse($categories as $category)
                <div
                    class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white">{{ $category->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $category->description ?? 'No description' }}</p>
                        </div>
                        @if($category->is_custom)
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                Custom
                            </span>
                        @else
                            <span
                                class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                Predefined
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 font-medium">Created by:
                        {{ $category->creator ? $category->creator->name : 'System' }}</p>
                    @if($category->is_custom)
                        <div class="flex flex-wrap gap-1.5 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <button
                                onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->description ?? '' }}')"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-300 dark:hover:bg-indigo-900/60 transition-colors">Edit</button>
                            <form action="{{ route('expense-categories.destroy', $category) }}" method="POST" class="inline"
                                onsubmit="return confirm('Delete?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:text-red-300 dark:hover:bg-red-900/60 transition-colors">Delete</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-center text-gray-500 py-8">No categories found.</p>
            @endforelse
        </div>

        @if($categories->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <!-- Edit Category Modal -->
    <div id="edit-category-modal"
        class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
        <div
            class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Edit Category</h3>
                <form id="edit-category-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-5">
                        <label for="edit_name"
                            class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Category Name
                            *</label>
                        <input type="text" name="name" id="edit_name" required
                            class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    </div>
                    <div class="mb-5">
                        <label for="edit_description"
                            class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Description</label>
                        <input type="text" name="description" id="edit_description"
                            class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-xl text-sm shadow-md hover:shadow-lg transition-all duration-200 flex-1">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Update</span>
                        </button>
                        <button type="button"
                            onclick="document.getElementById('edit-category-modal').classList.add('hidden')"
                            class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-2.5 px-5 rounded-lg text-sm transition-all duration-200 flex-1">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editCategory(id, name, description) {
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit-category-form').action = '/expense-categories/' + id;
            document.getElementById('edit-category-modal').classList.remove('hidden');
        }
    </script>
@endsection