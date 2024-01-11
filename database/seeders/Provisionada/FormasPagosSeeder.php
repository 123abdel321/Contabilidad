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
                'nombre' => 'CAJA EFECTIVO'
            ],
            [
                'id' => 2,
                'id_cuenta' => 2792,
                'id_tipo_formas_pago' => 31,
                'nombre' => 'TRANSFERENCIA'
            ],
            [
                'id' => 3,
                'id_cuenta' => 150,
                'id_tipo_formas_pago' => 1,
                'nombre' => 'VENTAS CREDITO'
            ],
            [
                'id' => 4,
                'id_cuenta' => 1081,
                'id_tipo_formas_pago' => 1,
                'nombre' => 'ANTICIPOS CLIENTES'
            ],
            [
                'id' => 5,
                'id_cuenta' => 802,
                'id_tipo_formas_pago' => 1,
                'nombre' => 'COMPRAS CREDITO'
            ],
            [
                'id' => 6,
                'id_cuenta' => 272,
                'id_tipo_formas_pago' => 1,
                'nombre' => 'ANTICIPOS PROVEEDORES'
            ],
        ]);
    }
}
