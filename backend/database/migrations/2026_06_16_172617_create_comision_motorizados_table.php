<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comisiones_motorizado', function (Blueprint $table) {
            $table->id();

            $table->foreignId('despacho_id')
                ->constrained('despachos')
                ->cascadeOnDelete();

            $table->foreignId('motorizado_id')
                ->constrained('motorizados')
                ->cascadeOnDelete();

            $table->decimal('monto', 8, 2)->default(0.50);
            $table->enum('estado', ['pendiente', 'cobrado'])->default('pendiente');
            $table->timestamp('cobrado_at')->nullable();
            $table->foreignId('cobrado_por')->nullable(); // tu user_id, sin FK estricta por simplicidad

            $table->timestamps();

            $table->index(['motorizado_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comisiones_motorizado');
    }
};
