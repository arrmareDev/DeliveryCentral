<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('restaurant_id')
                ->constrained('restaurants')
                ->cascadeOnDelete();

            // ID del pedido en el sistema del restaurante (no es FK, es referencia externa)
            $table->unsignedBigInteger('external_order_id');

            $table->foreignId('motorizado_id')
                ->nullable()
                ->constrained('motorizados')
                ->nullOnDelete();

            $table->enum('estado', [
                'solicitado',
                'aceptado',
                'recogido',
                'entregado',
                'cancelado',
            ])->default('solicitado');

            // Todo el detalle del pedido viaja embebido aquí
            $table->json('order_data');

            $table->decimal('comision_motorizado', 8, 2)->default(0.50);
            $table->decimal('monto_cobrado', 10, 2)->nullable();
            $table->string('nota_motorizado')->nullable();

            $table->timestamp('solicitado_at')->nullable();
            $table->timestamp('aceptado_at')->nullable();
            $table->timestamp('recogido_at')->nullable();
            $table->timestamp('entregado_at')->nullable();

            $table->timestamps();

            $table->index(['restaurant_id', 'external_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};
