<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuidMorphs('approvable');

            $table->foreignUuid('approval_definition_id')
                ->constrained('approval_definitions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
                ->default('pending');

            $table->unsignedInteger('current_step_order')->default(1);
            $table->boolean('is_current')->default(true);
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->json('meta')->nullable();

            $table->index(['status', 'is_current']);
            $table->index(['approval_definition_id', 'status']);
            $table->unique(['approvable_type', 'approvable_id', 'is_current'], 'approval_one_current');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};