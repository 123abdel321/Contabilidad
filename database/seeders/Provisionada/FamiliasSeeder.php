<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class FamiliasSeeder extends Seeder
{
    public function run()
    {
        \DB::table('fac_familias')->truncate();

        \DB::table('fac_familias')->insert([
            [
                'id' => 1,
                'codigo' => '01',
                'nombre' => 'INVENTARIOS',
                'inventario' => '1',
                'id_cuenta_venta' => '2795',
                'id_cuenta_venta_retencion' => '2796',
                'id_cuenta_venta_devolucion' => '2798',
                'id_cuenta_venta_iva' => '907',
                'id_cuenta_venta_descuento' => '2111',
                'id_cuenta_venta_devolucion_iva' => '924',
                'id_cuenta_compra' => '298',
                'id_cuenta_compra_retencion' => '2774',
                'id_cuenta_compra_devolucion' => '298',
                'id_cuenta_compra_iva' => '921',
                'id_cuenta_compra_descuento' => '2798',
                'id_cuenta_compra_devolucion_iva' => '2801',
                'id_cuenta_inventario' => '298',
                'id_cuenta_costos' => '2802'
            ],
            [
                'id' => 2,
                'codigo' => '50',
                'nombre' => 'SERVICIOS',
                'inventario' => '0',
                'id_cuenta_venta' => '2795',
                'id_cuenta_venta_retencion' => '2796',
                'id_cuenta_venta_devolucion' => '2798',
                'id_cuenta_venta_iva' => '907',
                'id_cuenta_venta_descuento' => '2111',
                'id_cuenta_venta_devolucion_iva' => '924',
                'id_cuenta_compra' => '',
                'id_cuenta_compra_retencion' => '',
                'id_cuenta_compra_devolucion' => '',
                'id_cuenta_compra_iva' => '',
                'id_cuenta_compra_descuento' => '',
                'id_cuenta_compra_devolucion_iva' => '',
                'id_cuenta_inventario' => '',
                'id_cuenta_costos' => ''
            ]
        ]);
    }
}
