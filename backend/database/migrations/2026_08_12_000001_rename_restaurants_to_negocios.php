<?php
// database/migrations/2026_08_12_000001_rename_restaurants_to_negocios.php
//
// DeliveryCentral no es solo para restaurantes — se vende a distintos
// tipos de negocio (florerías, farmacias, tiendas, etc.) que necesitan
// despachos. Esta migración renombra la tabla y la columna para que el
// código deje de asumir que el cliente siempre es un restaurante.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('restaurants', 'negocios');

        Schema::table('despachos', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id', 'external_order_id']);
            $table->renameColumn('restaurant_id', 'negocio_id');
        });

        Schema::table('despachos', function (Blueprint $table) {
            $table->index(['negocio_id', 'external_order_id']);
        });
    }

    public function down(): void
    {
        Schema::table('despachos', function (Blueprint $table) {
            $table->dropIndex(['negocio_id', 'external_order_id']);
            $table->renameColumn('negocio_id', 'restaurant_id');
        });

        Schema::table('despachos', function (Blueprint $table) {
            $table->index(['restaurant_id', 'external_order_id']);
        });

        Schema::rename('negocios', 'restaurants');
    }
};
