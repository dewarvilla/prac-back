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

            $table->unsignedInteger('step_order');
            $table->string('role_key', 80);

            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped'])
                ->default('pending');

            $table->unsignedBigInteger('acted_by')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->text('comment')->nullable();

            $table->foreign('acted_by')
                ->references('id')->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->unique(['approval_request_id', 'step_order'], 'approval_step_unique');
            $table->index(['approval_request_id', 'status']);
            $table->index(['role_key', 'status']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
    }
};