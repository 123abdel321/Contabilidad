<?php

namespace App\Http\Controllers\Tablas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\DocumentosGeneral;

class ComprobantesController extends Controller
{
    public function index ()
    {
        return view('pages.tablas.comprobantes.comprobantes-view');
    }

    public function generate ()
    {
        return response()->json([
            'success'=>	true,
            'data' => Comprobantes::get(),
            'message'=> 'Comprobante generado con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $comprobante = Comprobantes::create([
            'codigo' => $request->get('codigo'),
            'nombre' => $request->get('nombre'),
            'tipo_comprobante' => $request->get('tipo_comprobante'),
            'tipo_consecutivo' => $request->get('tipo_consecutivo'),
            'consecutivo_siguiente' => $request->get('consecutivo_siguiente'),
        ]);

        return response()->json([
            'success'=>	true,
            'data' => $comprobante,
            'message'=> 'Comprobante creado con exito!'
        ]);
    }

    public function update (Request $request)
    {
        $comprobante =  Comprobantes::where('id', $request->get('id'))
            ->update([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'tipo_comprobante' => $request->get('tipo_comprobante'),
                'tipo_consecutivo' => $request->get('tipo_consecutivo'),
                'consecutivo_siguiente' => $request->get('consecutivo_siguiente'),
            ]);

        return response()->json([
            'success'=>	true,
            'data' => $comprobante,
            'message'=> 'Comprobante creado con exito!'
        ]);
    }

    public function delete (Request $request)
    {
        $documentos = DocumentosGeneral::where('id_comprobante', $request->get('id'));
        if($documentos->count() > 0) {
            return response()->json([
                'success'=>	false,
                'data' => '',
                'message'=> 'No se puede eliminar un comprobante usado por los documentos!'
            ]);
        }

        Comprobantes::where('id', $request->get('id'))->delete();

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Comprobante eliminado con exito!'
        ]);
    }

    public function comboComprobante(Request $request)
    {
        $comprobantes = Comprobantes::select(
            'id',
            'codigo',
            'nombre',
            'consecutivo_siguiente',
            \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $comprobantes->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $comprobantes->paginate(20);
    }
}
