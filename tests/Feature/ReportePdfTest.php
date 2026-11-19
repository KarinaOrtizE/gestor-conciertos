<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportePdfTest extends TestCase
{
    use RefreshDatabase;

    private function evento(array $extra = []): Evento
    {
        return Evento::create(array_merge([
            'titulo' => 'Gira', 'fecha' => now()->addMonth(), 'lugar' => 'Coliseo', 'ciudad' => 'Bogotá',
            'precio' => 150000, 'genero' => 'Rock',
            'artista_id' => Artista::firstOrCreate(['nombre' => 'Aterciopelados'])->id,
        ], $extra));
    }

    public function test_invitado_no_descarga_reportes(): void
    {
        $evento = $this->evento();

        $this->get('/reportes/eventos')->assertRedirect('/login');
        $this->get("/eventos/{$evento->id}/pdf")->assertRedirect('/login');
    }

    public function test_descarga_el_reporte_de_eventos(): void
    {
        $this->evento();

        $respuesta = $this->actingAs(User::factory()->create())->get('/reportes/eventos');

        $respuesta->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('reporte-eventos-', $respuesta->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_el_reporte_de_proximos_deja_fuera_los_pasados(): void
    {
        $this->evento(['titulo' => 'Futuro']);
        $this->evento(['titulo' => 'Pasado', 'fecha' => now()->subMonth()]);

        $eventos = Evento::with('artista')->where('fecha', '>=', now())->orderBy('fecha')->get();
        $html = view('pdf.eventos', ['eventos' => $eventos, 'soloProximos' => true])->render();

        $this->assertStringContainsString('Futuro', $html);
        $this->assertStringNotContainsString('Pasado', $html);
        $this->actingAs(User::factory()->create())->get('/reportes/eventos?proximos=1')->assertOk();
    }

    public function test_descarga_la_ficha_de_un_evento_con_afiche(): void
    {
        Storage::fake('public');
        $evento = $this->evento(['afiche' => UploadedFile::fake()->image('afiche.png', 400, 600)->store('afiches', 'public')]);

        $respuesta = $this->actingAs(User::factory()->create())->get("/eventos/{$evento->id}/pdf");

        $respuesta->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString("evento-{$evento->id}.pdf", $respuesta->headers->get('content-disposition'));
    }

    public function test_los_botones_de_pdf_aparecen_en_listado_y_detalle(): void
    {
        $evento = $this->evento();
        $user = User::factory()->create();

        $this->actingAs($user)->get('/eventos')->assertSee(route('reportes.eventos'));
        $this->actingAs($user)->get("/eventos/{$evento->id}")->assertSee(route('eventos.pdf', $evento));
    }
}
