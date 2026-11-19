<?php

use App\Models\User;
use Database\Seeders\DatosDePruebaSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('usuario:admin {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("No existe un usuario con el correo {$email}.");

        return 1;
    }

    $user->rol = 'admin';
    $user->save();
    $this->info("{$user->name} ahora es administrador.");
})->purpose('Dar rol de administrador a un usuario existente');

Artisan::command('db:sembrar-si-vacia', function () {
    if (! User::exists()) {
        $this->call('db:seed', ['--force' => true]);

        return;
    }

    // El disco del servidor se borra al reiniciar, pero la base no: se repone el afiche de ejemplo.
    DatosDePruebaSeeder::restaurarArchivos();
    $this->info('La base ya tiene datos; no se siembra.');
})->purpose('Siembra los datos de prueba solo si la base está vacía');
