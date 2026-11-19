<?php

namespace App\Services;

use App\Models\Evento;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pronóstico del clima para la ciudad y la hora de un evento, con Open-Meteo y MET Norway de respaldo (gratis y sin clave).
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

    /** Símbolos de MET Norway (sin el sufijo _day/_night), en las mismas descripciones cortas. */
    private const SIMBOLOS = [
        'clearsky' => ['Despejado', '☀️'],
        'fair' => ['Mayormente despejado', '🌤️'],
        'partlycloudy' => ['Parcialmente nublado', '⛅'],
        'cloudy' => ['Nublado', '☁️'],
        'fog' => ['Niebla', '🌫️'],
        'lightrain' => ['Lluvia débil', '🌧️'],
        'rain' => ['Lluvia', '🌧️'],
        'heavyrain' => ['Lluvia fuerte', '🌧️'],
        'lightrainshowers' => ['Chubascos', '🌦️'],
        'rainshowers' => ['Chubascos', '🌦️'],
        'heavyrainshowers' => ['Chubascos fuertes', '⛈️'],
    ];

    /** MET Norway exige que cada aplicación se identifique en el User-Agent. */
    private const AGENTE = 'GestorConciertos/1.0 (+https://github.com/KarinaOrtizE/gestor-conciertos)';

    /**
     * @return array{estado: string, mensaje?: string, ciudad?: string, temperatura?: float, minima?: float, maxima?: float, lluvia?: ?int, descripcion?: string, icono?: string, fuente?: string}
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
        } catch (ConnectionException|RequestException $e) {
            report($e);

            return ['estado' => 'error', 'mensaje' => 'No se pudo consultar el clima en este momento.'];
        }

        if (! $lugar) {
            return ['estado' => 'sin_ciudad', 'mensaje' => "No encontramos la ciudad «{$evento->ciudad}»."];
        }

        // Open-Meteo limita las consultas por IP y en servidores compartidos se agota; MET Norway queda de respaldo.
        $datos = $this->desdeOpenMeteo($lugar, $evento) ?? $this->desdeMetNorway($lugar, $evento);

        if (! $datos) {
            return ['estado' => 'error', 'mensaje' => 'No se pudo consultar el clima en este momento.'];
        }

        return [
            'estado' => 'ok',
            'ciudad' => $lugar['name'].(isset($lugar['admin1']) && $lugar['admin1'] !== $lugar['name'] ? ', '.$lugar['admin1'] : ''),
            ...$datos,
        ];
    }

    private function desdeOpenMeteo(array $lugar, Evento $evento): ?array
    {
        if (Cache::has('clima.open-meteo.limitado')) {
            return null;
        }

        $dia = $evento->fecha->format('Y-m-d');

        try {
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
        } catch (RequestException $e) {
            if ($e->response->status() === 429) {
                // Límite diario agotado: no se vuelve a intentar durante una hora.
                Cache::put('clima.open-meteo.limitado', true, now()->addHour());
                Log::warning('Open-Meteo agotó el límite diario; se usa MET Norway.');
            } else {
                report($e);
            }

            return null;
        } catch (ConnectionException $e) {
            report($e);

            return null;
        }

        // Los datos por hora vienen de 00:00 a 23:00; se toma la hora en que empieza el evento.
        $hora = $evento->fecha->hour;
        $codigo = $datos['hourly']['weather_code'][$hora] ?? null;
        [$descripcion, $icono] = self::CONDICIONES[$codigo] ?? ['Sin datos', '🌡️'];

        return [
            'temperatura' => $datos['hourly']['temperature_2m'][$hora] ?? null,
            'lluvia' => $datos['hourly']['precipitation_probability'][$hora] ?? null,
            'minima' => $datos['daily']['temperature_2m_min'][0] ?? null,
            'maxima' => $datos['daily']['temperature_2m_max'][0] ?? null,
            'descripcion' => $descripcion,
            'icono' => $icono,
            'fuente' => 'Open-Meteo',
        ];
    }

    private function desdeMetNorway(array $lugar, Evento $evento): ?array
    {
        // MET pide coordenadas con máximo 4 decimales para aprovechar su caché.
        $latitud = round($lugar['latitude'], 4);
        $longitud = round($lugar['longitude'], 4);

        try {
            $series = Cache::remember("clima.met.{$latitud},{$longitud}", now()->addHour(), fn () => Http::timeout(5)
                ->withUserAgent(self::AGENTE)
                ->get('https://api.met.no/weatherapi/locationforecast/2.0/compact', ['lat' => $latitud, 'lon' => $longitud])
                ->throw()
                ->json('properties.timeseries', []));
        } catch (ConnectionException|RequestException $e) {
            report($e);

            return null;
        }

        // MET entrega horas en UTC: cada hora por los primeros días y luego cada 6 horas.
        $delDia = collect($series)
            ->map(fn (array $punto) => ['hora' => Carbon::parse($punto['time'])->setTimezone(config('app.timezone')), 'datos' => $punto['data']])
            ->filter(fn (array $punto) => $punto['hora']->isSameDay($evento->fecha));

        $cercano = $delDia->sortBy(fn (array $punto) => abs($punto['hora']->diffInMinutes($evento->fecha)))->first();

        if (! $cercano || abs($cercano['hora']->diffInMinutes($evento->fecha)) > 180) {
            return null;
        }

        $temperaturas = $delDia->map(fn (array $punto) => $punto['datos']['instant']['details']['air_temperature'] ?? null)->filter(fn ($t) => $t !== null);
        $simbolo = $cercano['datos']['next_1_hours']['summary']['symbol_code'] ?? $cercano['datos']['next_6_hours']['summary']['symbol_code'] ?? '';
        [$descripcion, $icono] = self::describirSimbolo($simbolo);

        return [
            'temperatura' => $cercano['datos']['instant']['details']['air_temperature'] ?? null,
            'lluvia' => null,
            'minima' => $temperaturas->min(),
            'maxima' => $temperaturas->max(),
            'descripcion' => $descripcion,
            'icono' => $icono,
            'fuente' => 'MET Norway',
        ];
    }

    private static function describirSimbolo(string $simbolo): array
    {
        $base = preg_replace('/_(day|night|polartwilight)$/', '', $simbolo);

        return self::SIMBOLOS[$base] ?? match (true) {
            str_contains($base, 'thunder') => ['Tormenta', '⛈️'],
            str_contains($base, 'sleet') => ['Aguanieve', '🌨️'],
            str_contains($base, 'snow') => ['Nieve', '🌨️'],
            str_contains($base, 'rain') => ['Lluvia', '🌧️'],
            default => ['Sin datos', '🌡️'],
        };
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
