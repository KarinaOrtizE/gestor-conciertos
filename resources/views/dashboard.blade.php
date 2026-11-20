<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $tarjetas = [
            ['Artistas', $totales['artistas'], 'microfono', 'from-purple-500 to-fuchsia-500'],
            ['Eventos registrados', $totales['eventos'], 'calendario', 'from-fuchsia-500 to-pink-500'],
            ['Próximos eventos', $totales['proximos'], 'reloj', 'from-amber-400 to-orange-500'],
            ['Ciudades', $totales['ciudades'], 'mundo', 'from-sky-400 to-indigo-500'],
        ];
        $graficos = [
            ['id' => 'grafico-genero', 'titulo' => 'Eventos por género', 'columna' => 'Género', 'datos' => $porGenero, 'vacio' => 'Todavía no hay eventos registrados.'],
            ['id' => 'grafico-mes', 'titulo' => 'Eventos por mes', 'subtitulo' => 'Próximos 6 meses', 'columna' => 'Mes', 'datos' => $porMes, 'vacio' => 'No hay eventos programados en los próximos 6 meses.'],
        ];
    @endphp

    <div class="py-8">
        <div class="px-4 mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">
            <section class="relative overflow-hidden text-white shadow-lg rounded-2xl bg-gradient-to-r from-fuchsia-600 via-purple-600 to-indigo-600">
                <div class="absolute rounded-full -top-16 -right-10 w-72 h-72 bg-white/10 blur-2xl"></div>
                <div class="relative flex flex-wrap items-center justify-between gap-6 p-6 sm:p-8">
                    <div>
                        <p class="text-sm font-medium text-purple-100">{{ Auth::user()->esAdmin() ? 'Administrador' : 'Usuario' }}</p>
                        <h3 class="mt-1 text-2xl font-extrabold sm:text-3xl">Hola, {{ Auth::user()->name }} 👋</h3>
                        <p class="mt-2 text-purple-100">Este es el resumen de los conciertos.
                            @if ($misFavoritos > 0)
                                Tienes {{ $misFavoritos }} {{ $misFavoritos === 1 ? 'evento próximo' : 'eventos próximos' }} en favoritos.
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @can('administrar')
                            <a href="{{ route('eventos.create') }}" class="inline-flex items-center gap-2 px-4 py-2 font-semibold text-purple-700 bg-white rounded-full hover:bg-purple-50">
                                <x-icono nombre="mas" class="w-4 h-4" /> Nuevo evento
                            </a>
                        @endcan
                        <a href="{{ route('eventos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 font-semibold rounded-full bg-white/15 ring-1 ring-white/30 hover:bg-white/25">
                            <x-icono nombre="calendario" class="w-4 h-4" /> Eventos
                        </a>
                        <a href="{{ route('favoritos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 font-semibold rounded-full bg-white/15 ring-1 ring-white/30 hover:bg-white/25">
                            <x-icono nombre="corazon" class="w-4 h-4" /> Favoritos
                        </a>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($tarjetas as [$etiqueta, $valor, $icono, $degradado])
                    <div class="flex items-center gap-4 p-5 bg-white shadow-sm rounded-2xl ring-1 ring-gray-100">
                        <span class="hidden sm:flex items-center justify-center w-12 h-12 text-white rounded-xl shadow bg-gradient-to-br {{ $degradado }}">
                            <x-icono :nombre="$icono" class="w-6 h-6" />
                        </span>
                        <div>
                            <p class="text-sm text-gray-500">{{ $etiqueta }}</p>
                            <p class="text-3xl font-extrabold text-gray-900">{{ number_format($valor, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($proximos->isNotEmpty())
                <section class="p-6 bg-white shadow-sm rounded-2xl ring-1 ring-gray-100" aria-labelledby="titulo-proximos">
                    <div class="flex items-center justify-between gap-4">
                        <h3 id="titulo-proximos" class="font-semibold text-gray-900">Próximos conciertos</h3>
                        <a href="{{ route('eventos.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-purple-700 hover:text-purple-900">
                            Ver todos <x-icono nombre="flecha" class="w-4 h-4" />
                        </a>
                    </div>
                    <div class="grid gap-4 mt-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($proximos as $evento)
                            <a href="{{ route('eventos.show', $evento) }}" class="flex gap-3 p-3 transition rounded-xl ring-1 ring-gray-100 hover:ring-purple-300 hover:bg-purple-50/50">
                                <x-portada-evento :evento="$evento" class="w-16 h-20 rounded-lg shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase text-fuchsia-600">{{ str_replace('.', '', $evento->fecha->locale(app()->getLocale())->isoFormat('D MMM · HH:mm')) }}</p>
                                    <p class="font-semibold text-gray-900 truncate">{{ $evento->titulo }}</p>
                                    <p class="text-sm text-gray-500 truncate">{{ $evento->artista->nombre }} · {{ $evento->ciudad }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($graficos as $grafico)
                    <section class="p-6 bg-white shadow-sm rounded-2xl ring-1 ring-gray-100" aria-labelledby="{{ $grafico['id'] }}-titulo">
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
