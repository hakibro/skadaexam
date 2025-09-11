@extends('layouts.admin')

@section('title', 'Kalender Pengawas')
@section('page-title', 'Kalender Pengawas')
@section('page-description', 'Lihat jadwal penugasan pengawas dalam bentuk kalender')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css" rel="stylesheet">
    <style>
        #loading-indicator.hidden {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

        .fc .fc-daygrid-body,
        .fc table,
        .fc tr,
        .fc td,
        .fc th {
            border-color: #ddd !important;
        }

        .fc table {
            width: 100% !important;
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            table-layout: fixed !important;
        }

        .fc td,
        .fc th {
            padding: 0 !important;
            vertical-align: top !important;
            text-align: center !important;
        }

        .fc .fc-scrollgrid,
        .fc .fc-daygrid-body {
            width: 100% !important;
        }

        #calendar {
            min-width: 100% !important;
        }

        .fc {
            width: 100% !important;
            font-size: 1em !important;
        }

        .fc-scrollgrid {
            width: 100% !important;
            border-collapse: separate !important;
        }

        .fc-event {
            cursor: pointer;
            transition: all 0.2s ease;
            border-left-width: 4px !important;
            margin: 2px 0 !important;
            padding: 2px 4px !important;
            white-space: normal !important;
            overflow: hidden;
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
            text-align: left !important;
            display: block !important;
        }

        .fc-h-event .fc-event-main {
            padding: 1px 2px !important;
            display: block !important;
            color: white !important;
        }

        .fc-daygrid-event-harness {
            margin-top: 1px !important;
            margin-bottom: 1px !important;
        }

        .fc-event:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .fc-daygrid-day.fc-day-today {
            background-color: rgba(59, 130, 246, 0.1) !important;
        }

        .fc-day-past .fc-daygrid-day-number {
            opacity: 0.6;
        }

        /* Fix month view layout */
        .fc-daygrid-day-frame {
            min-height: 80px !important;
            height: 100% !important;
        }

        .fc-day-sat,
        .fc-day-sun {
            background-color: #f9fafb !important;
        }

        .fc-daygrid-day-top {
            display: flex !important;
            justify-content: flex-end !important;
            padding-right: 2px !important;
        }

        .fc-daygrid-day-events {
            padding: 2px !important;
            min-height: 2rem !important;
        }

        .fc-daygrid-day-number {
            padding: 4px !important;
            font-weight: 500 !important;
        }

        .fc-button {
            display: inline-block !important;
            padding: 0.4rem 0.75rem !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            line-height: 1.25rem !important;
            border-radius: 0.25rem !important;
            border: 1px solid transparent !important;
            text-align: center !important;
            margin: 0 2px !important;
        }

        .fc-button-primary {
            background-color: #4F46E5 !important;
            border-color: #4F46E5 !important;
            color: white !important;
        }

        .fc-button-primary:hover {
            background-color: #4338CA !important;
            border-color: #4338CA !important;
        }

        .fc-button-primary:not(:disabled):active,
        .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #3730A3 !important;
            border-color: #3730A3 !important;
        }

        .fc-toolbar {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            margin-bottom: 1rem !important;
        }

        .fc-toolbar-title {
            font-weight: 600 !important;
            color: #1F2937 !important;
            font-size: 1.25rem !important;
            line-height: 1.75rem !important;
        }

        .loading-indicator {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
            border-radius: 0.5rem;
        }

        .fc th {
            padding: 10px 0 !important;
            background-color: #F3F4F6 !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.025em !important;
        }

        .fc-col-header-cell-cushion {
            padding: 8px 4px !important;
            color: #374151 !important;
            text-decoration: none !important;
        }

        .fc a {
            color: inherit !important;
            text-decoration: none !important;
        }

        /* Fix more events link */
        .fc-daygrid-more-link {
            display: block !important;
            padding: 1px 4px !important;
            text-align: center !important;
            color: #4F46E5 !important;
            font-size: 0.75rem !important;
            font-weight: 500 !important;
        }

        /* Fix popover */
        .fc-popover {
            z-index: 40 !important;
            border-radius: 0.375rem !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }

        @media (max-width: 640px) {
            .fc-toolbar.fc-header-toolbar {
                flex-direction: column !important;
                gap: 0.75rem !important;
            }

            .fc-toolbar-chunk {
                display: flex !important;
                justify-content: center !important;
                margin-bottom: 4px !important;
                width: 100% !important;
            }

            .fc-toolbar-title {
                font-size: 1.125rem !important;
            }

            .fc-button {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.75rem !important;
            }

            .fc-daygrid-day-frame {
                min-height: 60px !important;
            }
        }
    </style>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('koordinator.dashboard') }}"
                        class="inline-flex items-center text-sm text-gray-500 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                            </path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Kalender Pengawas</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="border-b border-gray-200 pb-4 mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Pilih Pengawas untuk Melihat Jadwal</h2>
                <p class="text-gray-500 text-sm mt-1">Pilih pengawas untuk melihat jadwal penugasan mereka dalam bentuk
                    kalender</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-2">
                    <label for="pengawas_id" class="block text-sm font-medium text-gray-700 mb-2">Pengawas</label>
                    <div class="relative">
                        <select id="pengawas_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pl-4 pr-8 py-2">
                            <option value="">-- Pilih Pengawas --</option>
                            @foreach ($pengawas as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->nip ?? 'Tanpa NIP' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 flex items-end justify-end">
                    <button id="printBtn"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-print"></i> Cetak Jadwal Pengawas
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Kalender Jadwal Pengawas</h2>
                <p class="text-gray-500 text-sm mt-1">Visualisasi jadwal penugasan pengawas dalam bentuk kalender interaktif
                </p>
            </div>

            <div id="pengawas-info" class="mb-6 hidden">
                <div class="p-5 bg-indigo-50 rounded-lg border border-indigo-100 shadow-sm">
                    <div class="flex items-center mb-2">
                        <div class="bg-indigo-100 rounded-full p-2 mr-3">
                            <i class="fa-solid fa-user-tie text-indigo-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-indigo-800 text-lg" id="pengawas-name"></h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pl-12">
                        <div class="flex items-center">
                            <i class="fa-solid fa-id-card text-indigo-500 mr-2"></i>
                            <span class="text-gray-600 text-sm">NIP:</span>
                            <span class="font-medium ml-2" id="pengawas-nip">-</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fa-solid fa-envelope text-indigo-500 mr-2"></i>
                            <span class="text-gray-600 text-sm">Email:</span>
                            <span class="font-medium ml-2" id="pengawas-email">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div id="loading-indicator" class="loading-indicator hidden">
                    <div class="bg-white p-4 rounded-lg shadow-lg flex flex-col items-center">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600 mb-2"></div>
                        <span class="text-indigo-800 font-medium">Memuat jadwal...</span>
                    </div>
                </div>

                <div id="calendar" class="mt-4 w-full"></div>

                <div id="no-pengawas" class="mt-6 p-6 bg-yellow-50 rounded-lg border border-yellow-100 text-center">
                    <div class="inline-block bg-yellow-100 rounded-full p-3 mb-3">
                        <i class="fa-solid fa-user-clock text-yellow-700 text-xl"></i>
                    </div>
                    <p class="text-yellow-800 font-medium">Silakan pilih pengawas terlebih dahulu untuk melihat jadwal.</p>
                    <p class="text-yellow-600 text-sm mt-1">Jadwal akan ditampilkan setelah pengawas dipilih.</p>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-6 border-t border-gray-200 pt-4">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Keterangan Status:</h3>
                <div class="flex flex-wrap gap-3">
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 mr-2 bg-blue-500 rounded-full"></span>
                        <span class="text-sm text-gray-700">Belum Mulai</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 mr-2 bg-green-500 rounded-full"></span>
                        <span class="text-sm text-gray-700">Berlangsung</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 mr-2 bg-gray-500 rounded-full"></span>
                        <span class="text-sm text-gray-700">Selesai</span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-block w-3 h-3 mr-2 bg-red-500 rounded-full"></span>
                        <span class="text-sm text-gray-700">Dibatalkan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div id="eventDetailModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true" role="dialog">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modalBackdrop"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" id="eventTitle">Detail Jadwal Pengawas</h3>
                            <p class="text-sm text-gray-600 mt-1" id="eventDate"></p>
                        </div>
                        <button type="button" id="closeModalX" class="text-gray-500 hover:text-gray-700">
                            <i class="fa-solid fa-times text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="bg-white px-6 py-5">
                    <div class="grid grid-cols-1 gap-5">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <div class="bg-indigo-100 rounded-md p-2">
                                    <i class="fa-solid fa-door-open text-indigo-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-700">Ruangan</p>
                                <p class="text-base font-semibold text-gray-900" id="eventRoom"></p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <div class="bg-indigo-100 rounded-md p-2">
                                    <i class="fa-solid fa-clock text-indigo-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-700">Waktu</p>
                                <p class="text-base font-semibold text-gray-900" id="eventTime"></p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <div class="bg-indigo-100 rounded-md p-2">
                                    <i class="fa-solid fa-book text-indigo-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-700">Mata Pelajaran</p>
                                <p class="text-base font-semibold text-gray-900" id="eventSubject"></p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <div class="bg-indigo-100 rounded-md p-2">
                                    <i class="fa-solid fa-info-circle text-indigo-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-700">Status</p>
                                <div id="eventStatus"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                    <button type="button" id="closeEventDetail"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-all font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Tutup
                    </button>
                    <a href="#" id="viewDetailBtn"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fa-solid fa-external-link-alt mr-2"></i>Lihat Detail
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pengawasSelect = document.getElementById('pengawas_id');
            const pengawasInfo = document.getElementById('pengawas-info');
            const pengawasName = document.getElementById('pengawas-name');
            const pengawasNip = document.getElementById('pengawas-nip');
            const pengawasEmail = document.getElementById('pengawas-email');
            const noPengawas = document.getElementById('no-pengawas');
            const printBtn = document.getElementById('printBtn');
            const loadingIndicator = document.getElementById('loading-indicator');

            // Event modal elements
            const eventDetailModal = document.getElementById('eventDetailModal');
            const modalBackdrop = document.getElementById('modalBackdrop');
            const closeEventDetail = document.getElementById('closeEventDetail');
            const closeModalX = document.getElementById('closeModalX');
            const eventTitle = document.getElementById('eventTitle');
            const eventDate = document.getElementById('eventDate');
            const eventRoom = document.getElementById('eventRoom');
            const eventTime = document.getElementById('eventTime');
            const eventSubject = document.getElementById('eventSubject');
            const eventStatus = document.getElementById('eventStatus');
            const viewDetailBtn = document.getElementById('viewDetailBtn');

            // Initialize Calendar
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                expandRows: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                height: 'auto',
                contentHeight: 650,
                aspectRatio: 1.35,
                locale: 'id',
                firstDay: 1, // Monday start
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    week: 'Minggu',
                    day: 'Hari'
                },
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false,
                    hour12: false
                },
                dayMaxEventRows: 3,
                moreLinkText: count => `+${count} lagi`,
                slotMinTime: '07:00:00',
                slotMaxTime: '18:00:00',
                fixedWeekCount: false,
                showNonCurrentDates: true,
                eventClick: function(info) {
                    console.log('Event clicked:', info.event);
                    console.log('Event extended props:', info.event.extendedProps);
                    showEventDetail(info.event);
                },
                loading: function(isLoading) {
                    if (isLoading) {
                        loadingIndicator.classList.remove('hidden');
                    } else {
                        // Ensure loading indicator is hidden immediately
                        loadingIndicator.classList.add('hidden');

                        const events = calendar.getEvents();
                        if (events.length === 0 && pengawasSelect.value) {
                            noPengawas.innerHTML = `
                                <div class="inline-block bg-yellow-100 rounded-full p-3 mb-3">
                                    <i class="fa-solid fa-calendar-times text-yellow-700 text-xl"></i>
                                </div>
                                <p class="text-yellow-800 font-medium">Tidak ada jadwal yang ditemukan untuk pengawas ini.</p>
                                <p class="text-yellow-600 text-sm mt-1">Pengawas belum memiliki jadwal penugasan atau jadwal telah selesai.</p>
                            `;
                            noPengawas.classList.remove('hidden');
                        } else if (pengawasSelect.value && events.length > 0) {
                            // Only hide no-pengawas message if there are events
                            noPengawas.classList.add('hidden');
                        }

                        // Force redraw of calendar and apply custom styling
                        setTimeout(() => {
                            calendar.updateSize();

                            // Apply custom styling to events after rendering
                            document.querySelectorAll('.fc-event').forEach(event => {
                                event.style.textAlign = 'left';
                                event.style.display = 'block';
                            });
                        }, 50);
                    }
                },
                noEventsContent: 'Tidak ada jadwal',
                dayMaxEvents: true,
                views: {
                    timeGrid: {
                        dayMaxEventRows: 3
                    }
                },
                eventDidMount: function(info) {
                    // Add tooltip with event details
                    const tooltip = document.createElement('div');
                    tooltip.classList.add('bg-white', 'p-2', 'shadow-lg', 'rounded-md', 'border',
                        'text-sm', 'z-50', 'absolute');
                    // Get mapel, checking different property paths
                    const mapel = info.event.extendedProps?.mapel ||
                        info.event._def?.extendedProps?.mapel ||
                        'Mata Pelajaran Tidak Diketahui';

                    tooltip.innerHTML = `
                        <div class="font-bold">${info.event.title}</div>
                        <div>${formatTime(info.event.start)} - ${formatTime(info.event.end)}</div>
                        <div>${mapel}</div>
                    `;

                    const mouseEnterHandler = () => {
                        document.body.appendChild(tooltip);
                        const rect = info.el.getBoundingClientRect();
                        tooltip.style.position = 'fixed';
                        tooltip.style.top = rect.bottom + 'px';
                        tooltip.style.left = rect.left + 'px';
                    };

                    const mouseLeaveHandler = () => {
                        if (document.body.contains(tooltip)) {
                            document.body.removeChild(tooltip);
                        }
                    };

                    info.el.addEventListener('mouseenter', mouseEnterHandler);
                    info.el.addEventListener('mouseleave', mouseLeaveHandler);

                    return function() {
                        info.el.removeEventListener('mouseenter', mouseEnterHandler);
                        info.el.removeEventListener('mouseleave', mouseLeaveHandler);
                    };
                }
            });

            calendar.render();

            // Handle pengawas selection
            pengawasSelect.addEventListener('change', function() {
                const pengawasId = this.value;
                if (!pengawasId) {
                    pengawasInfo.classList.add('hidden');
                    noPengawas.innerHTML = `
                        <div class="inline-block bg-yellow-100 rounded-full p-3 mb-3">
                            <i class="fa-solid fa-user-clock text-yellow-700 text-xl"></i>
                        </div>
                        <p class="text-yellow-800 font-medium">Silakan pilih pengawas terlebih dahulu untuk melihat jadwal.</p>
                        <p class="text-yellow-600 text-sm mt-1">Jadwal akan ditampilkan setelah pengawas dipilih.</p>
                    `;
                    noPengawas.classList.remove('hidden');
                    loadingIndicator.classList.add(
                        'hidden'); // Hide loading indicator if no pengawas selected
                    calendar.getEvents().forEach(e => e.remove());
                    return;
                }

                // Show pengawas info and prepare display
                const selectedOption = this.options[this.selectedIndex];
                const namaParts = selectedOption.text.match(/(.*?)\s*\((.*?)\)/);

                if (namaParts && namaParts.length > 2) {
                    pengawasName.textContent = namaParts[1].trim();
                    pengawasNip.textContent = namaParts[2].trim();
                } else {
                    pengawasName.textContent = selectedOption.text;
                    pengawasNip.textContent = '-';
                }

                pengawasEmail.textContent = '-'; // Default value if not available

                // Show loading indicator
                loadingIndicator.classList.remove('hidden');

                // Get pengawas details and calendar events from the database
                fetch(
                        `/koordinator/pengawas-assignment/calendar-events?pengawas_id=${pengawasId}&start_date=${getStartDate()}&end_date=${getEndDate()}`
                    )
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Clear previous events
                        calendar.getEvents().forEach(e => e.remove());

                        // Debug: Log all events to check their structure
                        console.log('All events data:', data);
                        if (data.length > 0) {
                            console.log('First event data sample:', data[0]);
                            console.log('mapel value in first event:', data[0].mapel);
                            console.log('description value in first event:', data[0].description);
                            console.log('extendedProps in first event:', data[0].extendedProps);
                        }

                        // Add new events
                        data.forEach(event => {
                            calendar.addEvent(event);
                        });

                        // Show pengawas info panel
                        pengawasInfo.classList.remove('hidden');

                        // Explicitly hide the "No Pengawas" message
                        noPengawas.classList.add('hidden');

                        // Explicitly hide loading indicator to ensure it's hidden
                        loadingIndicator.classList.add('hidden');

                        // Force calendar redraw and fix event styling
                        setTimeout(() => {
                                calendar.updateSize();

                                // Apply custom styling to events after adding
                                document.querySelectorAll('.fc-event').forEach(event => {
                                    event.style.textAlign = 'left';
                                    event.style.display = 'block';
                                });
                            },
                            100
                        ); // Calendar will handle showing/hiding no events message via loading callback
                    })
                    .catch(error => {
                        console.error('Error fetching events:', error);
                        loadingIndicator.classList.add('hidden');

                        // Show error message
                        noPengawas.innerHTML = `
                            <div class="inline-block bg-red-100 rounded-full p-3 mb-3">
                                <i class="fa-solid fa-exclamation-triangle text-red-600 text-xl"></i>
                            </div>
                            <p class="text-red-800 font-medium">Gagal memuat jadwal pengawas.</p>
                            <p class="text-red-600 text-sm mt-1">Silakan coba lagi atau hubungi administrator.</p>
                        `;
                        noPengawas.classList.remove('hidden');
                    });
            });

            // Handle print button
            printBtn.addEventListener('click', function() {
                const pengawasId = pengawasSelect.value;
                if (!pengawasId) {
                    alert('Silakan pilih pengawas terlebih dahulu.');
                    return;
                }

                // Print function here - open a new window with printable schedule for all dates
                window.open(`/koordinator/pengawas-assignment/schedule/${pengawasId}`,
                    '_blank');
            });

            // Handle window resize to properly adjust calendar
            window.addEventListener('resize', function() {
                if (calendar) {
                    setTimeout(() => {
                        calendar.updateSize();
                    }, 100);
                }
            });

            // Event detail modal
            function showEventDetail(event) {
                eventTitle.textContent = event.title;
                eventDate.textContent = formatDate(event.start);

                // Handle different ways of accessing properties
                const mapel = event.extendedProps?.mapel || event._def?.extendedProps?.mapel ||
                    'Mata Pelajaran Tidak Diketahui';
                const ruangan = event.extendedProps?.ruangan || event._def?.extendedProps?.ruangan ||
                    'Ruangan Tidak Diketahui';

                // Debug the available properties
                console.log('Event object:', event);
                console.log('Direct mapel:', event.mapel);
                console.log('Direct extendedProps:', event.extendedProps);
                console.log('_def if exists:', event._def);

                eventRoom.textContent = ruangan;
                eventTime.textContent = formatTime(event.start) + ' - ' + formatTime(event.end);
                eventSubject.textContent = mapel;

                // Set status
                const status = getStatusFromColor(event.backgroundColor);
                eventStatus.innerHTML =
                    `<span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClasses(status)}">${status}</span>`;

                // Set view detail link
                if (event.id) {
                    viewDetailBtn.style.display = 'inline-flex';
                    // This would typically link to a page showing details about this specific assignment
                    viewDetailBtn.href = `/koordinator/pengawas-assignment/detail/${event.id}`;
                } else {
                    viewDetailBtn.style.display = 'none';
                }

                // Show modal with animation
                eventDetailModal.classList.remove('hidden');
                setTimeout(() => {
                    eventDetailModal.querySelector('.inline-block').classList.add('scale-100');
                    eventDetailModal.querySelector('.inline-block').classList.remove('scale-95');
                }, 10);
            }

            closeEventDetail.addEventListener('click', function() {
                closeModal();
            });

            closeModalX.addEventListener('click', function() {
                closeModal();
            });

            modalBackdrop.addEventListener('click', function() {
                closeModal();
            });

            function closeModal() {
                eventDetailModal.classList.add('hidden');
            }

            // Helper functions
            function getStartDate() {
                const date = new Date();
                date.setMonth(date.getMonth() - 1); // Get events from 1 month ago
                return date.toISOString().split('T')[0];
            }

            function getEndDate() {
                const date = new Date();
                date.setMonth(date.getMonth() + 3); // Get events for the next 3 months
                return date.toISOString().split('T')[0];
            }

            function getCurrentDate() {
                return new Date().toISOString().split('T')[0];
            }

            function formatDate(date) {
                return new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }).format(date);
            }

            function formatTime(date) {
                if (!date) return '--:--';
                return date instanceof Date ?
                    date.toTimeString().substring(0, 5) :
                    new Date(date).toTimeString().substring(0, 5);
            }

            function getStatusFromColor(color) {
                switch (color) {
                    case '#3B82F6':
                        return 'Belum Mulai';
                    case '#10B981':
                        return 'Berlangsung';
                    case '#6B7280':
                        return 'Selesai';
                    case '#EF4444':
                        return 'Dibatalkan';
                    default:
                        return 'Tidak Diketahui';
                }
            }

            function getStatusClasses(status) {
                switch (status) {
                    case 'Belum Mulai':
                        return 'bg-blue-100 text-blue-800';
                    case 'Berlangsung':
                        return 'bg-green-100 text-green-800';
                    case 'Selesai':
                        return 'bg-gray-100 text-gray-800';
                    case 'Dibatalkan':
                        return 'bg-red-100 text-red-800';
                    default:
                        return 'bg-gray-100 text-gray-800';
                }
            }

            // Improve UI responsiveness with keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !eventDetailModal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // Make print button more responsive
            printBtn.addEventListener('mouseover', function() {
                this.classList.add('shadow-md');
            });

            printBtn.addEventListener('mouseout', function() {
                this.classList.remove('shadow-md');
            });
        });
    </script>
@endsection
