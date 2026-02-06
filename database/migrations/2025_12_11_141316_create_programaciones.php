<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('programaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // FK a creaciones (UUID)
            $table->uuid('creacion_id');
            $table->foreign('creacion_id')
                ->references('id')->on('creaciones')
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
                'en_aprobacion','aprobada','rechazada','en_ejecucion',
                'ejecutada','en_legalizacion','legalizada'
            ])->default('en_aprobacion');

            $table->enum('estado_depart', ['aprobada','rechazada','pendiente'])->default('pendiente');
            $table->enum('estado_postg', ['aprobada','rechazada','pendiente'])->default('pendiente');
            $table->enum('estado_decano', ['aprobada','rechazada','pendiente'])->default('pendiente');
            $table->enum('estado_jefe_postg', ['aprobada','rechazada','pendiente'])->default('pendiente');
            $table->enum('estado_vice', ['aprobada','rechazada','pendiente'])->default('pendiente');

            $table->date('fecha_inicio');
            $table->date('fecha_finalizacion');

            // índices para etapas
            $table->index('estado_depart');
            $table->index('estado_postg');
            $table->index('estado_decano');
            $table->index('estado_jefe_postg');
            $table->index('estado_vice');

            $table->index('creacion_id');
            $table->index('nombre_practica');
            $table->index('estado_practica');
            $table->index(['fecha_inicio','fecha_finalizacion'], 'programaciones_fechas_idx');

            $table->unique(['nombre_practica','fecha_inicio','fecha_finalizacion'], 'programaciones_nombre_fechas_unique');

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
