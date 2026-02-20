<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // filtrar
            $table->string('auditable_table', 80)->index();

            // morph (modelo + id)
            $table->string('auditable_type', 255)->index(); // Ej: App\Models\Creacion
            $table->string('auditable_id', 64)->nullable()->index(); // uuid o bigint convertido a string

            // created, updated, deleted....
            $table->string('event', 40)->index();

            // Antes y después
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Actor
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_email')->nullable()->index();

            // Contexto request
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('method', 10)->nullable();
            $table->text('url')->nullable();
            $table->string('route', 255)->nullable();
            $table->string('request_id', 80)->nullable()->index();

            // Meta extra (comentarios, rol, step_order, etc.)
            $table->json('meta')->nullable();

            $table->timestamp('created_at')->useCurrent()->index();

            // Índices
            $table->index(['auditable_type', 'auditable_id'], 'audits_type_id_idx');
            $table->index(['auditable_table', 'auditable_id'], 'audits_table_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
