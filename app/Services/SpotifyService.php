<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SpotifyService
{
    public function configurado(): bool
    {
        return filled(config('services.spotify.client_id')) && filled(config('services.spotify.client_secret'));
    }

    /**
     * Busca artistas por nombre y devuelve los datos que usa el formulario.
     *
     * @return array<int, array{spotify_id: string, nombre: string, imagen_url: ?string, generos: string, url: ?string}>
     */
    public function buscarArtistas(string $nombre, int $limite = 5): array
    {
        try {
            $items = Http::withToken($this->token())
                ->timeout(10)
                ->get('https://api.spotify.com/v1/search', ['q' => $nombre, 'type' => 'artist', 'limit' => $limite])
                ->throw()
                ->json('artists.items', []);
        } catch (ConnectionException|RequestException $e) {
            throw new RuntimeException('No se pudo consultar Spotify.', previous: $e);
        }

        return collect($items)->map(fn (array $artista) => [
            'spotify_id' => $artista['id'],
            'nombre' => $artista['name'],
            // Spotify ordena las imágenes de mayor a menor; se usa la primera.
            'imagen_url' => $artista['images'][0]['url'] ?? null,
            'generos' => implode(', ', array_map('ucfirst', $artista['genres'] ?? [])),
            'url' => $artista['external_urls']['spotify'] ?? null,
        ])->all();
    }

    /** Token de aplicación (Client Credentials). Se guarda en caché hasta poco antes de vencer. */
    private function token(): string
    {
        if (! $this->configurado()) {
            throw new RuntimeException('Spotify no está configurado.');
        }

        $token = Cache::get('spotify.token');

        if ($token) {
            return $token;
        }

        try {
            $respuesta = Http::asForm()
                ->withBasicAuth(config('services.spotify.client_id'), config('services.spotify.client_secret'))
                ->timeout(10)
                ->post('https://accounts.spotify.com/api/token', ['grant_type' => 'client_credentials'])
                ->throw();
        } catch (ConnectionException|RequestException $e) {
            throw new RuntimeException('No se pudo autenticar con Spotify. Revisa las credenciales.', previous: $e);
        }

        Cache::put('spotify.token', $respuesta->json('access_token'), now()->addSeconds($respuesta->json('expires_in', 3600) - 60));

        return $respuesta->json('access_token');
    }
}
