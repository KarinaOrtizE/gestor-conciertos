<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Eventos</h2>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    @endpush

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">

                @if (session('ok'))
                    <div class="p-3 mb-4 text-green-800 bg-green-100 rounded">{{ session('ok') }}</div>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        @can('administrar')
                            <a href="{{ route('eventos.create') }}"
                               class="inline-block px-4 py-2 text-white bg-purple-600 rounded hover:bg-purple-500">+ Nuevo evento</a>
                        @endcan
                    </div>
                    <div class="flex flex-wrap gap-2 text-sm">
                        <a href="{{ route('reportes.eventos') }}"
                           class="px-3 py-2 text-gray-700 border border-gray-300 rounded hover:bg-gray-50">Descargar PDF</a>
                        <a href="{{ route('reportes.eventos', ['proximos' => 1]) }}"
                           class="px-3 py-2 text-gray-700 border border-gray-300 rounded hover:bg-gray-50">PDF de próximos</a>
                    </div>
                </div>

                <table id="tabla-eventos" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Afiche</th>
                            <th>Título</th>
                            <th>Artista</th>
                            <th>Fecha</th>
                            <th>Ciudad</th>
                            <th>Género</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($eventos as $evento)
                            <tr>
                                <td>
                                    @if ($evento->afiche)
                                        <img src="{{ asset('storage/'.$evento->afiche) }}" class="object-cover w-12 h-16 rounded" alt="">
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $evento->titulo }}</td>
                                <td>{{ $evento->artista->nombre }}</td>
                                <td data-order="{{ $evento->fecha->timestamp }}">{{ $evento->fecha->format('d/m/Y H:i') }}</td>
                                <td>{{ $evento->ciudad }}</td>
                                <td>{{ $evento->genero }}</td>
                                <td data-order="{{ $evento->precio }}">${{ number_format($evento->precio, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap">
                                    <x-boton-favorito :evento="$evento" :marcado="$favoritos->contains($evento->id)" compacto class="align-middle" />
                                    <a href="{{ route('eventos.show', $evento) }}" class="text-blue-600 hover:underline">Ver</a>
                                    @can('administrar')
                                        | <a href="{{ route('eventos.edit', $evento) }}" class="text-yellow-600 hover:underline">Editar</a> |
                                        <form action="{{ route('eventos.destroy', $evento) }}" method="POST" class="inline"
                                              data-confirmar="¿Eliminar el evento {{ $evento->titulo }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:underline">Eliminar</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
        <script>
            $(function () {
                $('#tabla-eventos').DataTable({
                    language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },
                    columnDefs: [{ orderable: false, targets: [0, 7] }],
                    // Ordena por fecha y deja que la tabla se desplace de lado en pantallas pequeñas.
                    order: [[3, 'asc']],
                    scrollX: true
                });
            });
        </script>
    @endpush
</x-app-layout>