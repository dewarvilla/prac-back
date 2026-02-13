<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Polimórfico UUID: approvable_type (string) + approvable_id (uuid)
            $table->uuidMorphs('approvable');

            // Definición usada para esta solicitud
            $table->foreignUuid('approval_definition_id')
                ->constrained('approval_definitions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
                  ->default('pending');

            $table->unsignedInteger('current_step_order')->default(1);

            // 1 solicitud activa por aprobable
            $table->boolean('is_active')->default(true);

            $table->index(['status', 'is_active']);
            $table->index(['approval_definition_id', 'status']);

            $table->unsignedTinyInteger('active_key')->nullable(); // 1 cuando activa, null cuando cerrada
            $table->index(['status', 'active_key']);
            $table->unique(['approvable_type', 'approvable_id', 'active_key'], 'approval_one_active');

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
        Schema::dropIfExists('approval_requests');
    }
};
