<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    private Artista $artista;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->artista = Artista::create(['nombre' => 'Aterciopelados']);
    }

    private function datos(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'Gira 2027', 'fecha' => '2027-03-20 20:00', 'lugar' => 'Movistar Arena',
            'ciudad' => 'Bogotá', 'precio' => 180000, 'genero' => 'Rock', 'artista_id' => $this->artista->id,
        ], $extra);
    }

    public function test_usuario_puede_ver_listado_y_detalle(): void
    {
        $user = User::factory()->create();
        $evento = Evento::create($this->datos());

        $this->actingAs($user)->get('/eventos')->assertOk()->assertSee('Gira 2027')->assertDontSee('+ Nuevo evento');
        $this->actingAs($user)->get("/eventos/{$evento->id}")->assertOk();
    }

    public function test_usuario_no_puede_crear_editar_ni_borrar(): void
    {
        $user = User::factory()->create();
        $evento = Evento::create($this->datos());

        $this->actingAs($user)->get('/eventos/create')->assertForbidden();
        $this->actingAs($user)->post('/eventos', $this->datos(['titulo' => 'Otro']))->assertForbidden();
        $this->actingAs($user)->get("/eventos/{$evento->id}/edit")->assertForbidden();
        $this->actingAs($user)->put("/eventos/{$evento->id}", $this->datos(['titulo' => 'Cambiado']))->assertForbidden();
        $this->actingAs($user)->delete("/eventos/{$evento->id}")->assertForbidden();

        $this->assertSame(['Gira 2027'], Evento::pluck('titulo')->all());
    }

    public function test_admin_crea_con_afiche_lo_reemplaza_y_lo_borra(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/eventos', $this->datos(['afiche' => UploadedFile::fake()->image('a.png')]))
            ->assertRedirect('/eventos');
        $evento = Evento::firstOrFail();
        $primero = $evento->afiche;
        Storage::disk('public')->assertExists($primero);

        $this->actingAs($admin)->put("/eventos/{$evento->id}", $this->datos(['afiche' => UploadedFile::fake()->image('b.png')]))
            ->assertRedirect('/eventos');
        $segundo = $evento->fresh()->afiche;
        Storage::disk('public')->assertMissing($primero);
        Storage::disk('public')->assertExists($segundo);

        $this->actingAs($admin)->delete("/eventos/{$evento->id}")->assertRedirect('/eventos');
        Storage::disk('public')->assertMissing($segundo);
        $this->assertSame(0, Evento::count());
    }

    public function test_genero_debe_ser_uno_de_la_lista(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/eventos', $this->datos(['genero' => 'Cualquier cosa']))
            ->assertSessionHasErrors('genero');

        $this->assertSame(0, Evento::count());
    }

    public function test_titulo_con_comillas_no_queda_dentro_de_javascript(): void
    {
        Evento::create($this->datos(['titulo' => "x');alert(1);('"]));

        $html = $this->actingAs(User::factory()->admin()->create())->get('/eventos')->getContent();

        $this->assertStringNotContainsString('onsubmit=', $html);
        $this->assertStringContainsString('data-confirmar="¿Eliminar el evento x&#039;);alert(1);(&#039;?"', $html);
    }
}
