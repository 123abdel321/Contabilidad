<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class ResolucionesSeeder extends Seeder
{
    public function run()
    {
        \DB::table('fac_resoluciones')->truncate();

        \DB::table('fac_resoluciones')->insert([
            [
                'id' => 1,
                'id_comprobante' => 1,
                'nombre' => 'FACTURACION POS',
                'prefijo' => 'F-POS',
                'consecutivo' => '1',
                'numero_resolucion' => '1',
                'tipo_impresion' => '0',
                'tipo_resolucion' => '0',
                'fecha' => '2024-12-31',
                'vigencia' => '12',
                'consecutivo_desde' => '1',
                'consecutivo_hasta' => '1000',
            ]
        ]);
    }
}
