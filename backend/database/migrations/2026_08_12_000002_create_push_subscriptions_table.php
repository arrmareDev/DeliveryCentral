<?php
// database/migrations/2026_08_12_000002_create_push_subscriptions_table.php
//
// Cada fila es un navegador/dispositivo de un motorizado que aceptó
// recibir notificaciones push (puede tener varias — celular y tablet).

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->morphs('subscribable');
            $table->text('endpoint');
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('content_encoding')->nullable();
            $table->timestamps();

            $table->unique(['subscribable_type', 'subscribable_id', 'endpoint'], 'push_subscriptions_subscribable_endpoint_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
