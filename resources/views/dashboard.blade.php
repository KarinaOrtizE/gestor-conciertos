<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $tarjetas = [
            ['Artistas', $totales['artistas']],
            ['Eventos registrados', $totales['eventos']],
            ['Próximos eventos', $totales['proximos']],
            ['Ciudades', $totales['ciudades']],
        ];
        $graficos = [
            ['id' => 'grafico-genero', 'titulo' => 'Eventos por género', 'columna' => 'Género', 'datos' => $porGenero, 'vacio' => 'Todavía no hay eventos registrados.'],
            ['id' => 'grafico-mes', 'titulo' => 'Eventos por mes', 'subtitulo' => 'Próximos 6 meses', 'columna' => 'Mes', 'datos' => $porMes, 'vacio' => 'No hay eventos programados en los próximos 6 meses.'],
        ];
    @endphp

    <div class="py-8">
        <div class="px-4 mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <p class="text-gray-600">Hola, {{ Auth::user()->name }}. Este es el resumen de los conciertos.</p>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($tarjetas as [$etiqueta, $valor])
                    <div class="p-5 bg-white rounded-lg shadow-sm">
                        <p class="text-sm text-gray-500">{{ $etiqueta }}</p>
                        <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($valor, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($graficos as $grafico)
                    <section class="p-6 bg-white rounded-lg shadow-sm" aria-labelledby="{{ $grafico['id'] }}-titulo">
                        <h3 id="{{ $grafico['id'] }}-titulo" class="font-semibold text-gray-900">{{ $grafico['titulo'] }}</h3>
                        @isset($grafico['subtitulo'])
                            <p class="text-sm text-gray-500">{{ $grafico['subtitulo'] }}</p>
                        @endisset

                        @if ($grafico['datos']->sum() === 0)
                            <p class="py-16 text-center text-gray-500">{{ $grafico['vacio'] }}</p>
                        @else
                            <div class="relative mt-4 h-72">
                                <canvas id="{{ $grafico['id'] }}" role="img"
                                        aria-label="{{ $grafico['titulo'] }}: {{ $grafico['datos']->map(fn ($n, $k) => "$k $n")->join(', ') }}"></canvas>
                            </div>

                            <details class="mt-4 text-sm text-gray-600">
                                <summary class="cursor-pointer hover:text-gray-900">Ver como tabla</summary>
                                <table class="w-full mt-2">
                                    <thead>
                                        <tr class="text-left text-gray-500 border-b">
                                            <th class="py-1 font-medium">{{ $grafico['columna'] }}</th>
                                            <th class="py-1 font-medium text-right">Eventos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($grafico['datos'] as $categoria => $total)
                                            <tr class="border-b border-gray-100">
                                                <td class="py-1">{{ $categoria }}</td>
                                                <td class="py-1 text-right">{{ $total }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </details>
                        @endif
                    </section>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
        <script>
            (() => {
                const porGenero = {{ Js::from($porGenero) }};
                const porMes = {{ Js::from($porMes) }};

                Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
                Chart.defaults.color = '#4b5563';

                // Barras delgadas de un solo color, con la punta redondeada y la base recta.
                const opciones = (horizontal) => ({
                    indexAxis: horizontal ? 'y' : 'x',
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: { label: (item) => `${item.raw} ${item.raw === 1 ? 'evento' : 'eventos'}` },
                        },
                    },
                    scales: {
                        [horizontal ? 'x' : 'y']: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: '#e5e7eb' },
                            border: { display: false },
                        },
                        [horizontal ? 'y' : 'x']: {
                            grid: { display: false },
                        },
                    },
                });

                const barras = (datos) => ({
                    labels: Object.keys(datos),
                    datasets: [{
                        data: Object.values(datos),
                        backgroundColor: '#7c3aed',
                        hoverBackgroundColor: '#6d28d9',
                        borderRadius: 4,
                        maxBarThickness: 24,
                    }],
                });

                [['grafico-genero', porGenero, true], ['grafico-mes', porMes, false]].forEach(([id, datos, horizontal]) => {
                    const lienzo = document.getElementById(id);
                    if (lienzo) {
                        new Chart(lienzo, { type: 'bar', data: barras(datos), options: opciones(horizontal) });
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>
