<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salarios', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedInteger('anio')->unique();
            $table->decimal('valor', 12, 2);

            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salarios');
    }
};
