<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Sin factories: en producción no se instala Faker y el servidor siembra al arrancar.
        $cuentas = [
            ['name' => 'Administrador', 'email' => 'admin@example.com', 'rol' => 'admin'],
            ['name' => 'Test User', 'email' => 'test@example.com', 'rol' => 'usuario'],
        ];

        foreach ($cuentas as $cuenta) {
            $usuario = User::firstOrNew(['email' => $cuenta['email']]);

            if (! $usuario->exists) {
                $usuario->forceFill($cuenta + ['password' => 'password', 'email_verified_at' => now()])->save();
            }
        }

        // Contenido de ejemplo. Para no sembrarlo, quita esta línea; para borrarlo: php artisan datos-prueba:borrar
        $this->call(DatosDePruebaSeeder::class);
    }
}
