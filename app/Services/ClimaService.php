<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Pronóstico del clima para la ciudad y la hora de un evento, con Open-Meteo (gratis y sin clave).
 */
class ClimaService
{
    /** Open-Meteo pronostica hasta 16 días adelante; se deja un día de margen. */
    public const DIAS_DE_PRONOSTICO = 15;

    /** Códigos WMO que usa Open-Meteo, agrupados en descripciones cortas. */
    private const CONDICIONES = [
        0 => ['Despejado', '☀️'],
        1 => ['Mayormente despejado', '🌤️'],
        2 => ['Parcialmente nublado', '⛅'],
        3 => ['Nublado', '☁️'],
        45 => ['Niebla', '🌫️'], 48 => ['Niebla', '🌫️'],
        51 => ['Llovizna', '🌦️'], 53 => ['Llovizna', '🌦️'], 55 => ['Llovizna intensa', '🌦️'],
        56 => ['Llovizna helada', '🌧️'], 57 => ['Llovizna helada', '🌧️'],
        61 => ['Lluvia débil', '🌧️'], 63 => ['Lluvia', '🌧️'], 65 => ['Lluvia fuerte', '🌧️'],
        66 => ['Lluvia helada', '🌧️'], 67 => ['Lluvia helada', '🌧️'],
        71 => ['Nieve', '🌨️'], 73 => ['Nieve', '🌨️'], 75 => ['Nieve fuerte', '🌨️'], 77 => ['Granizo fino', '🌨️'],
        80 => ['Chubascos', '🌦️'], 81 => ['Chubascos', '🌦️'], 82 => ['Chubascos fuertes', '⛈️'],
        85 => ['Chubascos de nieve', '🌨️'], 86 => ['Chubascos de nieve', '🌨️'],
        95 => ['Tormenta', '⛈️'], 96 => ['Tormenta con granizo', '⛈️'], 99 => ['Tormenta con granizo', '⛈️'],
    ];

    /**
     * @return array{estado: string, mensaje?: string, ciudad?: string, temperatura?: float, minima?: float, maxima?: float, lluvia?: ?int, descripcion?: string, icono?: string}
     */
    public function pronostico(Evento $evento): array
    {
        if ($evento->fecha->isPast()) {
            return ['estado' => 'pasado', 'mensaje' => 'El evento ya pasó.'];
        }

        if ($evento->fecha->copy()->startOfDay()->gt(now()->startOfDay()->addDays(self::DIAS_DE_PRONOSTICO))) {
            $desde = $evento->fecha->copy()->subDays(self::DIAS_DE_PRONOSTICO)->format('d/m/Y');

            return ['estado' => 'lejano', 'mensaje' => "El pronóstico estará disponible desde el {$desde}."];
        }

        try {
            $lugar = $this->ubicar($evento->ciudad);

            if (! $lugar) {
                return ['estado' => 'sin_ciudad', 'mensaje' => "No encontramos la ciudad «{$evento->ciudad}»."];
            }

            $dia = $evento->fecha->format('Y-m-d');
            $datos = Cache::remember("clima.{$lugar['latitude']},{$lugar['longitude']}.{$dia}", now()->addHour(), fn () => Http::timeout(5)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lugar['latitude'],
                    'longitude' => $lugar['longitude'],
                    'hourly' => 'temperature_2m,precipitation_probability,weather_code',
                    'daily' => 'temperature_2m_max,temperature_2m_min',
                    'timezone' => config('app.timezone'),
                    'start_date' => $dia,
                    'end_date' => $dia,
                ])->throw()->json());
        } catch (ConnectionException|RequestException $e) {
            report($e);

            return ['estado' => 'error', 'mensaje' => 'No se pudo consultar el clima en este momento.'];
        }

        // Los datos por hora vienen de 00:00 a 23:00; se toma la hora en que empieza el evento.
        $hora = $evento->fecha->hour;
        $codigo = $datos['hourly']['weather_code'][$hora] ?? null;
        [$descripcion, $icono] = self::CONDICIONES[$codigo] ?? ['Sin datos', '🌡️'];

        return [
            'estado' => 'ok',
            'ciudad' => $lugar['name'].(isset($lugar['admin1']) && $lugar['admin1'] !== $lugar['name'] ? ', '.$lugar['admin1'] : ''),
            'temperatura' => $datos['hourly']['temperature_2m'][$hora] ?? null,
            'lluvia' => $datos['hourly']['precipitation_probability'][$hora] ?? null,
            'minima' => $datos['daily']['temperature_2m_min'][0] ?? null,
            'maxima' => $datos['daily']['temperature_2m_max'][0] ?? null,
            'descripcion' => $descripcion,
            'icono' => $icono,
        ];
    }

    /** Coordenadas de la ciudad: primero se busca en Colombia y, si no aparece, en cualquier país. */
    private function ubicar(string $ciudad): ?array
    {
        return Cache::remember('clima.ciudad.'.mb_strtolower($ciudad), now()->addDays(30), function () use ($ciudad) {
            foreach (['CO', null] as $pais) {
                $resultado = Http::timeout(5)->get('https://geocoding-api.open-meteo.com/v1/search', array_filter([
                    'name' => $ciudad, 'count' => 1, 'language' => 'es', 'format' => 'json', 'countryCode' => $pais,
                ]))->throw()->json('results.0');

                if ($resultado) {
                    return $resultado;
                }
            }

            return null;
        });
    }
}
