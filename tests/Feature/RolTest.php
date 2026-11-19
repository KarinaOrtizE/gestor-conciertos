<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolTest extends TestCase
{
    use RefreshDatabase;

    public function test_quien_se_registra_queda_como_usuario_aunque_envie_rol(): void
    {
        $this->post('/register', [
            'name' => 'Intruso', 'email' => 'intruso@example.com', 'rol' => 'admin',
            'password' => 'password', 'password_confirmation' => 'password',
        ]);

        $this->assertSame('usuario', User::where('email', 'intruso@example.com')->value('rol'));
    }

    public function test_comando_usuario_admin_promueve_al_usuario(): void
    {
        $user = User::factory()->create(['email' => 'karina@example.com']);

        $this->artisan('usuario:admin', ['email' => 'karina@example.com'])->assertSuccessful();

        $this->assertTrue($user->fresh()->esAdmin());
    }

    public function test_comando_usuario_admin_avisa_si_el_correo_no_existe(): void
    {
        $this->artisan('usuario:admin', ['email' => 'nadie@example.com'])->assertFailed();
    }
}
