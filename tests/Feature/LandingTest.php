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

    public function test_filtra_por_genero_y_ignora_generos_que_no_existen(): void
    {
        $this->evento('Festival Rock al Parque', now()->addWeek());
        $this->evento('Noche de salsa', now()->addWeeks(2), 'Grupo Niche')->update(['genero' => 'Salsa']);

        $html = $this->get('/?genero=Salsa')->assertOk()->assertSee('Noche de salsa')->assertDontSee('Festival Rock al Parque')->getContent();
        $this->assertMatchesRegularExpression('/aria-current="true"[^>]*>Salsa<\/a>/', $html);
        $this->get('/?genero=Inventado')->assertOk()->assertSee('Noche de salsa')->assertSee('Festival Rock al Parque');
    }

    public function test_muestra_cifras_y_artistas_en_cartelera_solo_con_eventos_proximos(): void
    {
        $this->evento('Gira 1', now()->addWeek());
        $this->evento('Gira 2', now()->addWeeks(2));
        $this->evento('Bailable', now()->addWeek(), 'Grupo Niche');
        $this->evento('Recuerdo', now()->subWeek(), 'Banda del pasado');

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['3', 'Próximos conciertos', '2', 'Artistas', '1', 'Ciudades'])
            ->assertSeeInOrder(['Artistas en cartelera', 'Aterciopelados', '2 conciertos', 'Grupo Niche', '1 concierto'])
            ->assertDontSee('Banda del pasado');
    }
}
