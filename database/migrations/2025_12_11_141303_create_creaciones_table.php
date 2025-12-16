<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('catalogo_id')
                  ->constrained('catalogos')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->string('nombre_practica');
            $table->text('recursos_necesarios');
            $table->text('justificacion');

            $table->enum('estado_practica', [
                'borrador',
                'en_aprobacion',
                'aprobada',
                'rechazada',
                'creada',
            ])->default('borrador');

            $table->string('estado_flujo')->default('departamento');
            
            $table->unique(['catalogo_id', 'nombre_practica'], 'creaciones_catalogo_nombre_unique');

            $table->index('nombre_practica');
            $table->index('estado_practica');
            $table->index('estado_flujo');

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
        Schema::dropIfExists('creaciones');
    }
};
