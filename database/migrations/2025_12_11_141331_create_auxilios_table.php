<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auxilios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->boolean('pernocta')->default(false);
            $table->boolean('distancias_mayor_70km')->default(false);
            $table->boolean('fuera_cordoba')->default(false);

            $table->decimal('valor_por_docente', 10, 2);
            $table->decimal('valor_por_estudiante', 10, 2);
            $table->decimal('valor_por_acompanante', 10, 2)->default(0);

            $table->foreignUuid('programacion_id')
                  ->constrained('programaciones')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->foreignUuid('salario_id')
                  ->constrained('salarios')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->index('programacion_id');
            $table->index('salario_id');

            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auxilios');
    }
};
