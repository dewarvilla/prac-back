<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('approval_request_id')
                ->constrained('approval_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Orden del paso dentro de la solicitud (1..n)
            $table->unsignedInteger('step_order');

            // Rol responsable del paso
            $table->string('role_key', 80);

            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped'])
                  ->default('pending');

            // Decisión
            $table->unsignedBigInteger('acted_by')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->text('comment')->nullable();

            $table->unique(['approval_request_id', 'step_order'], 'approval_step_unique');
            $table->index(['approval_request_id', 'status']);
            $table->index(['role_key', 'status']);

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
        Schema::dropIfExists('approval_steps');
    }
};
