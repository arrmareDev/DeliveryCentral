<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionCentral;

class ConfiguracionCentralSeeder extends Seeder
{
    public function run(): void
    {
        ConfiguracionCentral::updateOrCreate(
            ['key' => 'comision_por_entrega'],
            ['value' => '0.50']
        );
    }
}
