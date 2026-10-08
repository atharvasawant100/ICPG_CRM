@extends('layouts.app')

@section('title', 'Lead Details')
@section('page-title', 'Lead Details')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT SIDEBAR -->
        <div class="col-span-12 lg:col-span-4 xl:col-span-3 space-y-6">

            <!-- Header Info -->
            <div>
                <div class="flex flex-wrap gap-2 mb-3">

                    <span
                        class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded border border-yellow-200">
                        {{ $lead->status }}
                    </span>
                </div>

                <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ $lead->name }}
                    @if($lead->company)
                        <span
                            class="block text-sm font-normal text-gray-500 dark:text-gray-400 mt-1">{{ $lead->company }}</span>
                    @endif
                </h1>

                <!-- Action Buttons -->
                <div class="grid grid-cols-4 gap-2 mb-6">
                    <!-- Mail -->
                    <!-- Mail (Hidden) -->
                    {{-- <a href="mailto:{{ $lead->email }}"
                        class="flex flex-col items-center justify-center p-3 rounded-xl bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 hover:bg-green-200 transition-colors group">
                        <div
                            class="w-10 h-10 rounded-full bg-white dark:bg-green-800 flex items-center justify-center shadow-sm mb-2 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold">Mail</span>
                    </a> --}}

                    <!-- File (Placeholder) -->
                    <!-- Phone -->
                    <a href="tel:{{ $lead->phone }}" onclick="trackAction('call')"
                        class="flex flex-col items-center justify-center p-3 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 transition-colors group">
                        <div
                            class="w-10 h-10 rounded-full bg-white dark:bg-blue-800 flex items-center justify-center shadow-sm mb-2 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold">Call</span>
                    </a>

                    <!-- WhatsApp -->
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank"
                        onclick="trackAction('whatsapp')"
                        class="flex flex-col items-center justify-center p-3 rounded-xl bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 hover:bg-green-200 transition-colors group">
                        <div
                            class="w-10 h-10 rounded-full bg-white dark:bg-green-800 flex items-center justify-center shadow-sm mb-2 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.272-.57-.422l-.001.001zM12.001 22C6.478 22 2 17.522 2 12S6.478 2 12.001 2s10.001 4.478 10.001 10-4.479 10-10.001 10z" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold">WhatsApp</span>
                    </a>

                    <!-- Note -->
                    <a href="#quick-note"
                        class="flex flex-col items-center justify-center p-3 rounded-xl bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200 transition-colors group">
                        <div
                            class="w-10 h-10 rounded-full bg-white dark:bg-orange-800 flex items-center justify-center shadow-sm mb-2 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold">Note</span>
                    </a>

                    <!-- Activity -->
                    <a href="#activity-timeline"
                        class="flex flex-col items-center justify-center p-3 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 transition-colors group">
                        <div
                            class="w-10 h-10 rounded-full bg-white dark:bg-blue-800 flex items-center justify-center shadow-sm mb-2 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold">Activity</span>
                    </a>
                </div>
            </div>

            <!-- About Lead Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div
                    class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50 rounded-t-xl">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">About Lead</h3>
                    <a href="{{ route('leads.edit', $lead) }}"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                            </path>
                        </svg>
                    </a>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex justify-between items-center group">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Email</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200 text-right truncate max-w-[150px]"
                            title="{{ $lead->email }}">{{ $lead->email }}</span>
                    </div>
                    <div class="flex justify-between items-center group">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Phone</span>
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-gray-200 text-right">{{ $lead->phone ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center group">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Source</span>
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-gray-200 text-right">{{ $lead->source ?? '-' }}</span>
                    </div>
                    @if(!empty($lead->formatted_extra_fields))
                        @foreach($lead->formatted_extra_fields as $fieldLabel => $fieldValue)
                            <div class="flex justify-between items-start group gap-3">
                                <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">{{ $fieldLabel }}</span>
                                <span
                                    class="text-sm font-medium text-gray-800 dark:text-gray-200 text-right break-words">{{ $fieldValue !== '' ? $fieldValue : '-' }}</span>
                            </div>
                        @endforeach
                    @endif
                    <div class="flex justify-between items-center group">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Created</span>
                        <span
                            class="text-sm font-medium text-gray-800 dark:text-gray-200 text-right">{{ $lead->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <!-- Delete Button Area -->
                <div
                    class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 rounded-b-xl flex justify-center">
                    @if(!$lead->isConverted() && $lead->status !== 'lost')
                        <form action="{{ route('leads.convert', $lead) }}" method="POST" class="w-full mb-2"
                            onsubmit="return confirm('Convert this lead to customer?');">
                            @csrf
                            <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors mb-2">
                                Convert to Customer
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('leads.destroy', $lead) }}" method="POST" class="w-full"
                        onsubmit="return confirm('Are you sure you want to delete this lead?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 py-2 rounded-lg text-sm font-semibold transition-colors">
                            Delete Lead
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Note for Sidebar (optional, keeping it in main for now) -->

        </div>

        <!-- MAIN CONTENT -->
        <div class="col-span-12 lg:col-span-8 xl:col-span-9 space-y-6">

            <!-- Status Progression Bar -->
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center text-sm font-medium overflow-x-auto scrollbar-hide">
                    @php
                        $stages = ['new', 'contacted', 'interested', 'qualified', 'converted', 'lost'];
                        $currentStageIndex = array_search($lead->status, $stages);
                        if ($currentStageIndex === false)
                            $currentStageIndex = -1;
                    @endphp

                    @foreach($stages as $index => $stage)
                        @php
                            $isActive = $index <= $currentStageIndex;
                            $isCurrent = $stage === $lead->status;

                            $baseColor = 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400';

                            $stageColors = [
                                'new' => 'bg-blue-500 text-white',
                                'contacted' => 'bg-indigo-500 text-white',
                                'interested' => 'bg-cyan-500 text-white',
                                'qualified' => 'bg-purple-500 text-white',
                                'converted' => 'bg-green-500 text-white',
                                'lost' => 'bg-red-500 text-white',
                            ];

                            if ($lead->status === 'lost') {
                                $colorClass = 'bg-red-500 text-white';
                            } else {
                                $activeColor = $stageColors[$stage] ?? 'bg-gray-500 text-white';
                                $colorClass = $isActive ? $activeColor : $baseColor;
                            }
                            $isClickable = !$isCurrent && $stage !== 'converted';
                        @endphp

                        @if($isClickable)
                            <form action="{{ route('leads.update', $lead) }}" method="POST" class="flex-1 flex min-w-[100px]">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $stage }}">
                                <button type="submit"
                                    class="w-full relative py-3 px-4 text-center {{ $colorClass }} {{ $index !== 0 ? 'border-l border-white dark:border-gray-800' : '' }} hover:opacity-90 transition-opacity">
                                    {{ ucfirst($stage) }}
                                </button>
                            </form>
                        @else
                            <div
                                class="flex-1 relative py-3 px-4 text-center {{ $colorClass }} {{ $index !== 0 ? 'border-l border-white dark:border-gray-800' : '' }} min-w-[100px]">
                                {{ ucfirst($stage) }}
                                @if($isCurrent)
                                    <span
                                        class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-b-[6px] border-b-white dark:border-b-gray-800"></span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Tabs & Content -->
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 min-h-[500px]">

                <!-- Tabs Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 px-6">
                    <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                        <button onclick="switchTab('timeline')" id="tab-timeline"
                            class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600 dark:text-blue-400">
                            Activity Timeline
                        </button>
                        <button onclick="switchTab('notes')" id="tab-notes"
                            class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300">
                            Notes
                        </button>
                        <button onclick="switchTab('followups')" id="tab-followups"
                            class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300">
                            Follow Ups
                        </button>
                    </nav>
                </div>

                <!-- Tab Contents -->
                <div class="p-6">

                    <!-- TIMELINE TAB -->
                    <div id="content-timeline" class="tab-content space-y-6">
                        <!-- Quick Note (Always handy on timeline) -->
                        <div id="quick-note"
                            class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg border border-gray-200 dark:border-gray-600 mb-6">
                            <form action="{{ route('leads.notes.store', $lead) }}" method="POST">
                                @csrf
                                <textarea name="note" rows="2" placeholder="Write a note..."
                                    class="w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:text-gray-200 text-sm p-3"></textarea>
                                <div class="flex justify-between items-center mt-3">
                                    @can('leads.notes.create')
                                    <label class="flex items-center text-xs text-gray-600 dark:text-gray-400">
                                        <input type="checkbox" name="is_internal" value="1"
                                            class="mr-2 rounded border-gray-300 text-blue-600">
                                        Internal Note
                                    </label>
                                    @endcan
                                    @can('leads.notes.create')
                                    <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded text-sm font-medium transition-colors">Post
                                        Note</button>
                                    @endcan
                                </div>
                            </form>
                        </div>

                        <div id="activity-timeline"
                            class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-300 before:to-transparent">
                            @forelse($lead->activities as $activity)
                                <div
                                    class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-gray-100 dark:bg-gray-700 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 relative z-10">
                                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div
                                        class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white dark:bg-gray-700 p-4 rounded-xl border border-gray-100 dark:border-gray-600 shadow-sm">
                                        <div class="flex items-center justify-between space-x-2 mb-1">
                                            <div class="font-bold text-gray-900 dark:text-white text-sm">
                                                {{ $activity->description }}
                                            </div>
                                            <time
                                                class="font-caveat font-medium text-xs text-indigo-500">{{ $activity->created_at->format('M d, H:i A') }}</time>
                                        </div>
                                        <div class="text-gray-500 dark:text-gray-400 text-xs">By {{ $activity->user->name }}
                                        </div>
                                        @if($activity->old_value)
                                            <div class="mt-2 text-xs bg-gray-50 dark:bg-gray-800 p-2 rounded">
                                                <span class="line-through text-red-400">{{ $activity->old_value }}</span>
                                                <span class="text-gray-400 mx-1">→</span>
                                                <span class="text-green-500 font-medium">{{ $activity->new_value }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 dark:text-gray-400 py-10">No activities found.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- NOTES TAB -->
                    <div id="content-notes" class="tab-content hidden space-y-4">
                        @forelse($lead->notes as $note)
                            <div
                                class="bg-yellow-50 dark:bg-yellow-900/10 p-4 rounded-xl border border-yellow-200 dark:border-yellow-700/50">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-yellow-200 dark:bg-yellow-800 flex items-center justify-center text-yellow-700 dark:text-yellow-300 font-bold text-xs">
                                            {{ substr($note->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $note->user->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $note->created_at->format('M d, Y h:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                    @if($note->is_internal)
                                        <span
                                            class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded border border-red-200">INTERNAL</span>
                                    @endif
                                </div>
                                <p class="text-gray-700 dark:text-gray-300 text-sm whitespace-pre-wrap pl-10">{{ $note->note }}
                                </p>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 dark:text-gray-400 py-10">No notes found.</p>
                        @endforelse
                    </div>

                    <!-- FOLLOWUPS TAB -->
                    <div id="content-followups" class="tab-content hidden space-y-4">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100">Follow Ups</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Schedule calls and meetings for this lead</p>
                            </div>
                            <button onclick="document.getElementById('followup-form-tab').classList.toggle('hidden')"
                                class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Schedule Follow Up
                            </button>
                        </div>

                        <!-- Hidden Form -->
                        <div id="followup-form-tab"
                            class="hidden mb-6 p-5 bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-200 dark:border-gray-600">
                            <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-4">New Follow Up</h4>
                            <form action="{{ route('leads.followups.store', $lead) }}" method="POST">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="followup_date" class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">Date &amp; Time <span class="text-red-500">*</span></label>
                                        <input type="datetime-local" name="followup_date" id="followup_date" required
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 p-2.5 text-sm text-gray-700 dark:text-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    </div>
                                    <div>
                                        <label for="followup_type" class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">Type</label>
                                        <select name="followup_type" id="followup_type"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 p-2.5 text-sm text-gray-700 dark:text-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                            <option value="call">📞 Call</option>
                                            <option value="meeting">🤝 Meeting</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="followup_description" class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1.5">Description</label>
                                    <textarea name="description" id="followup_description" placeholder="What is this follow up about?" rows="2"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 p-2.5 text-sm text-gray-700 dark:text-gray-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        onclick="document.getElementById('followup-form-tab').classList.add('hidden')"
                                        class="px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-5 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">
                                        Save Follow Up
                                    </button>
                                </div>
                            </form>
                        </div>

                        @forelse($lead->followups as $followup)
                            @php
                                $isCompleted = $followup->status === 'completed';
                                $isOverdue = !$isCompleted && $followup->isOverdue();
                            @endphp
                            <div
                                class="flex items-start gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow">
                                <div
                                    class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center {{ $isCompleted ? 'bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400' : ($isOverdue ? 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400' : 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400') }}">
                                    @if($followup->followup_type === 'meeting')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start gap-2">
                                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                                            {{ ucfirst($followup->followup_type) }}
                                        </h4>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $followup->followup_date->format('M d, Y · h:i A') }}
                                        </span>
                                    </div>
                                    @if($followup->description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $followup->description }}</p>
                                    @endif
                                    <div class="mt-2.5 flex items-center gap-2">
                                        @if($isCompleted)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">✓ Completed</span>
                                        @elseif($isOverdue)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">Overdue</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">Pending</span>
                                        @endif
                                        @if($followup->status === 'pending')
                                            <form action="{{ route('leads.followups.complete', [$lead, $followup]) }}"
                                                method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 hover:bg-green-100 hover:text-green-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-green-900/40 dark:hover:text-green-300 transition-colors">
                                                    Mark Complete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="mx-auto w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                    <svg class="w-7 h-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">No follow ups scheduled</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Click "Schedule Follow Up" to add one.</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all contents
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            // Show selected content
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Reset all tabs
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                el.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });

            // Activate selected tab
            const activeBtn = document.getElementById('tab-' + tabName);
            activeBtn.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            activeBtn.classList.add('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
        }

        function trackAction(action) {
            fetch("{{ route('leads.track-action', $lead) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    action: action
                })
            }).then(response => {
                if (response.ok) {
                    console.log('Action tracked: ' + action);
                    // Refresh the activity timeline
                    location.reload();
                }
            }).catch(error => console.error('Error tracking action:', error));
        }
    </script>
@endsection