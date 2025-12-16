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
        //tabla de fecha de apertura y cierre definida por el vice academica
        Schema::create('fechas', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('periodo')->unique();
            
            $table->date('fecha_apertura_preg');
            $table->date('fecha_cierre_docente_preg');
            $table->date('fecha_cierre_jefe_depart');
            $table->date('fecha_cierre_decano');
            $table->date('fecha_apertura_postg');
            $table->date('fecha_cierre_docente_postg');
            $table->date('fecha_cierre_coordinador_postg');
            $table->date('fecha_cierre_jefe_postg');

            // Auditoría
            $table->boolean('estado')->default(true)->comment('');
            $table->timestamp('fechacreacion')->useCurrent();
            $table->timestamp('fechamodificacion')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('usuariocreacion')->nullable();
            $table->unsignedBigInteger('usuariomodificacion')->nullable();
            $table->ipAddress('ipcreacion')->nullable();
            $table->ipAddress('ipmodificacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        {
            Schema::dropIfExists('fechas');
        }
    }
};
