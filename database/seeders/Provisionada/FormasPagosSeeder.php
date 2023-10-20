<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class FormasPagosSeeder extends Seeder
{
    public function run()
    {
        \DB::table('fac_formas_pagos')->truncate();

        \DB::table('fac_formas_pagos')->insert([
            [
                'id' => 1,
                'id_cuenta' => 4,
                'id_tipo_formas_pago' => 10,
                'nombre' => 'EFECTIVO'
            ],
            [
                'id' => 2,
                'id_cuenta' => 9,
                'id_tipo_formas_pago' => 1,
                'nombre' => 'CRÉDITO'
            ],
            [
                'id' => 3,
                'id_cuenta' => 70,
                'id_tipo_formas_pago' => 1,
                'nombre' => 'ANTICIPOS'
            ],
        ]);
    }
}
