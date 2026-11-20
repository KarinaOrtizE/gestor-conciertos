<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-15 10:00');
    }

    private function evento(string $fecha, string $genero, string $ciudad = 'Bogotá'): void
    {
        Evento::create([
            'titulo' => "Evento {$genero} {$fecha}", 'fecha' => $fecha, 'lugar' => 'Coliseo', 'ciudad' => $ciudad,
            'precio' => 100000, 'genero' => $genero,
            'artista_id' => Artista::firstOrCreate(['nombre' => 'Aterciopelados'])->id,
        ]);
    }

    public function test_invitado_es_enviado_al_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_calcula_totales_y_agrupa_por_genero_y_mes(): void
    {
        $this->evento('2026-10-01 20:00', 'Rock', 'Cali');      // pasado
        $this->evento('2026-11-20 20:00', 'Rock');
        $this->evento('2026-11-28 20:00', 'Salsa', 'Medellín');
        $this->evento('2027-01-10 20:00', 'Rock');
        $this->evento('2027-04-30 20:00', 'Jazz');
        $this->evento('2027-05-01 20:00', 'Pop');               // fuera de los 6 meses

        $vista = $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk();

        $vista->assertViewHas('totales', ['artistas' => 1, 'eventos' => 6, 'proximos' => 5, 'ciudades' => 3]);
        $this->assertSame(['Rock' => 3, 'Jazz' => 1, 'Pop' => 1, 'Salsa' => 1], $vista->viewData('porGenero')->map(fn ($n) => (int) $n)->all());
        $this->assertSame(
            ['Nov 2026' => 2, 'Dic 2026' => 0, 'Ene 2027' => 1, 'Feb 2027' => 0, 'Mar 2027' => 0, 'Abr 2027' => 1],
            $vista->viewData('porMes')->all()
        );
        $vista->assertSee('grafico-genero')->assertSee('grafico-mes')->assertSee('Ver como tabla');
    }

    public function test_sin_eventos_muestra_mensajes_en_lugar_de_graficos(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')
            ->assertSee('Todavía no hay eventos registrados.')
            ->assertSee('No hay eventos programados en los próximos 6 meses.')
            ->assertDontSee('<canvas', false);
    }

    public function test_muestra_los_proximos_conciertos_y_los_favoritos_del_usuario(): void
    {
        $usuario = User::factory()->create(['name' => 'Karina']);
        $artista = Artista::create(['nombre' => 'Bomba Estéreo']);
        $datos = ['lugar' => 'Estadio', 'ciudad' => 'Medellín', 'precio' => 90000, 'genero' => 'Electrónica', 'artista_id' => $artista->id];
        $proximo = Evento::create($datos + ['titulo' => 'Bomba en vivo', 'fecha' => now()->addWeek()]);
        Evento::create($datos + ['titulo' => 'Ya pasó', 'fecha' => now()->subWeek()]);
        $usuario->eventosFavoritos()->attach($proximo);

        $this->actingAs($usuario)->get('/dashboard')->assertOk()
            ->assertSee('Hola, Karina')
            ->assertSee('Tienes 1 evento próximo en favoritos.')
            ->assertSeeInOrder(['Próximos conciertos', 'Bomba en vivo'])
            ->assertDontSee('Ya pasó');
    }
}
