<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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

            $table->enum('estado_practica', [
                'borrador',
                'en_aprobacion',
                'aprobada',
                'rechazada',
                'en_ejecucion',
                'ejecutada',
                'en_legalizacion',
                'legalizada',
            ])->default('borrador');

            $table->enum('tipo_flujo', ['pregrado', 'postgrado'])->default('pregrado');

            $table->string('estado_flujo')
                  ->nullable(); // null = no está en aprobación

            $table->date('fecha_inicio');
            $table->date('fecha_finalizacion');

            // Índices útiles
            $table->index('creacion_id');
            $table->index('nombre_practica');
            $table->index('estado_practica');
            $table->index('estado_flujo');
            $table->index('tipo_flujo');
            $table->index(['fecha_inicio', 'fecha_finalizacion'], 'programaciones_fechas_idx');

            $table->unique(
                ['nombre_practica', 'fecha_inicio', 'fecha_finalizacion'],
                'programaciones_nombre_fechas_unique'
            );

            // Auditoría
            $table->timestamp('fechacreacion')->useCurrent();
            $table->timestamp('fechamodificacion')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('usuariocreacion')->nullable();
            $table->unsignedBigInteger('usuariomodificacion')->nullable();
            $table->ipAddress('ipcreacion')->nullable();
            $table->ipAddress('ipmodificacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programaciones');
    }
};
