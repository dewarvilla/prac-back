<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rutapeajes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('ruta_id')
                  ->constrained('rutas')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string('nombre')->index();

            $table->decimal('lat', 10, 8)->nullable()->index();
            $table->decimal('lng', 11, 8)->nullable()->index();

            $table->unsignedInteger('distancia_m')->nullable();

            $table->decimal('orden_km', 8, 2)->nullable()->index();

            $table->string('categoria_vehiculo', 20)->nullable()->index();

            $table->decimal('cat_i',   12, 2)->nullable();
            $table->decimal('cat_ii',  12, 2)->nullable();
            $table->decimal('cat_iii', 12, 2)->nullable();
            $table->decimal('cat_iv',  12, 2)->nullable();
            $table->decimal('cat_v',   12, 2)->nullable();
            $table->decimal('cat_vi',  12, 2)->nullable();
            $table->decimal('cat_vii', 12, 2)->nullable();

            $table->decimal('valor_total', 12, 2)->nullable();

            $table->string('fuente')->nullable();
            $table->date('fecha_tarifa')->nullable();

            $table->boolean('estado')->default(true);
            $table->timestamp('fechacreacion')->useCurrent();
            $table->timestamp('fechamodificacion')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('usuariocreacion')->nullable();
            $table->unsignedBigInteger('usuariomodificacion')->nullable();
            $table->ipAddress('ipcreacion')->nullable();
            $table->ipAddress('ipmodificacion')->nullable();

            $table->index(['ruta_id', 'orden_km']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutapeajes');
    }
};
