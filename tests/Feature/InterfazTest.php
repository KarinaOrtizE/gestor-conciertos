<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterfazTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_movil_incluye_artistas_y_eventos(): void
    {
        $html = $this->actingAs(User::factory()->create())->get('/dashboard')->getContent();
        $movil = substr($html, strpos($html, 'Responsive Navigation Menu'));

        $this->assertStringContainsString(route('artistas.index'), $movil);
        $this->assertStringContainsString(route('eventos.index'), $movil);
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
