<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class PlanCuentasTiposSeeder extends Seeder
{
    public function run()
    {
        \DB::table('plan_cuentas_tipos')->truncate();

        \DB::table('plan_cuentas_tipos')->insert([
            'id' => 1,
            'id_cuenta' => 67,
            'id_tipo_cuenta' => 8
        ]);
    }
}
