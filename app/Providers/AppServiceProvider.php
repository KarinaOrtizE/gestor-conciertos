<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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

        // Búsqueda "contiene" que trata % y _ como texto; '!' funciona como escape en SQLite, MySQL y PostgreSQL.
        Builder::macro('whereContiene', function (string $columna, string $texto) {
            $patron = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $texto).'%';

            // En PostgreSQL LIKE distingue mayúsculas; ILIKE no.
            $operador = $this->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            return $this->whereRaw($this->grammar->wrap($columna)." {$operador} ? escape '!'", [$patron]);
        });
    }
}
