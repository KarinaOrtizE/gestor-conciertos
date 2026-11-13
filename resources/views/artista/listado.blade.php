<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Artistas</h2>
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

                <div class="mb-4">
                    <a href="{{ route('artistas.create') }}"
                       class="px-4 py-2 text-white bg-purple-600 rounded hover:bg-purple-500">+ Nuevo artista</a>
                </div>

                <table id="tabla-artistas" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Géneros</th>
                            <th>Eventos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($artistas as $artista)
                            <tr>
                                <td>
                                    @if ($artista->imagen_url)
                                        <img src="{{ $artista->imagen_url }}" class="object-cover w-12 h-12 rounded-full" alt="">
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $artista->nombre }}</td>
                                <td>{{ $artista->generos }}</td>
                                <td>{{ $artista->eventos_count }}</td>
                                <td>
                                    <a href="{{ route('artistas.show', $artista) }}" class="text-blue-600 hover:underline">Ver</a> |
                                    <a href="{{ route('artistas.edit', $artista) }}" class="text-yellow-600 hover:underline">Editar</a> |
                                    <form action="{{ route('artistas.destroy', $artista) }}" method="POST" class="inline"
                                          onsubmit="return confirm('¿Eliminar a {{ $artista->nombre }}? También se borrarán sus eventos.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">Eliminar</button>
                                    </form>
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
                $('#tabla-artistas').DataTable({
                    language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },
                    columnDefs: [{ orderable: false, targets: [0, 4] }]
                });
            });
        </script>
    @endpush
</x-app-layout>