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
                'nombre' => 'PRINCIPAL',
                'inventario' => '1',
                'id_cuenta_venta' => '72',
                'id_cuenta_venta_retencion' => '',
                'id_cuenta_venta_devolucion' => '',
                'id_cuenta_venta_iva' => '75',
                'id_cuenta_venta_descuento' => '',
                'id_cuenta_venta_devolucion_iva' => '',
                'id_cuenta_compra' => '78',
                'id_cuenta_compra_retencion' => '18',
                'id_cuenta_compra_devolucion' => '',
                'id_cuenta_compra_iva' => '76',
                'id_cuenta_compra_descuento' => '',
                'id_cuenta_compra_devolucion_iva' => '',
                'id_cuenta_inventario' => '78',
                'id_cuenta_costos' => '82'
            ]
        ]);
    }
}
