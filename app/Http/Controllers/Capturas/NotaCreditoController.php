<?php

namespace App\Http\Controllers\Capturas;

use DB;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
//MODELS
use App\Models\Sistema\FacFactura;
use App\Models\Sistema\FacFacturaDetalle;

class NotaCreditoController extends Controller
{
    use BegConsecutiveTrait;

    protected $messages = null;

    public function __construct(Request $request)
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
	}

    public function index (Request $request)
    {
        return view('pages.capturas.nota_credito.nota_credito-view');
    }

    public function detalleFactura (Request $request)
    {
        try {
            
            $facturaDetalles = FacFacturaDetalle::withValoresDevueltos()
                ->where('fac_venta_detalles.id_venta', $request->get('id'))
                ->with('producto')
                ->get();

            return response()->json([
                "success" => true,
                "data" => $facturaDetalles,
                "message" => "Factura detalle consultada con exito!"
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }
}
