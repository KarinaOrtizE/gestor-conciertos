<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Calendario de eventos</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-4 bg-white shadow-sm sm:p-6 sm:rounded-2xl ring-1 ring-gray-100">
                <div id="calendario"></div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Botones de FullCalendar con el morado del proyecto */
            #calendario {
                --fc-button-bg-color: #9333ea;
                --fc-button-border-color: #9333ea;
                --fc-button-hover-bg-color: #7e22ce;
                --fc-button-hover-border-color: #7e22ce;
                --fc-button-active-bg-color: #6b21a8;
                --fc-button-active-border-color: #6b21a8;
                --fc-today-bg-color: #faf5ff;
            }
            #calendario .fc-toolbar { flex-wrap: wrap; gap: .5rem; }
            #calendario .fc-toolbar-title { font-size: 1.25rem; font-weight: 700; }
            #calendario .fc-toolbar-title::first-letter { text-transform: uppercase; }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/es.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const calendario = new FullCalendar.Calendar(document.getElementById('calendario'), {
                    // En pantallas pequeñas la lista se lee mejor que la cuadrícula del mes.
                    initialView: window.innerWidth < 640 ? 'listMonth' : 'dayGridMonth',
                    locale: 'es',
                    height: 'auto',
                    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
                    buttonText: { today: 'Hoy', month: 'Mes', list: 'Lista' },
                    events: '{{ route('calendario.datos') }}',
                    eventColor: '#9333ea',
                });
                calendario.render();
            });
        </script>
    @endpush
</x-app-layout>