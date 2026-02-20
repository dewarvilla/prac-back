<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogos', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->enum('nivel_academico', ['pregrado', 'postgrado'])->default('pregrado');
            $table->string('facultad');
            $table->string('programa_academico');

            $table->unique(['programa_academico', 'facultad'], 'catalogos_programa_facultad_unique');
            $table->index('facultad', 'catalogos_facultad_idx');
            $table->index('programa_academico', 'catalogos_programa_idx');

            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos');
    }
};