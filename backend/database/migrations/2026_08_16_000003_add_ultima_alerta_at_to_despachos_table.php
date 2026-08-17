<?php
// database/migrations/2026_08_16_000003_add_ultima_alerta_at_to_despachos_table.php
//
// Para el reenvio automatico de la notificacion push cuando nadie
// acepta un despacho — sin esto, reenviaria en cada corrida del
// job (cada minuto) sin control de cuándo fue la última vez.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('despachos', function (Blueprint $table) {
            $table->timestamp('ultima_alerta_at')->nullable()->after('solicitado_at');
        });
    }

    public function down(): void
    {
        Schema::table('despachos', function (Blueprint $table) {
            $table->dropColumn('ultima_alerta_at');
        });
    }
};
