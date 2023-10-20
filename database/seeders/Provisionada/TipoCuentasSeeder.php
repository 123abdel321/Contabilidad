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

        \DB::table('tipo_cuentas')->insert([
            [
                'id' => 1,
                'nombre' => 'Gastos - Costos',
            ],
            [
                'id' => 2,
                'nombre' => 'Caja - Bancos',
            ],
            [
                'id' => 3,
                'nombre' => 'CxC',
            ],
            [
                'id' => 4,
                'nombre' => 'CxP',
            ],
            [
                'id' => 5,
                'nombre' => 'Inventario',
            ],
            [
                'id' => 6,
                'nombre' => 'Ventas',
            ],
            [
                'id' => 7,
                'nombre' => 'Anticipos Proveedores / Por Cobrar ',
            ],
            [
                'id' => 8,
                'nombre' => 'Anticipos Clientes / Por Pagar',
            ],
            [
                'id' => 9,
                'nombre' => 'IVA en compras',
            ],
            [
                'id' => 10,
                'nombre' => 'Descuento en compras',
            ],
            [
                'id' => 11,
                'nombre' => 'Descuento en ventas',
            ],
            [
                'id' => 12,
                'nombre' => 'Retención en compras',
            ],
            [
                'id' => 13,
                'nombre' => 'Retención en ventas',
            ],
            [
                'id' => 16,
                'nombre' => 'IVA en ventas',
            ],
        ]);
    }
}
