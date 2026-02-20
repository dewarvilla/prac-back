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

            $table->unsignedInteger('step_order');
            $table->string('role_key', 80);
            $table->boolean('requires_comment_on_reject')->default(true);
            $table->unsignedInteger('sla_days')->nullable();
            $table->unique(['approval_definition_id','step_order'], 'approval_def_steps_unique');
            $table->index('role_key');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_definition_steps');
    }
};
