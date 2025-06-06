<?php

namespace Database\Seeders\Provisionada;

use Illuminate\Database\Seeder;
use DB;

class NomPeriodosSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		DB::table('nom_periodos')->truncate();

		DB::table('nom_periodos')->insert([
			[
				'nombre' => 'Quincenal',
				'dias_salario' => 15,
				'horas_dia' => 8,
				'tipo_dia_pago' => 0,
				'periodo_dias_ordinales' => '15,31'
			],
			[
				'nombre' => 'Mensual',
				'dias_salario' => 30,
				'horas_dia' => 8,
				'tipo_dia_pago' => 0,
				'periodo_dias_ordinales' => '31'
			],
		]);
	}
}
