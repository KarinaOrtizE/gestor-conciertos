<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Calendario de eventos</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <div id="calendario"></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/es.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const calendario = new FullCalendar.Calendar(document.getElementById('calendario'), {
                    initialView: 'dayGridMonth',
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