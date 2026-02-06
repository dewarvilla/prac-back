<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aprobaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Polimórfico: Creacion / Programacion
            $table->string('aprobable_type');
            $table->uuid('aprobable_id');
            $table->index(['aprobable_type', 'aprobable_id'], 'aprobaciones_aprobable_idx');

            $table->string('etapa', 80);

            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');

            $table->unsignedBigInteger('decidido_por')->nullable();
            $table->timestamp('decidido_en')->nullable();

            $table->text('justificacion')->nullable();

            // Auditoría
            $table->ipAddress('ip')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->foreign('decidido_por')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aprobaciones');
    }
};
