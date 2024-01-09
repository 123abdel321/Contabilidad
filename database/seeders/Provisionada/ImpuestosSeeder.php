<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ImpuestosTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('impuestos')->delete();
        
        \DB::table('impuestos')->insert(array (
            0 => 
            array (
                'id' => 1,
                'id_tipo_impuesto' => 1,
                'nombre' => 'IVA',
                'base' => '0.00',
                'porcentaje' => '16.00',
                'total_uvt' => '0.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'id_tipo_impuesto' => 1,
                'nombre' => 'IVA',
                'base' => '0.00',
                'porcentaje' => '19.00',
                'total_uvt' => '0.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'id_tipo_impuesto' => 1,
                'nombre' => 'IVA',
                'base' => '0.00',
                'porcentaje' => '5.00',
                'total_uvt' => '0.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Compras generales (declarantes renta)',
                'base' => '1270755.00',
                'porcentaje' => '2.50',
                'total_uvt' => '27.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Compras generales (no declarantes renta)',
                'base' => '1270755.00',
                'porcentaje' => '3.50',
                'total_uvt' => '27.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Servicios generales (declarantes renta)',
                'base' => '188260.00',
                'porcentaje' => '4.00',
                'total_uvt' => '4.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            6 => 
            array (
                'id' => 7,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Servicios generales (no declarantes renta)',
                'base' => '188260.00',
                'porcentaje' => '6.00',
                'total_uvt' => '4.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            7 => 
            array (
                'id' => 8,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Honorarios y comisiones (personas jurídicas)',
                'base' => '0.00',
                'porcentaje' => '11.00',
                'total_uvt' => '0.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            8 => 
            array (
                'id' => 9,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Honorarios y comisiones (no declarantes renta)',
                'base' => '0.00',
                'porcentaje' => '10.00',
                'total_uvt' => '0.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            9 => 
            array (
                'id' => 10,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Arrendamiento de bienes muebles',
                'base' => '0.00',
                'porcentaje' => '4.00',
                'total_uvt' => '0.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            10 => 
            array (
                'id' => 11,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Retencion en la fuente (declarantes y no declarantes)',
                'base' => '1270755.00',
                'porcentaje' => '3.50',
                'total_uvt' => '27.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            11 => 
            array (
                'id' => 12,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Contratos de construcción y urbanización',
                'base' => '1270755.00',
                'porcentaje' => '2.00',
                'total_uvt' => '27.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            12 => 
            array (
                'id' => 13,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Servicios de transporte de carga',
                'base' => '188260.00',
                'porcentaje' => '1.00',
                'total_uvt' => '4.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            13 => 
            array (
                'id' => 14,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Servicios de transporte nacional de pasajeros por vía terrestre',
                'base' => '1270755.00',
                'porcentaje' => '3.50',
                'total_uvt' => '27.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            14 => 
            array (
                'id' => 15,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Servicios de hoteles y restaurantes',
                'base' => '188260.00',
                'porcentaje' => '3.50',
                'total_uvt' => '4.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            15 => 
            array (
                'id' => 16,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Intereses o rendimientos financieros en general',
                'base' => '0.00',
                'porcentaje' => '7.00',
                'total_uvt' => '0.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            16 => 
            array (
                'id' => 17,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Compras de combustibles derivados del petróleo',
                'base' => '0.00',
                'porcentaje' => '0.10',
                'total_uvt' => '0.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            17 => 
            array (
                'id' => 18,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Compras con tarjeta débito o crédito',
                'base' => '0.00',
                'porcentaje' => '1.50',
                'total_uvt' => '0.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            18 => 
            array (
                'id' => 19,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Servicios prestados por empresas de vigilancia y aseo (sobre AIU)',
                'base' => '188260.00',
                'porcentaje' => '2.00',
                'total_uvt' => '4.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            19 => 
            array (
                'id' => 20,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Servicios integrales de salud prestados por IPS',
                'base' => '188260.00',
                'porcentaje' => '2.00',
                'total_uvt' => '4.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            20 => 
            array (
                'id' => 21,
                'id_tipo_impuesto' => 6,
                'nombre' => 'Servicios prestados por empresas de servicios temporales (sobre AIU)',
                'base' => '188260.00',
                'porcentaje' => '1.00',
                'total_uvt' => '4.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            21 => 
            array (
                'id' => 22,
                'id_tipo_impuesto' => 1,
                'nombre' => 'IVA',
                'base' => '0.00',
                'porcentaje' => '0.00',
                'total_uvt' => '0.00',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}