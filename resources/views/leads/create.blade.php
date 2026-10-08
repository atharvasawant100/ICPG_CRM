@extends('layouts.app')

@section('title', 'Create Lead')
@section('page-title', 'Create Lead')

@section('content')
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 sm:p-6 md:p-8 max-w-4xl mx-auto border border-gray-100 dark:border-gray-700">
        <form action="{{ route('leads.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Name
                        *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Email
                        *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone"
                        class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="company"
                        class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Company</label>
                    <input type="text" name="company" id="company" value="{{ old('company') }}"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                    @error('company')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="source"
                        class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Source</label>
                    <select name="source" id="source"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        <option value="">Select Source</option>
                        <option value="Website" {{ old('source') === 'Website' ? 'selected' : '' }}>Website</option>
                        <option value="Referral" {{ old('source') === 'Referral' ? 'selected' : '' }}>Referral</option>
                        <option value="Social Media" {{ old('source') === 'Social Media' ? 'selected' : '' }}>Social Media
                        </option>
                        <option value="Email Campaign" {{ old('source') === 'Email Campaign' ? 'selected' : '' }}>Email
                            Campaign
                        </option>
                        <option value="Cold Call" {{ old('source') === 'Cold Call' ? 'selected' : '' }}>Cold Call</option>
                        <option value="Trade Show" {{ old('source') === 'Trade Show' ? 'selected' : '' }}>Trade Show</option>
                        <option value="Advertisement" {{ old('source') === 'Advertisement' ? 'selected' : '' }}>Advertisement
                        </option>
                        <option value="Partner" {{ old('source') === 'Partner' ? 'selected' : '' }}>Partner</option>
                        <option value="Other" {{ old('source') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('source')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status"
                        class="block text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Status</label>
                    <select name="status" id="status"
                        class="shadow-sm appearance-none border border-gray-300 dark:border-gray-600 rounded-lg w-full py-2.5 px-4 text-gray-700 dark:text-white dark:bg-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all">
                        <option value="new" {{ old('status', 'new') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="contacted" {{ old('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="interested" {{ old('status') === 'interested' ? 'selected' : '' }}>Interested</option>
                        <option value="qualified" {{ old('status') === 'qualified' ? 'selected' : '' }}>Qualified</option>
                        <option value="converted" {{ old('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                        <option value="lost" {{ old('status') === 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>



            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('leads.index') }}"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium transition-colors">Cancel</a>
                <button type="submit"
                    class="bg-gradient-to-r from-gray-800 to-gray-700 hover:from-gray-700 hover:to-gray-600 dark:from-blue-600 dark:to-blue-700 dark:hover:from-blue-500 dark:hover:to-blue-600 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition-all duration-200">
                    Create Lead
                </button>
            </div>
        </form>
    </div>
@endsection