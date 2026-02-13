<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approval_definition_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('approval_definition_id')
                ->constrained('approval_definitions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Orden del paso dentro del flujo (1..n)
            $table->unsignedInteger('step_order');

            // Rol responsable del paso (clave canónica: decano, vicerrectoria, etc.)
            $table->string('role_key', 80);

            // Requerir comentario al rechazar en este paso
            $table->boolean('requires_comment_on_reject')->default(true);

            // (Opcional) SLA en días (si luego quieres ventanas / vencimientos)
            $table->unsignedInteger('sla_days')->nullable();

            // Un flujo no puede tener dos pasos con el mismo orden
            $table->unique(['approval_definition_id','step_order'], 'approval_def_steps_unique');

            $table->index('role_key');

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
        Schema::dropIfExists('approval_definition_steps');
    }
};
