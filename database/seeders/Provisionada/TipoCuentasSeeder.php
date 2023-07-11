<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;

class TipoCuentasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('tipo_cuentas')->truncate();

        \DB::table('tipo_cuentas')->insert(array (
            0 =>
            array (
                'id' => 1,
                'nombre' => 'Gastos - Costos',
            ),
            1 =>
            array (
                'id' => 2,
                'nombre' => 'Caja - Bancos',
            ),
            2 =>
            array (
                'id' => 3,
                'nombre' => 'CxC',
            ),
            3 =>
            array (
                'id' => 4,
                'nombre' => 'CxP',
            ),
            4 =>
            array (
                'id' => 5,
                'nombre' => 'Inventario',
            ),
            5 =>
            array (
                'id' => 6,
                'nombre' => 'Ventas',
            ),
            6 =>
            array (
                'id' => 7,
                'nombre' => 'Anticipos Proveedores / Por Cobrar ',
            ),
            7 =>
            array (
                'id' => 8,
                'nombre' => 'Anticipos Clientes / Por Pagar',
            ),
            8 =>
            array (
                'id' => 9,
                'nombre' => 'IVA en compras',
            ),
            9 =>
            array (
                'id' => 10,
                'nombre' => 'Descuento en compras',
            ),
            10 =>
            array (
                'id' => 11,
                'nombre' => 'Descuento en ventas',
            ),
            11 =>
            array (
                'id' => 12,
                'nombre' => 'Retención en compras',
            ),
            12 =>
            array (
                'id' => 13,
                'nombre' => 'Retención en ventas',
            ),
            13 =>
            array (
                'id' => 16,
                'nombre' => 'IVA en ventas',
            ),
        ));
    }
}
