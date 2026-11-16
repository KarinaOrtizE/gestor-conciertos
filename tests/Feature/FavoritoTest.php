<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoritoTest extends TestCase
{
    use RefreshDatabase;

    private function evento(string $titulo = 'Gira', ?\DateTimeInterface $fecha = null): Evento
    {
        $artista = Artista::firstOrCreate(['nombre' => 'Aterciopelados']);

        return Evento::create([
            'titulo' => $titulo, 'fecha' => $fecha ?? now()->addMonth(), 'lugar' => 'Coliseo', 'ciudad' => 'Bogotá',
            'precio' => 100000, 'genero' => 'Rock', 'artista_id' => $artista->id,
        ]);
    }

    public function test_cualquier_usuario_marca_y_desmarca_un_favorito(): void
    {
        $usuario = User::factory()->create();
        $evento = $this->evento();

        $this->actingAs($usuario)->postJson("/eventos/{$evento->id}/favorito")->assertOk()->assertExactJson(['favorito' => true]);
        $this->assertTrue($usuario->eventosFavoritos()->whereKey($evento->id)->exists());

        $this->actingAs($usuario)->postJson("/eventos/{$evento->id}/favorito")->assertOk()->assertExactJson(['favorito' => false]);
        $this->assertSame(0, $usuario->eventosFavoritos()->count());
    }

    public function test_sin_javascript_el_formulario_vuelve_a_la_pagina_anterior(): void
    {
        $evento = $this->evento();

        $this->actingAs(User::factory()->create())
            ->from('/eventos')
            ->post("/eventos/{$evento->id}/favorito")
            ->assertRedirect('/eventos')
            ->assertSessionHas('ok', 'Evento agregado a favoritos.');
    }

    public function test_un_invitado_no_puede_marcar_favoritos(): void
    {
        $evento = $this->evento();

        $this->post("/eventos/{$evento->id}/favorito")->assertRedirect('/login');
        $this->get('/favoritos')->assertRedirect('/login');
        $this->assertDatabaseCount('evento_user', 0);
    }

    public function test_la_pagina_de_favoritos_separa_proximos_y_pasados_y_solo_muestra_los_propios(): void
    {
        $usuario = User::factory()->create();
        $otro = User::factory()->create();
        $proximo = $this->evento('Concierto que viene', now()->addWeek());
        $pasado = $this->evento('Concierto que pasó', now()->subWeek());
        $ajeno = $this->evento('Favorito de otro');
        $usuario->eventosFavoritos()->attach([$proximo->id, $pasado->id]);
        $otro->eventosFavoritos()->attach($ajeno);

        $this->actingAs($usuario)->get('/favoritos')
            ->assertOk()
            ->assertSeeInOrder(['Próximos', 'Concierto que viene', 'Ya pasaron', 'Concierto que pasó'])
            ->assertDontSee('Favorito de otro');
    }

    public function test_sin_favoritos_se_muestra_un_mensaje(): void
    {
        $this->actingAs(User::factory()->create())->get('/favoritos')
            ->assertOk()->assertSee('Aún no tienes eventos favoritos.');
    }

    public function test_el_listado_el_detalle_y_el_inicio_muestran_el_estado_del_favorito(): void
    {
        $usuario = User::factory()->create();
        $marcado = $this->evento('Marcado');
        $libre = $this->evento('Libre');
        $usuario->eventosFavoritos()->attach($marcado);

        $this->get('/')->assertOk()->assertDontSee(route('eventos.favorito', $marcado));

        $listado = $this->actingAs($usuario)->get('/eventos')->assertOk()->getContent();
        $this->assertSame(1, substr_count($listado, 'aria-pressed="true"'));
        $this->assertSame(1, substr_count($listado, 'aria-pressed="false"'));

        $this->actingAs($usuario)->get("/eventos/{$marcado->id}")->assertSee('En favoritos');
        $this->actingAs($usuario)->get("/eventos/{$libre->id}")->assertSee('Agregar a favoritos');

        $this->actingAs($usuario)->get('/')->assertSee('aria-pressed="true"', false);
    }

    public function test_eliminar_el_evento_o_el_usuario_borra_sus_favoritos(): void
    {
        $usuario = User::factory()->create();
        $evento = $this->evento();
        $usuario->eventosFavoritos()->attach($evento);

        $evento->delete();
        $this->assertDatabaseCount('evento_user', 0);

        $usuario->eventosFavoritos()->attach($this->evento('Otro'));
        $usuario->delete();
        $this->assertDatabaseCount('evento_user', 0);
    }

    public function test_api_de_favoritos_con_token(): void
    {
        $usuario = User::factory()->create();
        $evento = $this->evento();

        $this->getJson('/api/favoritos')->assertUnauthorized();

        Sanctum::actingAs($usuario);
        $this->putJson("/api/favoritos/{$evento->id}")->assertNoContent();
        $this->putJson("/api/favoritos/{$evento->id}")->assertNoContent();
        $this->assertSame(1, $usuario->eventosFavoritos()->count());

        $this->getJson('/api/favoritos')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Gira')
            ->assertJsonPath('data.0.artista.nombre', 'Aterciopelados');

        $this->deleteJson("/api/favoritos/{$evento->id}")->assertNoContent();
        $this->getJson('/api/favoritos')->assertJsonCount(0, 'data');
        $this->putJson('/api/favoritos/999')->assertNotFound();
    }
}
