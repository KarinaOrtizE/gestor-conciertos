<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoApiTransport;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
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

    public function test_si_el_servicio_de_correo_falla_se_avisa_sin_error_500(): void
    {
        Mail::extend('fallido', fn () => new class extends AbstractTransport
        {
            protected function doSend(SentMessage $message): void
            {
                throw new TransportException('Clave rechazada');
            }

            public function __toString(): string
            {
                return 'fallido://';
            }
        });
        config(['mail.mailers.fallido' => ['transport' => 'fallido'], 'mail.default' => 'fallido']);
        User::factory()->create(['email' => 'karina@ejemplo.com']);

        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'karina@ejemplo.com'])
            ->assertRedirect('/forgot-password')
            ->assertSessionHasErrors(['email' => 'No pudimos enviar el correo en este momento. Intenta de nuevo más tarde.']);
    }
}
