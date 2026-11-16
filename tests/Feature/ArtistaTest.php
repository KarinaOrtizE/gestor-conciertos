<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtistaTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitado_es_enviado_al_login(): void
    {
        $this->get('/artistas')->assertRedirect('/login');
    }

    public function test_usuario_puede_ver_listado_y_detalle(): void
    {
        $user = User::factory()->create();
        $artista = Artista::create(['nombre' => 'Aterciopelados']);

        $this->actingAs($user)->get('/artistas')->assertOk()->assertSee('Aterciopelados')->assertDontSee('+ Nuevo artista');
        $this->actingAs($user)->get("/artistas/{$artista->id}")->assertOk();
    }

    public function test_usuario_no_puede_crear_editar_ni_borrar(): void
    {
        $user = User::factory()->create();
        $artista = Artista::create(['nombre' => 'Aterciopelados']);

        $this->actingAs($user)->get('/artistas/create')->assertForbidden();
        $this->actingAs($user)->post('/artistas', ['nombre' => 'Otro'])->assertForbidden();
        $this->actingAs($user)->get("/artistas/{$artista->id}/edit")->assertForbidden();
        $this->actingAs($user)->put("/artistas/{$artista->id}", ['nombre' => 'Cambiado'])->assertForbidden();
        $this->actingAs($user)->delete("/artistas/{$artista->id}")->assertForbidden();

        $this->assertSame(['Aterciopelados'], Artista::pluck('nombre')->all());
    }

    public function test_admin_crea_edita_y_borra(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/artistas/create')->assertOk();
        $this->actingAs($admin)->post('/artistas', ['nombre' => 'Bomba Estéreo', 'generos' => 'Electrónica'])
            ->assertRedirect('/artistas');
        $artista = Artista::firstOrFail();

        $this->actingAs($admin)->put("/artistas/{$artista->id}", ['nombre' => 'Bomba Estéreo (live)'])
            ->assertRedirect('/artistas');
        $this->assertSame('Bomba Estéreo (live)', $artista->fresh()->nombre);

        $this->actingAs($admin)->delete("/artistas/{$artista->id}")->assertRedirect('/artistas');
        $this->assertSame(0, Artista::count());
    }

    public function test_borrar_artista_quita_los_afiches_de_sus_eventos(): void
    {
        Storage::fake('public');
        $artista = Artista::create(['nombre' => 'Aterciopelados']);
        $afiche = UploadedFile::fake()->image('afiche.png')->store('afiches', 'public');
        Evento::create([
            'titulo' => 'Gira', 'fecha' => now()->addMonth(), 'lugar' => 'Coliseo', 'ciudad' => 'Bogotá',
            'precio' => 100000, 'genero' => 'Rock', 'artista_id' => $artista->id, 'afiche' => $afiche,
        ]);

        $this->actingAs(User::factory()->admin()->create())->delete("/artistas/{$artista->id}");

        $this->assertSame(0, Evento::count());
        Storage::disk('public')->assertMissing($afiche);
    }

    public function test_nombre_con_comillas_no_queda_dentro_de_javascript(): void
    {
        Artista::create(['nombre' => "x');alert(1);('"]);

        $html = $this->actingAs(User::factory()->admin()->create())->get('/artistas')->getContent();

        $this->assertStringNotContainsString('onsubmit=', $html);
        $this->assertStringContainsString('data-confirmar="¿Eliminar a x&#039;);alert(1);(&#039;?', $html);
    }
}
