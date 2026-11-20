<?php

namespace App\Http\Controllers;

use App\Models\Artista;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $totales = [
            'artistas' => Artista::count(),
            'eventos' => Evento::count(),
            'proximos' => Evento::where('fecha', '>=', now())->count(),
            'ciudades' => Evento::distinct()->count('ciudad'),
        ];

        $porGenero = Evento::selectRaw('genero, count(*) as total')
            ->groupBy('genero')
            ->orderByDesc('total')
            ->orderBy('genero')
            ->pluck('total', 'genero');

        // Próximos 6 meses empezando por el actual; los meses sin eventos quedan en cero.
        $inicio = now()->startOfMonth();
        $fechas = Evento::whereBetween('fecha', [$inicio, $inicio->copy()->addMonths(6)->subSecond()])->pluck('fecha');
        $porMes = collect(range(0, 5))->mapWithKeys(function (int $i) use ($inicio, $fechas) {
            $mes = $inicio->copy()->addMonths($i);
            $etiqueta = ucfirst(str_replace('.', '', $mes->locale(app()->getLocale())->isoFormat('MMM YYYY')));

            return [$etiqueta => $fechas->filter(fn ($fecha) => $fecha->isSameMonth($mes))->count()];
        });

        $proximos = Evento::with('artista')->where('fecha', '>=', now())->orderBy('fecha')->take(4)->get();
        $misFavoritos = $request->user()->eventosFavoritos()->where('fecha', '>=', now())->count();

        return view('dashboard', compact('totales', 'porGenero', 'porMes', 'proximos', 'misFavoritos'));
    }
}
