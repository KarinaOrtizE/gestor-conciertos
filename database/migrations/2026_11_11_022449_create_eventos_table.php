<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->dateTime('fecha');
            $table->string('lugar');
            $table->string('ciudad');
            $table->decimal('precio', 10, 2);
            $table->string('genero');
            $table->string('afiche')->nullable();
            $table->foreignId('artista_id')->constrained('artistas')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};