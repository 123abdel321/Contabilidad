<?php

namespace App\Http\Controllers\Tablas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\TipoDocumentos;
use App\Models\Sistema\DocumentosGeneral;

class NitController extends Controller
{
    public function index ()
    {
        return view('pages.tablas.nits.nits-view');
    }

    public function generate (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = 15; // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value
        $totalRecordswithFilter = Nits::select('count(*) as allcount');

        $nits = Nits::skip($start)
            ->with('tipo_documento')
            ->take($rowperpage);

        if($columnName){
            $nits->orderBy($columnName,$columnSortOrder);
        }

        if($searchValue) {
            $nits->where('primer_apellido', 'like', '%' .$searchValue . '%')
                ->orWhere('segundo_apellido', 'like', '%' .$searchValue . '%')
                ->orWhere('primer_nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('otros_nombres', 'like', '%' .$searchValue . '%')
                ->orWhere('razon_social', 'like', '%' .$searchValue . '%');
            $totalRecordswithFilter->where('primer_apellido', 'like', '%' .$searchValue . '%')
                ->orWhere('segundo_apellido', 'like', '%' .$searchValue . '%')
                ->orWhere('primer_nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('otros_nombres', 'like', '%' .$searchValue . '%')
                ->orWhere('razon_social', 'like', '%' .$searchValue . '%');
        }

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'data' => $nits->get(),
            'perPage' => $rowperpage,
            'iTotalRecords' => $totalRecordswithFilter->count(),
            'iTotalDisplayRecords' => $totalRecordswithFilter->count(),
            'message'=> 'Comprobante generado con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $nitsExist = Nits::where('numero_documento', $request->get('numero_documento'));
        if($nitsExist->count() > 0){
            return response()->json([
                'success'=>	false,
                'data' => '',
                'message'=> 'El numero de documento ya esta siendo usado!'
            ]);
        }

        $nit = Nits::create([
            'id_tipo_documento' => $request->get('id_tipo_documento'),
            'numero_documento' => $request->get('numero_documento'),
            'tipo_contribuyente' => $request->get('tipo_contribuyente'),
            'primer_apellido' => $request->get('primer_apellido'),
            'segundo_apellido' => $request->get('segundo_apellido'),
            'primer_nombre' => $request->get('primer_nombre'),
            'otros_nombres' => $request->get('otros_nombres'),
            'razon_social' => $request->get('razon_social'),
            'direccion' => $request->get('direccion'),
            'email' => $request->get('email'),
        ]);

        return response()->json([
            'success'=>	true,
            'data' => $nit,
            'message'=> 'Nit creado con exito!'
        ]);
    }

    public function update (Request $request)
    {
        $nitActual = Nits::find($request->get('id'));
        if($nitActual->numero_documento != $request->get('numero_documento')){
            $nitsExist = Nits::where('numero_documento', $request->get('numero_documento'));
            if($nitsExist->count() > 0){
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'El numero de documento ya esta siendo usado!'
                ]);
            }
        }

        Nits::where('id', $request->get('id'))
            ->update([
                'id_tipo_documento' => $request->get('id_tipo_documento'),
                'numero_documento' => $request->get('numero_documento'),
                'tipo_contribuyente' => $request->get('tipo_contribuyente'),
                'primer_apellido' => $request->get('primer_apellido'),
                'segundo_apellido' => $request->get('segundo_apellido'),
                'primer_nombre' => $request->get('primer_nombre'),
                'otros_nombres' => $request->get('otros_nombres'),
                'razon_social' => $request->get('razon_social'),
                'direccion' => $request->get('direccion'),
                'email' => $request->get('email'),
            ]);

        $nits = Nits::where('id', $request->get('id'))->with('tipo_documento')->first();

        return response()->json([
            'success'=>	true,
            'data' => $nits,
            'message'=> 'Nit actualizado con exito!'
        ]);
    }

    public function delete (Request $request)
    {
        $documentos = DocumentosGeneral::where('id_nit', $request->get('id'));
        if($documentos->count() > 0) {
            return response()->json([
                'success'=>	false,
                'data' => '',
                'message'=> 'No se puede eliminar una cuenta usado por los documentos!'
            ]);
        }

        Nits::where('id', $request->get('id'))->delete();

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Nit eliminado con exito!'
        ]);
    }

    public function comboTipoDocumento(Request $request)
    {
        $tipoDocumento = TipoDocumentos::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $tipoDocumento->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $tipoDocumento->paginate(40);
    }

    public function comboNit(Request $request)
    {

        $nits = Nits::select(
            'id',
            'id_tipo_documento',
            'id_ciudad',
            'primer_nombre',
            \DB::raw('numero_documento AS segundo_nombre'),
            'primer_apellido',
            'segundo_apellido',
            'email',
            \DB::raw('telefono_1 AS telefono'),
            \DB::raw("(CASE
					WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN CONCAT(numero_documento, ' - ', razon_social)
					WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT( numero_documento, ' - ', CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido))
					ELSE NULL
				END) AS text")
        );

        if ($request->get("q")) {
            $nits->where('primer_apellido', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('segundo_apellido', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('primer_nombre', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('otros_nombres', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('razon_social', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('email', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('numero_documento', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $nits->paginate(40);
    }

}
