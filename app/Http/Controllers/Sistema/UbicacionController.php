<?php

namespace App\Http\Controllers\Sistema;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Empresas\Paises;
use App\Models\Empresas\Ciudades;
use App\Models\Empresas\Departamentos;

class UbicacionController extends Controller
{

    public function getPais (Request $request)
    {
		$query = $request->get("q");
		$queryModel = Paises::whereNotNull("id");
        
		if($query){
			$queryModel->where("nombre","LIKE","%".$query."%");
		}

		return $queryModel->paginate(30);
	}

	public function getDepartamento (Request $request)
    {
		$query = $request->get("q");
		$queryModel = Departamentos::whereNotNull("id");

		if($query){
			$queryModel->where("nombre","LIKE","%".$query."%");
		}

		if($request->get("id_pais")){
			$queryModel->where("id_pais",$request->get("id_pais"));
		}

		return $queryModel->paginate(30);
	}

	public function getCiudad (Request $request)
    {
		$query = $request->get("q");
		$queryModel = Ciudades::whereNotNull("id")->select(
			'id',
			'id_pais',
			'id_departamento',
			'codigo',
			'indicativo',
			'nombre',
			'nombre_completo',
			\DB::raw("nombre_completo as text")
		);

		if($query){
			$queryModel->where("nombre","LIKE","%".$query."%");
		}

		if($request->get("id_departamento")){
			$queryModel->where("id_departamento",$request->get("id_departamento"));
		}

		if($request->get("id_pais")){
			$queryModel->where("id_pais",$request->get("id_pais"));
		}

		return $queryModel->paginate(30);
	}

}
