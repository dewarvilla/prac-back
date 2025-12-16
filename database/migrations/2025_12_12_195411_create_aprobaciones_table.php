<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aprobaciones', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuidMorphs('aprobable');

            $table->string('etapa');

            $table->enum('decision', ['aprobada', 'rechazada', 'devuelta']);

            $table->foreignId('user_id')->constrained('users');

            $table->text('comentario')->nullable();

            $table->timestamps();

            $table->index(['aprobable_id', 'aprobable_type']);
            $table->index('etapa');
            $table->index('decision');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aprobaciones');
    }
};
