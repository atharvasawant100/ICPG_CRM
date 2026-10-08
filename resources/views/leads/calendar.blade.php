@extends('layouts.app')

@section('title', 'Follow-up Calendar')
@section('page-title', 'Follow-up Calendar')

@section('content')
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-3 sm:p-5 md:p-6 border border-gray-100 dark:border-gray-700 min-h-[400px] sm:min-h-[500px] h-[calc(100vh-130px)] sm:h-[calc(100vh-140px)] flex flex-col overflow-hidden">
        <div id="calendar" class="flex-1 w-full min-h-0"></div>
    </div>

    <!-- FullCalendar CSS override for better dark mode and custom look -->
    <style>
        :root {
            --fc-border-color: #e5e7eb;
            --fc-button-text-color: #374151;
            --fc-button-bg-color: #ffffff;
            --fc-button-border-color: #d1d5db;
            --fc-button-hover-bg-color: #f3f4f6;
            --fc-button-hover-border-color: #d1d5db;
            --fc-button-active-bg-color: #e5e7eb;
            --fc-button-active-border-color: #d1d5db;
            --fc-event-bg-color: #3b82f6;
            --fc-event-border-color: #3b82f6;
            --fc-today-bg-color: #f3f4f6;
            --fc-now-indicator-color: #ef4444;
            --fc-page-bg-color: #ffffff;
            --fc-neutral-bg-color: #f9fafb;
            --fc-list-event-hover-bg-color: #f3f4f6;
        }

        html.dark {
            --fc-border-color: #4b5563;
            --fc-button-text-color: #e5e7eb;
            --fc-button-bg-color: #374151;
            --fc-button-border-color: #4b5563;
            --fc-button-hover-bg-color: #4b5563;
            --fc-button-hover-border-color: #6b7280;
            --fc-button-active-bg-color: #1f2937;
            --fc-button-active-border-color: #6b7280;
            --fc-today-bg-color: #374151;
            --fc-page-bg-color: #1f2937;
            --fc-neutral-bg-color: #111827;
            --fc-list-event-hover-bg-color: #374151;
            --fc-event-text-color: #ffffff;
        }

        /* General Calendar Styling */
        .fc {
            background-color: var(--fc-page-bg-color);
            color: #374151;
        }
        html.dark .fc,
        .dark .fc {
            background-color: var(--fc-page-bg-color);
            color: #d1d5db;
        }

        /* Toolbar Styling */
        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
        }
        html.dark .fc .fc-toolbar-title,
        .dark .fc .fc-toolbar-title {
            color: #f3f4f6;
        }

        .fc .fc-button {
            padding: 0.5rem 1rem;
            font-weight: 500;
            text-transform: capitalize;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }
        
        .fc .fc-button-primary {
            background-color: var(--fc-button-bg-color);
            border-color: var(--fc-button-border-color);
            color: var(--fc-button-text-color);
        }

        .fc .fc-button-primary:hover {
            background-color: var(--fc-button-hover-bg-color);
            border-color: var(--fc-button-hover-border-color);
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active, 
        .fc .fc-button-primary:not(:disabled):active {
            background-color: var(--fc-button-active-bg-color);
            border-color: var(--fc-button-active-border-color);
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
        }

        .fc .fc-button-group > .fc-button {
            border-radius: 0;
        }
        .fc .fc-button-group > .fc-button:first-child {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }
        .fc .fc-button-group > .fc-button:last-child {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
        
        /* Dark mode specific overrides for buttons to ensure visibility */
        html.dark .fc .fc-button-primary,
        .dark .fc .fc-button-primary {
            background-color: #374151;
            border-color: #4b5563;
            color: #e5e7eb;
        }
        html.dark .fc .fc-button-primary:hover,
        .dark .fc .fc-button-primary:hover {
            background-color: #4b5563;
        }
        html.dark .fc .fc-button-primary:not(:disabled).fc-button-active,
        .dark .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #1f2937;
            border-color: #6b7280;
        }


        /* Calendar Grid Styling */
        .fc th {
            padding: 0.75rem 0;
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        html.dark .fc th,
        .dark .fc th {
            color: #9ca3af;
            background-color: var(--fc-page-bg-color);
        }

        .fc .fc-daygrid-day-number {
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.5rem;
            color: #374151;
            text-decoration: none;
        }
        html.dark .fc .fc-daygrid-day-number,
        .dark .fc .fc-daygrid-day-number {
            color: #d1d5db;
        }

        .fc .fc-col-header-cell-cushion {
            text-decoration: none;
            color: inherit;
        }
        
        /* List View Styling */
        html.dark .fc-list,
        .dark .fc-list {
            background-color: var(--fc-page-bg-color);
        }
        html.dark .fc-list-day-cushion,
        .dark .fc-list-day-cushion {
            background-color: var(--fc-neutral-bg-color);
        }
        html.dark .fc-list-event:hover td,
        .dark .fc-list-event:hover td {
            background-color: var(--fc-list-event-hover-bg-color);
        }

        /* Events Styling */
        .fc-event {
            border: none;
            border-radius: 0.25rem;
            padding: 2px 4px;
            font-size: 0.85rem;
            font-weight: 500;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: transform 0.1s;
        }
        
        .fc-event:hover {
            transform: scale(1.02);
            filter: brightness(0.95);
        }
        
        /* Remove default blue underline on links/events */
        .fc-event a {
            color: inherit;
            text-decoration: none;
        }

        /* Today highlight */
        .fc .fc-day-today {
            background-color: var(--fc-today-bg-color) !important;
        }
        
        /* Responsive: smaller toolbar on mobile */
        @media (max-width: 640px) {
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 0.75rem;
                flex-wrap: wrap;
            }
            .fc .fc-toolbar-title {
                font-size: 1rem;
                order: 1;
                width: 100%;
                text-align: center;
            }
            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 0.25rem;
            }
            .fc .fc-toolbar-chunk:first-child {
                order: 2;
            }
            .fc .fc-toolbar-chunk:last-child {
                order: 3;
                width: 100%;
                justify-content: center;
            }
            .fc .fc-toolbar-chunk:last-child .fc-button-group {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            .fc .fc-button {
                padding: 0.4rem 0.6rem;
                font-size: 0.75rem;
            }
            .fc .fc-button-group .fc-button {
                padding: 0.35rem 0.5rem;
            }
            .fc .fc-daygrid-day-number {
                font-size: 0.75rem;
                padding: 0.25rem;
            }
            .fc .fc-col-header-cell {
                padding: 0.25rem 0;
                font-size: 0.65rem;
            }
            .fc-event {
                font-size: 0.7rem;
                padding: 1px 3px;
            }
            .fc-view-harness {
                min-height: 320px;
            }
        }
        
        /* Ensure responsive height */
        .fc-view-harness {
            min-height: 400px;
        }
        
        /* Dark mode borders and cells */
        html.dark .fc-theme-standard td, 
        html.dark .fc-theme-standard th, 
        html.dark .fc-theme-standard .fc-scrollgrid,
        .dark .fc-theme-standard td, 
        .dark .fc-theme-standard th, 
        .dark .fc-theme-standard .fc-scrollgrid {
            border-color: var(--fc-border-color);
        }
        html.dark .fc-daygrid-day {
            background-color: #1f2937;
        }
        html.dark .fc-daygrid-day.fc-day-today {
            background-color: #374151 !important;
        }
        html.dark .fc-timegrid-slot {
            border-color: #374151;
        }
        html.dark .fc-timegrid-axis {
            background-color: #111827;
            color: #9ca3af;
        }
        html.dark .fc-list-event-dot {
            border-color: #60a5fa;
        }
        html.dark .fc-list-day-cushion,
        html.dark .fc-list-table td {
            background-color: #1f2937;
            color: #e5e7eb;
        }
    </style>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var isMobile = window.innerWidth < 640;
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: isMobile ? 'listWeek' : 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                navLinks: true,
                editable: false,
                dayMaxEvents: true,
                events: "{{ route('leads.calendar') }}",
                themeSystem: 'standard',
                height: '100%',
                contentHeight: 'auto',
                eventClick: function (info) {
                    // Prevent default navigation if we want a modal, but here we let it go to the URL
                    // info.jsEvent.preventDefault(); 
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        return false;
                    }
                },
                eventDidMount: function (info) {
                    // Add tooltips or custom element rendering here if needed
                    // For example, showing status dot
                    if (info.event.extendedProps.status === 'completed') {
                        info.el.style.opacity = '0.7';
                        info.el.style.textDecoration = 'line-through';
                    }
                }
            });
            calendar.render();
        });
    </script>
@endsection