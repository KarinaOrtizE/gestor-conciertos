<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de eventos</title>
    @include('pdf._estilos')
</head>
<body>
    <h1>Reporte de eventos</h1>
    <p class="subtitulo">
        {{ $soloProximos ? 'Próximos eventos' : 'Todos los eventos' }} · {{ $eventos->count() }} en total ·
        generado el {{ now()->format('d/m/Y H:i') }}
    </p>

    @if ($eventos->isEmpty())
        <p>No hay eventos para mostrar.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Evento</th>
                    <th>Artista</th>
                    <th>Lugar</th>
                    <th>Ciudad</th>
                    <th>Género</th>
                    <th class="derecha">Precio</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($eventos as $evento)
                    <tr>
                        <td>{{ $evento->fecha->format('d/m/Y H:i') }}</td>
                        <td>{{ $evento->titulo }}</td>
                        <td>{{ $evento->artista->nombre }}</td>
                        <td>{{ $evento->lugar }}</td>
                        <td>{{ $evento->ciudad }}</td>
                        <td>{{ $evento->genero }}</td>
                        <td class="derecha">${{ number_format($evento->precio, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="pie">{{ config('app.name') }}</div>
</body>
</html>
