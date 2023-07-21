<?php

namespace App\Http\Controllers\Informes;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Sistema\FacDocumentos;

class DocumentoController extends Controller
{
    public function index ()
    {
        return view('pages.contabilidad.documento.documento-view');
    }

    public function generate (Request $request)
    {
        // $id_comprobante = $request->get('id_comprobante');
        // $fecha_hasta = $request->get('fecha_hasta');
        // $consecutivo = $request->get('consecutivo');

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

        $FacDocumentos = FacDocumentos::skip($start)
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') fecha_edicion")
            )
            ->with('comprobante')
            ->take($rowperpage);

        if($columnName){
            // $FacDocumentos->orderBy($columnName,$columnSortOrder);
        }

        if($request->has('id_comprobante') && $request->get('id_comprobante')) {
            $FacDocumentos->where('id_comprobante', $request->get('id_comprobante'));
        }

        if($request->has('fecha_hasta') && $request->get('fecha_hasta')) {
            $FacDocumentos->where('fecha_manual', $request->get('fecha_manual'));
        }

        if($request->has('consecutivo') && $request->get('consecutivo')) {
            $FacDocumentos->where('consecutivo', $request->get('consecutivo'));
        }

        if($searchValue) {
            // $FacDocumentos->whereHas('comprobante', function ($query) use ($searchValue) {
            //         $query->where('codigo', 'like', '%' .$searchValue . '%')
            //         ->orWhere('nombre', 'like', '%' .$searchValue . '%');
            //     })
            //     ->orWhere('fecha_manual', 'like', '%' .$searchValue . '%')
            //     ->orWhere('consecutivo', 'like', '%' .$searchValue . '%')
            //     ->orWhere('debito', 'like', '%' .$searchValue . '%')
            //     ->orWhere('credito', 'like', '%' .$searchValue . '%');
        }

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $FacDocumentos->count(),
            'iTotalDisplayRecords' => $FacDocumentos->count(),
            'data' => $FacDocumentos->get(),
            'perPage' => $rowperpage,
            'message'=> 'Documento generado con exito!'
        ]);

    }
}

