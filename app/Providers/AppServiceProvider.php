<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Solo el admin crea, edita y borra artistas y eventos; el resto solo los consulta.
        Gate::define('administrar', fn (User $user) => $user->esAdmin());

        // Correo por la API HTTP de Brevo (MAIL_MAILER=brevo y BREVO_API_KEY).
        Mail::extend('brevo', fn () => (new BrevoTransportFactory)->create(
            new Dsn('brevo+api', 'default', config('services.brevo.key'))
        ));

        // Búsqueda "contiene" que trata % y _ como texto; '!' funciona como escape en SQLite, MySQL y PostgreSQL.
        Builder::macro('whereContiene', function (string $columna, string $texto) {
            $patron = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $texto).'%';

            // En PostgreSQL LIKE distingue mayúsculas; ILIKE no.
            $operador = $this->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            return $this->whereRaw($this->grammar->wrap($columna)." {$operador} ? escape '!'", [$patron]);
        });
    }
}
