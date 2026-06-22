<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motorizados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('telefono', 20);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('foto')->nullable();
            $table->enum('estado', ['disponible', 'ocupado', 'inactivo'])
                ->default('inactivo');
            $table->boolean('verificado')->default(false);
            $table->boolean('activo')->default(false);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamp('ultimo_ping')->nullable();
            $table->string('push_token')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motorizados');
    }
};
