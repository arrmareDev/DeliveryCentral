<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla separada de la de `password_reset_tokens` (que usa el
        // broker "users" del panel admin) para no mezclar tokens de
        // dos guards/modelos distintos en la misma tabla.
        Schema::create('motorizado_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motorizado_password_reset_tokens');
    }
};
