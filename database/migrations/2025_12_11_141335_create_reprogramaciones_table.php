<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // tabla reprogramacion de practicas
        Schema::create('reprogramaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->date('fecha_reprogramacion');

            $table->enum('estado_reprogramacion', [
                'borrador',
                'en_aprobacion',
                'aprobada',
                'rechazada',
            ])->default('borrador');

            $table->enum('tipo_reprogramacion', ['normal', 'emergencia'])->default('normal');

            $table->string('estado_flujo')->nullable();

            $table->string('justificacion');

            $table->foreignUuid('programacion_id')
                  ->constrained('programaciones')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->index('programacion_id');
            $table->index('estado_reprogramacion');
            $table->index('estado_flujo');
            $table->index('tipo_reprogramacion');

            // Auditoría
            $table->timestamp('fechacreacion')->useCurrent();
            $table->timestamp('fechamodificacion')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('usuariocreacion')->nullable();
            $table->unsignedBigInteger('usuariomodificacion')->nullable();
            $table->ipAddress('ipcreacion')->nullable();
            $table->ipAddress('ipmodificacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reprogramaciones');
    }
};
