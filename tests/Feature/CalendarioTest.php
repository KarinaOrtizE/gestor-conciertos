<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioTest extends TestCase
{
    use RefreshDatabase;

    private function evento(string $titulo = 'Bomba en vivo', string $fecha = '2026-12-05 20:00'): Evento
    {
        return Evento::create([
            'titulo' => $titulo, 'fecha' => $fecha, 'lugar' => 'Estadio', 'ciudad' => 'Medellín',
            'precio' => 90000, 'genero' => 'Electrónica', 'artista_id' => Artista::firstOrCreate(['nombre' => 'Bomba Estéreo'])->id,
        ]);
    }

    public function test_el_calendario_y_sus_datos_requieren_sesion(): void
    {
        $evento = $this->evento();

        $this->get('/calendario')->assertRedirect('/login');
        $this->get('/calendario/datos')->assertRedirect('/login');
        $this->get("/eventos/{$evento->id}/ics")->assertRedirect('/login');
    }

    public function test_el_calendario_recibe_los_eventos_en_json(): void
    {
        $evento = $this->evento();

        $this->actingAs(User::factory()->create())->get('/calendario')->assertOk()->assertSee('fullcalendar', false);
        $this->actingAs(User::factory()->create())->getJson('/calendario/datos')
            ->assertOk()
            ->assertExactJson([[
                'id' => $evento->id,
                'title' => 'Bomba en vivo · Bomba Estéreo',
                'start' => $evento->fecha->toIso8601String(),
                'url' => route('eventos.show', $evento),
            ]]);
    }

    public function test_el_archivo_ics_trae_el_evento_en_hora_utc(): void
    {
        $evento = $this->evento('Gira, en vivo');

        $respuesta = $this->actingAs(User::factory()->create())->get("/eventos/{$evento->id}/ics")
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->assertHeader('Content-Disposition', "attachment; filename=\"evento-{$evento->id}.ics\"");

        $ics = $respuesta->getContent();
        // 20:00 en Bogotá (UTC-5) son la 01:00 UTC del día siguiente.
        $this->assertStringContainsString("DTSTART:20261206T010000Z\r\n", $ics);
        $this->assertStringContainsString("DTEND:20261206T040000Z\r\n", $ics);
        $this->assertStringContainsString('SUMMARY:Gira\, en vivo - Bomba Estéreo', $ics);
        $this->assertStringStartsWith("BEGIN:VCALENDAR\r\n", $ics);
    }
}
