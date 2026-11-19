<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DatosDePruebaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatosDePruebaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_el_seeder_crea_artistas_eventos_proximos_afiche_y_favoritos(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(5, Artista::count());
        $this->assertSame(11, Evento::count());
        $this->assertSame(0, Evento::where('fecha', '<', now())->count());

        $afiche = Evento::where('titulo', 'Gira Florecita Rockera')->value('afiche');
        Storage::disk('public')->assertExists($afiche);

        $this->assertSame(4, User::where('email', 'test@example.com')->first()->eventosFavoritos()->count());
    }

    public function test_sembrar_dos_veces_no_duplica(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatosDePruebaSeeder::class);

        $this->assertSame(5, Artista::count());
        $this->assertSame(11, Evento::count());
        $this->assertCount(1, Storage::disk('public')->files('afiches'));
    }

    public function test_el_comando_borra_solo_los_datos_de_prueba(): void
    {
        $this->seed(DatabaseSeeder::class);
        $propio = Artista::create(['nombre' => 'Artista real']);
        Evento::create([
            'titulo' => 'Evento real', 'fecha' => now()->addWeek(), 'lugar' => 'Teatro', 'ciudad' => 'Cali',
            'precio' => 50000, 'genero' => 'Jazz', 'artista_id' => $propio->id,
        ]);

        $this->artisan('datos-prueba:borrar')
            ->expectsConfirmation('¿Borrar estos datos? Los eventos de estos artistas también se eliminan.', 'yes')
            ->expectsOutputToContain('Se borraron 5 artistas con sus eventos y 0 usuarios.')
            ->assertSuccessful();

        $this->assertSame(['Artista real'], Artista::pluck('nombre')->all());
        $this->assertSame(['Evento real'], Evento::pluck('titulo')->all());
        $this->assertDatabaseCount('evento_user', 0);
        $this->assertSame([], Storage::disk('public')->files('afiches'));
        $this->assertSame(2, User::count());
    }

    public function test_si_no_se_confirma_no_borra_nada(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->artisan('datos-prueba:borrar')
            ->expectsConfirmation('¿Borrar estos datos? Los eventos de estos artistas también se eliminan.', 'no')
            ->assertFailed();

        $this->assertSame(11, Evento::count());
    }

    public function test_con_usuarios_borra_las_cuentas_de_ejemplo_y_avisa_si_no_queda_admin(): void
    {
        $this->seed(DatabaseSeeder::class);
        $karina = User::factory()->create(['email' => 'karina@ejemplo.com']);

        $this->artisan('datos-prueba:borrar --usuarios --force')
            ->expectsOutputToContain('No queda otro administrador')
            ->assertSuccessful();

        $this->assertSame([$karina->id], User::pluck('id')->all());
        $this->assertSame(0, Artista::count());
    }

    public function test_sin_datos_de_prueba_lo_dice(): void
    {
        $this->artisan('datos-prueba:borrar')->expectsOutput('No hay datos de prueba para borrar.')->assertSuccessful();
    }
}
