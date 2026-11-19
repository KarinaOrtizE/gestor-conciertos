<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $evento->titulo }}</title>
    @include('pdf._estilos')
    <style>
        .ficha td { border: none; background: none !important; padding: 0; }
        .afiche { width: 230px; border-radius: 6px; }
        .dato { margin: 0 0 10px; font-size: 13px; }
        .etiqueta { display: block; font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .precio { font-size: 22px; font-weight: bold; color: #5b21b6; }
    </style>
</head>
<body>
    <h1>{{ $evento->titulo }}</h1>
    <p class="subtitulo">{{ $evento->artista->nombre }} · {{ $evento->genero }}</p>

    <table class="ficha">
        <tr>
            {{-- dompdf carga las imágenes desde el disco, no desde la URL pública --}}
            @if ($evento->afiche && is_file(storage_path('app/public/'.$evento->afiche)))
                <td style="width: 250px;">
                    <img class="afiche" src="{{ storage_path('app/public/'.$evento->afiche) }}" alt="Afiche">
                </td>
            @endif
            <td>
                <p class="dato"><span class="etiqueta">Fecha y hora</span>{{ $evento->fecha->format('d/m/Y H:i') }}</p>
                <p class="dato"><span class="etiqueta">Lugar</span>{{ $evento->lugar }}</p>
                <p class="dato"><span class="etiqueta">Ciudad</span>{{ $evento->ciudad }}</p>
                <p class="dato"><span class="etiqueta">Artista</span>{{ $evento->artista->nombre }}</p>
                <p class="dato"><span class="etiqueta">Precio</span><span class="precio">${{ number_format($evento->precio, 0, ',', '.') }} COP</span></p>
            </td>
        </tr>
    </table>

    <div class="pie">{{ config('app.name') }} · generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
