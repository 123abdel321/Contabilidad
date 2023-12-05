<?php

namespace App\Http\Controllers\Informes;

use DB;
use Illuminate\Http\Request;
use App\Helpers\Printers\DocumentosPdf;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacDocumentos;

class DocumentoController extends Controller
{
    public function index ()
    {
        return view('pages.contabilidad.documento.documento-view');
    }

    public function generate (Request $request)
    {
        $FacDocumentos = FacDocumentos::select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') fecha_edicion")
            )
            ->with('comprobante');

        if($request->has('id_comprobante') && $request->get('id_comprobante')) {
            $FacDocumentos->where('id_comprobante', $request->get('id_comprobante'));
        }

        if($request->has('fecha_hasta') && $request->get('fecha_hasta')) {
            $FacDocumentos->whereDate('fecha_manual', '<=', $request->get('fecha_hasta'));
        }

        if($request->has('fecha_desde') && $request->get('fecha_desde')) {
            $FacDocumentos->whereDate('fecha_manual', '>=', $request->get('fecha_desde'));
        }
        
        if($request->has('consecutivo') && $request->get('consecutivo')) {
            $FacDocumentos->where('consecutivo', $request->get('consecutivo'));
        }
        
        if($request->has('tipo_factura') && $request->get('tipo_factura') == '1') {
            $FacDocumentos->where('anulado', 1);
        }
        
        return response()->json($FacDocumentos->paginate());

    }

    public function showPdf(Request $request, $id)
    {
        // return $request->user();

        $factura = FacDocumentos::whereId($id)->first();

        if(!$factura) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'La factura no existe'
            ]);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        // $data = (new DocumentosPdf($empresa, $factura))->buildPdf()->getData();
        // return view('pdf.facturacion.documentos', $data);
 
        return (new DocumentosPdf($empresa, $factura))
            ->buildPdf()
            ->showPdf();
    }

}

