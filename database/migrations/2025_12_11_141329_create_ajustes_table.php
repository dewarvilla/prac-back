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
        Schema::create('ajustes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->date('fecha_ajuste');

            $table->enum('estado_ajuste', [
                'en_aprobacion',
                'aprobada',
                'rechazada',
            ])->default('en_aprobacion');

            $table->string('estado_flujo')->nullable();

            $table->string('justificacion');

            $table->foreignUuid('programacion_id')
                  ->constrained('programaciones')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->index('programacion_id');
            $table->index('estado_ajuste');
            $table->index('estado_flujo');

            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajustes');
    }
};
