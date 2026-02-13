<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approval_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Código único del flujo (ej: CREACION_PRACTICA, PROGRAMACION_PREGRADO)
            $table->string('code', 80)->unique();

            // Nombre legible del flujo
            $table->string('name', 150);

            // Para desactivar un flujo sin borrarlo
            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('approval_definitions');
    }
};
