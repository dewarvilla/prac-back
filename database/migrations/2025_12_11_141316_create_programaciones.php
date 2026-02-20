<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('creacion_id')
                ->constrained('creaciones')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('nombre_practica');

            $table->text('descripcion')->nullable();
            $table->boolean('requiere_transporte')->default(false);
            $table->string('lugar_de_realizacion')->nullable();
            $table->text('justificacion');
            $table->text('recursos_necesarios');
            $table->unsignedInteger('numero_estudiantes');

            // Para escoger el flujo
            $table->enum('nivel_formacion', ['pregrado', 'posgrado']);

            // Estado macro para el proceso de programación
            $table->enum('estado_practica', [
                'en_aprobacion',
                'aprobada',
                'rechazada',
                'en_ejecucion',
                'ejecutada',
                'en_legalizacion',
                'legalizada',
            ])->default('en_aprobacion');

            $table->date('fecha_inicio');
            $table->date('fecha_finalizacion');

            $table->index('creacion_id');
            $table->index('nombre_practica');
            $table->index('nivel_formacion');
            $table->index('estado_practica');
            $table->index(['fecha_inicio','fecha_finalizacion'], 'programaciones_fechas_idx');

            $table->unique(
                ['nombre_practica', 'fecha_inicio', 'fecha_finalizacion'],
                'programaciones_nombre_fechas_unique'
            );

            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programaciones');
    }
};
