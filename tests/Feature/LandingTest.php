<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    private function evento(string $titulo, $fecha, string $artista = 'Aterciopelados'): Evento
    {
        return Evento::create([
            'titulo' => $titulo, 'fecha' => $fecha, 'lugar' => 'Coliseo', 'ciudad' => 'Bogotá',
            'precio' => 100000, 'genero' => 'Rock',
            'artista_id' => Artista::firstOrCreate(['nombre' => $artista])->id,
        ]);
    }

    public function test_solo_muestra_eventos_que_no_han_pasado(): void
    {
        $this->evento('Gira futura', now()->addWeek());
        $this->evento('Gira pasada', now()->subWeek());

        $this->get('/')->assertOk()->assertSee('Gira futura')->assertDontSee('Gira pasada');
    }

    public function test_busca_por_titulo_o_por_artista_sin_traer_eventos_pasados(): void
    {
        $this->evento('Festival Rock al Parque', now()->addWeek(), 'Doctor Krápula');
        $this->evento('Noche de salsa', now()->addWeek(), 'Grupo Niche');
        $this->evento('Krápula 2020', now()->subYear(), 'Doctor Krápula');

        $this->get('/?q=rock')->assertSee('Festival Rock al Parque')->assertDontSee('Noche de salsa');
        $this->get('/?q=Niche')->assertSee('Noche de salsa')->assertDontSee('Festival Rock al Parque');
        $this->get('/?q=Krápula')->assertSee('Festival Rock al Parque')->assertDontSee('Krápula 2020');
    }

    public function test_porcentaje_y_guion_bajo_se_buscan_como_texto(): void
    {
        $this->evento('Descuento 50% Live', now()->addWeek());
        $this->evento('Concierto normal', now()->addWeek());

        $this->get('/?q=%25')->assertSee('Descuento 50% Live')->assertDontSee('Concierto normal');
        $this->get('/?q=_')->assertDontSee('Descuento 50% Live')->assertDontSee('Concierto normal')
            ->assertSee('No encontramos próximos eventos para esa búsqueda.');
    }
}
