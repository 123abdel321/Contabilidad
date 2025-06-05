<?php

namespace Database\Seeders\Provisionada;

use DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\Nomina\NomAdministradoras;

class AdministradorasSeeder extends Seeder
{
    public $tipoAdministradora = [
        'EPS' => 0,
        'AFP' => 1,
        'ARL' => 2,
        'CCF' => 3
    ];

    public function run()
    {
        $urlFile = Storage::disk('do_spaces')->url('import/nom_administradoras.csv');
        $csvFile = fopen($urlFile, "r");

        $dataAdministradoras = [];

        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            $dataDocumento = explode("-", $data[2]);
			$numero_documento = count($dataDocumento) > 1 ? $dataDocumento[0] : $data[2];

            $nit = Nits::where('numero_documento', $numero_documento)->first();

            if (!$nit) {
                $nit = Nits::create([
                    'numero_documento' => count($dataDocumento) > 1 ? $dataDocumento[0] : $data[2],
                    'digito_verificacion' => count($dataDocumento) > 1 ? $dataDocumento[1] : NULL,
                    'razon_social' => $data[3],
                    'id_tipo_documento' => 6,
                    'id_ciudad' => 1,
                    'id_departamento' => 1,
                    'id_pais' => 53,
                    'tipo_contribuyente' => 1,
                    'direccion' => 1,
                    'email_recepcion_factura_electronica' => 1,
                    'tipo_cuenta_banco' => 1,
                    'no_calcular_iva' => 1
                ]);
            }

            array_push($dataAdministradoras, $this->dataAdministradoras($data, $nit->id));
        }

        if($dataAdministradoras){
			DB::table('nom_administradoras')->truncate();
			DB::table('nom_administradoras')->insert($dataAdministradoras);
		}
    }

    private function dataAdministradoras($data, $id_nit)
	{
		return [
			'tipo' => $this->tipoAdministradora[$data[0]],
			'codigo' => $data[1],
			'id_nit' => $id_nit,
			'descripcion' => $data[4]
		];
	}
}