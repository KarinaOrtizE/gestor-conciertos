<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Tests\TestCase;

class CorreoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_mailer_brevo_usa_la_api_http_con_la_clave_configurada(): void
    {
        config(['services.brevo.key' => 'clave-de-prueba']);

        $transporte = Mail::mailer('brevo')->getSymfonyTransport();

        $this->assertInstanceOf(BrevoApiTransport::class, $transporte);
        $this->assertSame('brevo+api://api.brevo.com', (string) $transporte);
    }

    public function test_el_correo_para_restablecer_la_contrasena_sale_en_espanol(): void
    {
        Notification::fake();
        $usuario = User::factory()->create(['email' => 'karina@ejemplo.com']);

        $this->post('/forgot-password', ['email' => 'karina@ejemplo.com'])
            ->assertSessionHas('status', 'Te enviamos por correo el enlace para restablecer tu contraseña.');

        Notification::assertSentTo($usuario, ResetPassword::class, function (ResetPassword $aviso) use ($usuario) {
            $correo = $aviso->toMail($usuario);
            $html = (string) $correo->render();

            return $correo->subject === 'Restablecer contraseña'
                && str_contains($html, 'Restablecer contraseña')
                && str_contains($html, url('/reset-password/'.$aviso->token));
        });
    }
}
