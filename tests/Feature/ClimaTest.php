<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClimaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-15 10:00');
    }

    private function verEvento(string $fecha, string $ciudad = 'Bogotá')
    {
        $evento = Evento::create([
            'titulo' => 'Gira', 'fecha' => $fecha, 'lugar' => 'Movistar Arena', 'ciudad' => $ciudad,
            'precio' => 100000, 'genero' => 'Rock', 'artista_id' => Artista::create(['nombre' => 'Aterciopelados'])->id,
        ]);

        return $this->actingAs(User::factory()->create())->get("/eventos/{$evento->id}");
    }

    private function pronosticoPorHora(int $hora, float $temperatura, int $lluvia, int $codigo): array
    {
        $serie = fn ($valor, $resto) => array_replace(array_fill(0, 24, $resto), [$hora => $valor]);

        return [
            'hourly' => [
                'temperature_2m' => $serie($temperatura, 10.0),
                'precipitation_probability' => $serie($lluvia, 0),
                'weather_code' => $serie($codigo, 0),
            ],
            'daily' => ['temperature_2m_min' => [11.8], 'temperature_2m_max' => [22.4]],
        ];
    }

    public function test_muestra_el_pronostico_a_la_hora_del_evento(): void
    {
        Http::fake([
            'geocoding-api.open-meteo.com/*' => Http::response(['results' => [['name' => 'Bogotá', 'admin1' => 'Bogotá D.C.', 'latitude' => 4.61, 'longitude' => -74.08]]]),
            'api.open-meteo.com/*' => Http::response($this->pronosticoPorHora(20, 14.8, 31, 61)),
        ]);

        $this->verEvento('2026-11-18 20:00')
            ->assertOk()
            ->assertSee('15 °C')
            ->assertSee('a las 20:00')
            ->assertSee('Lluvia débil')
            ->assertSee('Mín. 12 °C · Máx. 22 °C')
            ->assertSee('Probabilidad de lluvia 31 %')
            ->assertSee('Pronóstico para Bogotá, Bogotá D.C.');

        Http::assertSent(fn ($r) => str_starts_with($r->url(), 'https://api.open-meteo.com/')
            && $r['start_date'] === '2026-11-18' && $r['latitude'] === 4.61);
    }

    public function test_evento_lejano_dice_desde_cuando_hay_pronostico_sin_consultar(): void
    {
        $this->verEvento('2027-01-10 20:00')->assertSee('El pronóstico estará disponible desde el 26/12/2026.');

        Http::assertNothingSent();
    }

    public function test_evento_pasado_no_consulta_el_clima(): void
    {
        $this->verEvento('2026-11-01 20:00')->assertSee('El evento ya pasó.');

        Http::assertNothingSent();
    }

    public function test_busca_la_ciudad_fuera_de_colombia_si_no_aparece(): void
    {
        Http::fake([
            'geocoding-api.open-meteo.com/*countryCode=CO*' => Http::response(['generationtime_ms' => 0.1]),
            'geocoding-api.open-meteo.com/*' => Http::response(['results' => [['name' => 'Madrid', 'admin1' => 'Madrid', 'latitude' => 40.4, 'longitude' => -3.7]]]),
            'api.open-meteo.com/*' => Http::response($this->pronosticoPorHora(21, 9.0, 0, 0)),
        ]);

        $this->verEvento('2026-11-20 21:00', 'Madrid')->assertSee('Despejado')->assertSee('Pronóstico para Madrid');
    }

    public function test_ciudad_inexistente_muestra_aviso(): void
    {
        Http::fake(['geocoding-api.open-meteo.com/*' => Http::response(['generationtime_ms' => 0.1])]);

        $this->verEvento('2026-11-20 21:00', 'Ciudad Inventada')->assertOk()->assertSee('No encontramos la ciudad «Ciudad Inventada».');
    }

    public function test_si_open_meteo_falla_la_pagina_igual_carga(): void
    {
        Http::fake(['*' => Http::response('error', 500)]);

        $this->verEvento('2026-11-20 21:00')->assertOk()->assertSee('No se pudo consultar el clima en este momento.');
    }

    /** Serie de MET Norway para un día: horas en UTC, cada hora, con una temperatura distinta a la hora indicada (hora local). */
    private function serieMet(string $dia, int $hora, float $temperatura, string $simbolo): array
    {
        $inicio = Carbon::parse("{$dia} 00:00", config('app.timezone'))->utc();

        return ['properties' => ['timeseries' => collect(range(0, 23))->map(fn ($h) => [
            'time' => $inicio->copy()->addHours($h)->format('Y-m-d\TH:i:s\Z'),
            'data' => [
                'instant' => ['details' => ['air_temperature' => $h === $hora ? $temperatura : 10.0 + $h / 2]],
                'next_1_hours' => ['summary' => ['symbol_code' => $h === $hora ? $simbolo : 'cloudy']],
            ],
        ])->all()]];
    }

    public function test_si_open_meteo_agota_el_limite_usa_met_norway(): void
    {
        Http::fake([
            'geocoding-api.open-meteo.com/*' => Http::response(['results' => [['name' => 'Bogotá', 'admin1' => 'Bogotá D.C.', 'latitude' => 4.609712, 'longitude' => -74.08175]]]),
            'api.open-meteo.com/*' => Http::response(['error' => true, 'reason' => 'Daily API request limit exceeded.'], 429),
            'api.met.no/*' => Http::response($this->serieMet('2026-11-18', 20, 16.6, 'lightrainshowers_night')),
        ]);

        $this->verEvento('2026-11-18 20:00')
            ->assertOk()
            ->assertSee('17 °C')
            ->assertSee('Chubascos')
            ->assertSee('Mín. 10 °C · Máx. 22 °C')
            ->assertDontSee('Probabilidad de lluvia')
            ->assertSee('Fuente: MET Norway');

        Http::assertSent(fn ($r) => str_starts_with($r->url(), 'https://api.met.no/')
            && $r['lat'] == 4.6097 && $r['lon'] == -74.0818
            && str_starts_with($r->header('User-Agent')[0], 'GestorConciertos/'));
    }

    public function test_tras_el_limite_no_vuelve_a_consultar_open_meteo_durante_una_hora(): void
    {
        Http::fake([
            'geocoding-api.open-meteo.com/*' => Http::response(['results' => [['name' => 'Bogotá', 'latitude' => 4.61, 'longitude' => -74.08]]]),
            'api.open-meteo.com/*' => Http::response(['error' => true], 429),
            'api.met.no/*' => Http::response($this->serieMet('2026-11-18', 20, 16.0, 'cloudy')),
        ]);

        $this->verEvento('2026-11-18 20:00')->assertSee('Fuente: MET Norway');
        $this->verEvento('2026-11-18 20:00')->assertSee('Fuente: MET Norway');

        Http::assertSentCount(3);
    }

    public function test_si_ambos_servicios_fallan_la_pagina_igual_carga(): void
    {
        Http::fake([
            'geocoding-api.open-meteo.com/*' => Http::response(['results' => [['name' => 'Bogotá', 'latitude' => 4.61, 'longitude' => -74.08]]]),
            'api.open-meteo.com/*' => Http::response(['error' => true], 429),
            'api.met.no/*' => Http::response('error', 503),
        ]);

        $this->verEvento('2026-11-18 20:00')->assertOk()->assertSee('No se pudo consultar el clima en este momento.');
    }
}
