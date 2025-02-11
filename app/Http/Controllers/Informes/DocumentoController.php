<?php

namespace App\Http\Controllers\Informes;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\ConGastos;
use App\Models\Sistema\ConRecibos;
use App\Models\Sistema\FacCompras;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\FacDocumentos;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
// PDFS
use App\Helpers\Printers\PagosPdf;
use App\Helpers\Printers\GastosPdf;
use App\Helpers\Printers\VentasPdf;
use App\Helpers\Printers\RecibosPdf;
use App\Helpers\Printers\ComprasPdf;
use App\Helpers\Printers\DocumentosPdf;

class DocumentoController extends Controller
{
    public function index ()
    {
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first();

        $data = [
            'ubicacion_maximoph' => $ubicacion_maximoph && $ubicacion_maximoph->valor ? $ubicacion_maximoph->valor : '0',
        ];
        
        return view('pages.contabilidad.documento.documento-view', $data);
    }

    public function generate (Request $request)
    {
        
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = 20;
        
        $FacDocumentos = FacDocumentos::select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') fecha_edicion")
            )
            ->with('comprobante', 'nit');

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

        $FacDocumentosPaginate = $FacDocumentos->skip($start)
            ->take($rowperpage);

        $credito = $FacDocumentosPaginate->sum('credito');
        $debito = $FacDocumentosPaginate->sum('debito');

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $FacDocumentos->count(),
            'iTotalDisplayRecords' => $FacDocumentos->count(),
            'data' => $FacDocumentosPaginate->get(),
            'perPage' => $rowperpage,
            'total' => [
                'credito' => $credito,
                'debito' => $debito,
                'diferencia' => $credito - $debito
            ],
            'message'=> 'Documentos capturados generados con exito!'
        ]);
        
        return response()->json($FacDocumentos->paginate(50));

    }

    public function showPdf(Request $request, $id)
    {
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

    public function showGeneralPdf(Request $request, $id_comprobante, $consecutivo)
    {
        $comprobante = Comprobantes::where('id', $id_comprobante)->first();
        if (!$comprobante) {
            logger()->critical("Error showGeneralPdf: el comprobante id: {$id_comprobante} no existe; consecutivo: {$consecutivo}");
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> "El comprobante: {$id_comprobante} no existe"
            ]);
        }

        $documento = DocumentosGeneral::with('comprobante')
            ->where('id_comprobante', $id_comprobante)
            ->where('consecutivo', $consecutivo)
            ->first();

        if (!$documento) {
            logger()->critical("Error showGeneralPdf: el documento id: {$documento->id} no tiene cabezas para imprimir;");
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> "El documento: {$documento->id} no tiene cabezas para imprimir"
            ]);
        }
        
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        if ($documento->tipo_comprobante == Comprobantes::TIPO_INGRESOS) {
            $recibo = ConRecibos::where('id', $documento->relation_id)->first();
            if ($recibo) {
                return (new RecibosPdf($empresa, $recibo))
                    ->buildPdf()
                    ->showPdf();
            }
        }
        if ($comprobante->tipo_comprobante == Comprobantes::TIPO_VENTAS) {
            $venta = FacVentas::where('id', $documento->relation_id)->first();;
            if ($venta) {
                // $data = (new VentasPdf($empresa, $venta))->buildPdf()->getData();
                return (new VentasPdf($empresa, $venta))
                    ->buildPdf()
                    ->showPdf();
            }
        }
        if ($comprobante->tipo_comprobante == Comprobantes::TIPO_COMPRAS) {
            $compra = FacCompras::with('comprobante')
                ->where('id_comprobante', $id_comprobante)
                ->where('consecutivo', $consecutivo)
                ->orderBy('id', 'DESC')
                ->first();

            if ($compra) {
                // $data = (new ComprasPdf($empresa, $compra))->buildPdf()->getData();
                return (new ComprasPdf($empresa, $compra))
                    ->buildPdf()
                    ->showPdf();
            }
        }
        if ($comprobante->tipo_comprobante == Comprobantes::TIPO_EGRESOS) {
            $gasto = ConGastos::where('id', $documento->relation_id)->first();

            if ($gasto) {
                // $data = (new GastosPdf($empresa, $gasto))->buildPdf()->getData();
                return (new GastosPdf($empresa, $gasto))
                    ->buildPdf()
                    ->showPdf();
            }
        }

        $facDocumento = FacDocumentos::where('id', $documento->relation_id)->first();

        if (!$facDocumento) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'La factura no existe'
            ]);
        }

        if (count($facDocumento->documentos)) {
            return (new DocumentosPdf($empresa, $facDocumento))
                ->buildPdf()
                ->showPdf();
        }

        return response()->json([
            'success'=>	false,
            'data' => [],
            'message'=> 'La factura no existe'
        ]);
    }

    public function showPdfPublic(Request $request)
    {
        $token_db = base64_decode($request->get('token_db'));
        $empresa = Empresa::where('token_db', $token_db)->first();

		Config::set('database.connections.sam.database', $token_db);
        
        $factura = FacDocumentos::whereId($id)->first();

        if(!$factura) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'La factura no existe'
            ]);
        }

        return (new DocumentosPdf($empresa, $factura))
            ->buildPdf()
            ->showPdf();
    }

}

