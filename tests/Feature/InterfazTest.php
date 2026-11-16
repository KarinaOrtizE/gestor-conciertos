<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterfazTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_lateral_tiene_todas_las_secciones(): void
    {
        $html = $this->actingAs(User::factory()->create())->get('/dashboard')->getContent();
        $inicio = strpos($html, 'id="menu-lateral"');
        $this->assertNotFalse($inicio);
        $menu = substr($html, $inicio, strpos($html, '</aside>', $inicio) - $inicio);

        foreach (['dashboard', 'artistas.index', 'eventos.index', 'profile.edit', 'landing', 'logout'] as $ruta) {
            $this->assertStringContainsString(route($ruta), $menu, "Falta {$ruta} en el menú");
        }
    }

    public function test_menu_marca_la_seccion_actual_y_el_rol(): void
    {
        $admin = $this->actingAs(User::factory()->admin()->create())->get('/eventos')->assertSee('Administrador');
        $this->assertMatchesRegularExpression($this->enlaceActivo('eventos.index'), $admin->getContent());

        $usuario = $this->actingAs(User::factory()->create())->get('/artistas')->assertSee('Usuario');
        $this->assertMatchesRegularExpression($this->enlaceActivo('artistas.index'), $usuario->getContent());
    }

    private function enlaceActivo(string $ruta): string
    {
        return '/href="'.preg_quote(route($ruta), '/').'"\s+aria-current="page"/';
    }

    public function test_los_scripts_de_cada_pagina_van_al_final_del_body(): void
    {
        $html = $this->actingAs(User::factory()->create())->get('/eventos')->getContent();

        $this->assertGreaterThan(strpos($html, '<body'), strpos($html, 'jquery-3.7.1'));
        $this->assertLessThan(strpos($html, '</body>'), strpos($html, 'jquery-3.7.1'));
    }

    public function test_mensajes_de_validacion_en_espanol(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/eventos', [])
            ->assertSessionHasErrors(['titulo' => 'El campo título es obligatorio.']);
    }
}
