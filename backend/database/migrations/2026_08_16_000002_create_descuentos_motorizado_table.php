<?php
// database/migrations/2026_08_16_000002_create_descuentos_motorizado_table.php
//
// Descuentos aplicados manualmente por el admin (faltas, daños, etc.)
// — tabla separada de comisiones_motorizado a propósito: son conceptos
// distintos (lo que se le debe vs lo que se le descuenta), mezclarlos
// en una sola tabla con montos negativos sería confuso de auditar.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descuentos_motorizado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('motorizado_id')->constrained('motorizados')->cascadeOnDelete();
            $table->decimal('monto', 8, 2);
            $table->string('motivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descuentos_motorizado');
    }
};
