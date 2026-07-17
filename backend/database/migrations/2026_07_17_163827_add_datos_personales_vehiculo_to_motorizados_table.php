<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motorizados', function (Blueprint $table) {
            // Datos personales
            $table->string('dni', 15)->nullable()->after('email');
            $table->string('nombres', 100)->nullable()->after('dni');
            $table->string('apellidos', 100)->nullable()->after('nombres');
            $table->date('fecha_nacimiento')->nullable()->after('apellidos');

            // Datos del vehículo
            $table->string('placa', 10)->nullable()->after('fecha_nacimiento');
            $table->string('marca_vehiculo', 50)->nullable()->after('placa');
            $table->string('modelo_vehiculo', 50)->nullable()->after('marca_vehiculo');
            $table->unsignedSmallInteger('anio_vehiculo')->nullable()->after('modelo_vehiculo');
            $table->string('foto_vehiculo')->nullable()->after('anio_vehiculo');
            $table->string('soat_numero', 30)->nullable()->after('foto_vehiculo');

            $table->unique('dni');
            $table->unique('placa');
        });
    }

    public function down(): void
    {
        Schema::table('motorizados', function (Blueprint $table) {
            $table->dropUnique(['dni']);
            $table->dropUnique(['placa']);
            $table->dropColumn([
                'dni',
                'nombres',
                'apellidos',
                'fecha_nacimiento',
                'placa',
                'marca_vehiculo',
                'modelo_vehiculo',
                'anio_vehiculo',
                'foto_vehiculo',
                'soat_numero',
            ]);
        });
    }
};
