<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DocumentoPdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReporteController extends Controller
{
    public function eventos(Request $request): Response
    {
        $soloProximos = $request->boolean('proximos');

        $eventos = Evento::with('artista')
            ->when($soloProximos, fn ($query) => $query->where('fecha', '>=', now()))
            ->orderBy('fecha')
            ->get();

        return $this->pdf('pdf.eventos', compact('eventos', 'soloProximos'))
            ->setPaper('a4', 'landscape')
            ->download('reporte-eventos-'.now()->format('Y-m-d').'.pdf');
    }

    public function evento(Evento $evento): Response
    {
        $evento->load('artista');

        return $this->pdf('pdf.evento', compact('evento'))
            ->download('evento-'.$evento->id.'.pdf');
    }

    private function pdf(string $vista, array $datos): DocumentoPdf
    {
        // Solo se incrustan los caracteres usados de la fuente; sin esto cada PDF pesa casi 1 MB.
        return Pdf::setOption('isFontSubsettingEnabled', true)->loadView($vista, $datos);
    }
}
