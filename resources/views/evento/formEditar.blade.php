<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Editar evento</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('eventos.update', $evento) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('evento._campos')
                    <div class="flex gap-3">
                        <x-primary-button>Actualizar</x-primary-button>
                        <a href="{{ route('eventos.index') }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>