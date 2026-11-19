<?php

namespace App\Http\Controllers;

use App\Services\SpotifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SpotifyController extends Controller
{
    public function buscar(Request $request, SpotifyService $spotify): JsonResponse
    {
        $datos = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);

        try {
            return response()->json(['artistas' => $spotify->buscarArtistas($datos['q'])]);
        } catch (RuntimeException $e) {
            report($e);

            return response()->json(['mensaje' => $e->getMessage()], 503);
        }
    }
}
