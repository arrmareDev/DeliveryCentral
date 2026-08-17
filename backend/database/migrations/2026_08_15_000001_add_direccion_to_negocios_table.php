<?php
// database/migrations/2026_08_15_000001_add_direccion_to_negocios_table.php
//
// Dirección física del negocio (de dónde sale el pedido a recoger).
// Vive una sola vez por negocio, no se repite en cada despacho.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('slug');
            $table->decimal('lat', 10, 7)->nullable()->after('direccion');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn(['direccion', 'lat', 'lng']);
        });
    }
};
