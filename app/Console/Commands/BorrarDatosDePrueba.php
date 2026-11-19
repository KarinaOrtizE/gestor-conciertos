<?php

namespace App\Console\Commands;

use App\Models\Artista;
use App\Models\User;
use Database\Seeders\DatosDePruebaSeeder;
use Illuminate\Console\Command;

class BorrarDatosDePrueba extends Command
{
    protected $signature = 'datos-prueba:borrar
                            {--usuarios : Borra también admin@example.com y test@example.com}
                            {--force : No pide confirmación}';

    protected $description = 'Elimina los artistas y eventos de ejemplo de DatosDePruebaSeeder';

    private const USUARIOS = ['admin@example.com', 'test@example.com'];

    public function handle(): int
    {
        $artistas = Artista::whereIn('nombre', array_column(DatosDePruebaSeeder::ARTISTAS, 'nombre'))->withCount('eventos')->get();
        $usuarios = $this->option('usuarios') ? User::whereIn('email', self::USUARIOS)->get() : collect();

        if ($artistas->isEmpty() && $usuarios->isEmpty()) {
            $this->info('No hay datos de prueba para borrar.');

            return self::SUCCESS;
        }

        $this->table(['Artista', 'Eventos'], $artistas->map(fn ($a) => [$a->nombre, $a->eventos_count]));
        $usuarios->each(fn ($u) => $this->line("Usuario: {$u->email}"));

        if ($usuarios->isNotEmpty() && ! User::where('rol', 'admin')->whereNotIn('email', self::USUARIOS)->exists()) {
            $this->warn('No queda otro administrador. Dale el rol a tu cuenta con: php artisan usuario:admin tu-correo');
        }

        if (! $this->option('force') && ! $this->confirm('¿Borrar estos datos? Los eventos de estos artistas también se eliminan.')) {
            return self::FAILURE;
        }

        // Se borra uno por uno para que el modelo elimine los afiches de cada evento.
        $artistas->each->delete();
        $usuarios->each->delete();

        $this->info("Se borraron {$artistas->count()} artistas con sus eventos y {$usuarios->count()} usuarios.");

        return self::SUCCESS;
    }
}
