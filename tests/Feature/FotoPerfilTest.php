<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FotoPerfilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function actualizar(User $user, array $extra = [])
    {
        return $this->actingAs($user)->patch('/profile', array_merge([
            'name' => $user->name,
            'email' => $user->email,
        ], $extra));
    }

    public function test_sube_la_foto_y_se_ve_en_el_menu(): void
    {
        $user = User::factory()->create();

        $this->actualizar($user, ['avatar' => UploadedFile::fake()->image('yo.jpg')])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertStringStartsWith('avatares/', $user->avatar);
        Storage::disk('public')->assertExists($user->avatar);

        $this->actingAs($user)->get('/dashboard')->assertSee($user->avatarUrl());
    }

    public function test_reemplazar_la_foto_borra_la_anterior(): void
    {
        $user = User::factory()->create();
        $this->actualizar($user, ['avatar' => UploadedFile::fake()->image('uno.jpg')]);
        $anterior = $user->fresh()->avatar;

        $this->actualizar($user->fresh(), ['avatar' => UploadedFile::fake()->image('dos.png')]);

        Storage::disk('public')->assertMissing($anterior);
        Storage::disk('public')->assertExists($user->fresh()->avatar);
    }

    public function test_quitar_la_foto(): void
    {
        $user = User::factory()->create();
        $this->actualizar($user, ['avatar' => UploadedFile::fake()->image('yo.jpg')]);
        $ruta = $user->fresh()->avatar;

        $this->actualizar($user->fresh(), ['quitar_avatar' => '1'])->assertSessionHasNoErrors();

        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_guardar_el_perfil_sin_foto_conserva_la_actual(): void
    {
        $user = User::factory()->create();
        $this->actualizar($user, ['avatar' => UploadedFile::fake()->image('yo.jpg')]);
        $ruta = $user->fresh()->avatar;

        $this->actualizar($user->fresh(), ['name' => 'Nombre nuevo']);

        $this->assertSame($ruta, $user->fresh()->avatar);
        $this->assertSame('Nombre nuevo', $user->fresh()->name);
    }

    public function test_rechaza_archivos_que_no_son_imagen_o_pesan_mas_de_2_mb(): void
    {
        $user = User::factory()->create();

        $this->actualizar($user, ['avatar' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors('avatar');
        $this->actualizar($user, ['avatar' => UploadedFile::fake()->image('grande.jpg')->size(3000)])
            ->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->avatar);
    }

    public function test_eliminar_la_cuenta_borra_su_foto(): void
    {
        $user = User::factory()->create();
        $this->actualizar($user, ['avatar' => UploadedFile::fake()->image('yo.jpg')]);
        $ruta = $user->fresh()->avatar;

        $this->actingAs($user->fresh())->delete('/profile', ['password' => 'password']);

        $this->assertNull($user->fresh());
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_sin_foto_el_menu_muestra_la_inicial(): void
    {
        $user = User::factory()->create(['name' => 'Karina']);

        $this->actingAs($user)->get('/dashboard')->assertDontSee('Foto de Karina');
    }
}
