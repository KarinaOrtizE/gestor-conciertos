<?php

namespace App\Http\Controllers;

use App\Models\Evento;

class CalendarioController extends Controller
{
    public function index()
    {
        return view('calendario.index');
    }

    // Los eventos en JSON para el calendario
    public function datos()
    {
        $eventos = Evento::with('artista')->get()->map(fn ($e) => [
            'id'    => $e->id,
            'title' => $e->titulo . ' · ' . $e->artista->nombre,
            'start' => $e->fecha->toIso8601String(),
            'url'   => route('eventos.show', $e),
        ]);

        return response()->json($eventos);
    }

    // Descarga el archivo .ics de un evento
    public function ics(Evento $evento)
    {
        $evento->load('artista');

        $inicio = $evento->fecha->copy()->utc();
        $fin = $inicio->copy()->addHours(3);

        $esc = fn ($t) => str_replace(['\\', ';', ',', "\n"], ['\\\\', '\;', '\,', '\n'], $t);

        $lineas = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Gestor Conciertos//ES',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:evento-' . $evento->id . '@gestor-conciertos',
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $inicio->format('Ymd\THis\Z'),
            'DTEND:' . $fin->format('Ymd\THis\Z'),
            'SUMMARY:' . $esc($evento->titulo . ' - ' . $evento->artista->nombre),
            'LOCATION:' . $esc($evento->lugar . ', ' . $evento->ciudad),
            'DESCRIPTION:' . $esc('Género: ' . $evento->genero . '. Precio: $' . number_format($evento->precio, 0, ',', '.')),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lineas) . "\r\n", 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="evento-' . $evento->id . '.ics"',
        ]);
    }
}