<?php
// database/migrations/2026_08_16_000001_create_zonas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('motorizado_zona', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motorizado_id')->constrained('motorizados')->cascadeOnDelete();
            $table->foreignId('zona_id')->constrained('zonas')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['motorizado_id', 'zona_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motorizado_zona');
        Schema::dropIfExists('zonas');
    }
};
