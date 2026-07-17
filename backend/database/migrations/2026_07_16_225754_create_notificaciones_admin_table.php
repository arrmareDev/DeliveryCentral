<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_admin', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // nuevo_despacho, motorizado_pendiente, despacho_cancelado, etc.
            $table->string('titulo');
            $table->text('mensaje');
            $table->json('data')->nullable(); // ids relevantes para navegar (despacho_id, motorizado_id...)
            $table->boolean('leido')->default(false);
            $table->timestamps();

            $table->index(['leido', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_admin');
    }
};
