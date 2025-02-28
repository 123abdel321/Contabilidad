<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Carbon\Carbon;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Helpers\Printers\GastosPdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConGastos;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\CentroCostos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\ConGastoPagos;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\ConGastoDetalles;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\ConConceptoGastos;
use App\Models\Sistema\DocumentosGeneral;

class GastosController extends Controller
{
    use BegConsecutiveTrait;

    protected $tipoRetencion = 'cuenta_retencion';
    protected $retenciones = [];
    protected $messages = null;
    protected $proveedor = null;
    protected $cuentasContables = [
        "cuenta_gasto" => ["valor" => "subtotal"],
        // "cuenta_descento" => ["valor" => "descuento_valor"],
        "cuenta_iva" => ["valor" => "iva_valor"],
        "cuenta_reteica" => ["valor" => "rete_ica_valor"],
    ];
    protected $totalesFactura = [
        'base_retencion' => 0,
        'porcentaje_rete_fuente' => 0,
        'porcentaje_rete_ica' => 0,
        'id_cuenta_rete_fuente' => null,
        'subtotal' => 0,
        'total_iva' => 0,
        'total_no_iva' => 0,
        'total_rete_fuente' => 0,
        'total_rete_ica' => 0,
        'total_descuento' => 0,
        'total_gasto' => 0,
        'total_pagado' => 0
    ];

    public function __construct()
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

    public function index ()
    {
        $porcentaje_iva_aiu = VariablesEntorno::where('nombre', 'porcentaje_iva_aiu')->first();

        $data = [
            'porcentaje_iva_aiu' => $porcentaje_iva_aiu ? $porcentaje_iva_aiu->valor : 0,
            'comprobantes' => Comprobantes::where('tipo_comprobante', Comprobantes::TIPO_GASTOS)->get(),
            'centro_costos' => CentroCostos::get(),
        ];

        return view('pages.capturas.gasto.gasto-view', $data);
    }

    public function create (Request $request)
    {
        $rules = [
            'id_proveedor' => 'required|exists:sam.nits,id',
            'fecha_manual' => 'required|date',
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
            'id_centro_costos' => 'required|exists:sam.centro_costos,id',
            'consecutivo' => 'required',
            'gastos' => 'array|required',
            'gastos.*.id_concepto' => 'required|exists:sam.con_concepto_gastos,id',
            'gastos.*.valor_gasto' => 'required',
            'gastos.*.descuento_gasto' => 'nullable',
            'gastos.*.porcentaje_descuento_gasto' => 'nullable',
            'gastos.*.total_valor_gasto' => 'required',
            'gastos.*.observacion' => 'nullable',
            'pagos' => 'array|required',
            'pagos.*.id' => 'required|exists:sam.fac_formas_pagos,id',
            'pagos.*.valor' => 'required',
        ];
        
        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        $porcentaje_iva_aiu = VariablesEntorno::where('nombre', 'porcentaje_iva_aiu')->first();
        $porcentaje_iva_aiu = $porcentaje_iva_aiu ? $porcentaje_iva_aiu->valor : 0;
        
        try {
            DB::connection('sam')->beginTransaction();
            
            $this->proveedor = $this->findProveedor($request->get('id_proveedor'));
            if (!$this->proveedor->declarante) $this->tipoRetencion = 'cuenta_retencion_declarante';

            if ($request->get('editing_gasto')) {

                $gasto = ConGastos::where('id_comprobante', $request->get('id_comprobante'))
                    ->where('consecutivo', $request->get('consecutivo'))
                    ->orderBy('id', 'DESC')
                    ->first();

                if ($gasto) {
                    $gasto->detalles()->delete();
                    $gasto->pagos()->delete();
                    $gasto->delete();
                }
            } else {
                $consecutivo = $this->getNextConsecutive($request->get('id_comprobante'), $request->get('fecha_manual'));
                $request->request->add(['consecutivo' => $consecutivo]);
            }

            //CREAR FACTURA GASTO
            $gasto = $this->createFacturaGasto($request);

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE GASTOS
            $documentoGeneral = new Documento(
                $request->get('id_comprobante'),
                $gasto,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            $comprobanteGasto = Comprobantes::where('id', $request->get('id_comprobante'))->first();

            //AGREGAR MOVIMIENTO DE CUENTAS POR GASTO
            foreach ($request->get('gastos') as $movimiento) {
                $movimiento = (object)$movimiento;
                
                $conceptoGasto = ConConceptoGastos::with(
                    'cuenta_gasto',
                    'cuenta_reteica',
                    'cuenta_descento',
                    'cuenta_iva.impuesto',
                    'cuenta_retencion.impuesto',
                    'cuenta_retencion_declarante.impuesto',
                )->find($movimiento->id_concepto);

                $porcentajeRetencion = $this->totalesFactura['porcentaje_rete_fuente'];
                $porcentajeReteIca = $this->totalesFactura['porcentaje_rete_ica'];
                $porcentajeIva = $conceptoGasto->cuenta_iva ? floatval($conceptoGasto->cuenta_iva->impuesto->porcentaje) : 0;
                $subtotalGasto = $movimiento->valor_gasto - $movimiento->descuento_gasto;
                $baseAIU = 0;

                if (floatval($this->proveedor->porcentaje_aiu)) {

                    $ivaGasto = 0;
                    $baseAIU = $subtotalGasto * ($this->proveedor->porcentaje_aiu / 100);

                    if ($porcentajeIva) {
                        $ivaGasto = $porcentajeIva ? $baseAIU * ($porcentajeIva / 100) : 0;
                    } else if ($porcentaje_iva_aiu) {
                        $ivaGasto = $baseAIU * ($porcentaje_iva_aiu / 100);
                    }

                    $retencionGasto = $porcentajeRetencion ? ($baseAIU - $movimiento->no_valor_iva) * ($porcentajeRetencion / 100) : 0;
                    $reteIcaGasto = $porcentajeReteIca ? $baseAIU * ($porcentajeReteIca / 1000) : 0;
                    $totalGasto = ($subtotalGasto + $ivaGasto) - ($retencionGasto + $reteIcaGasto);
                    
                    $subtotalGasto+= $ivaGasto;
                } else {
                    $ivaGasto = $porcentajeIva ? $subtotalGasto * ($porcentajeIva / 100) : 0;
                    $retencionGasto = $porcentajeRetencion ? ($subtotalGasto - $movimiento->no_valor_iva) * ($porcentajeRetencion / 100) : 0;
                    $reteIcaGasto = $porcentajeReteIca ? ($subtotalGasto - $movimiento->no_valor_iva) * ($porcentajeReteIca / 1000) : 0;
                    $totalGasto = ($subtotalGasto + $ivaGasto) - ($retencionGasto + $reteIcaGasto);
                }

                if ($this->proveedor->sumar_aiu) {
                    $totalGasto+= $baseAIU;
                    $subtotalGasto+= $baseAIU;
                }
                
                $detalleGasto = ConGastoDetalles::create([
                    'id_gasto' => $gasto->id,
                    'id_concepto_gastos' => $movimiento->id_concepto,
                    'id_cuenta_gasto' => $conceptoGasto->id_cuenta_gasto,
                    'id_cuenta_iva' => $conceptoGasto->id_cuenta_iva,
                    'id_cuenta_retencion' => $conceptoGasto->id_cuenta_retencion,
                    'id_cuenta_reteica' => $conceptoGasto->id_cuenta_reteica,
                    'id_cuenta_retencion_declarante' => $conceptoGasto->id_cuenta_retencion_declarante,
                    'observacion' => $movimiento->observacion,
                    'subtotal' => $subtotalGasto,
                    'aiu_porcentaje' => $this->proveedor->porcentaje_aiu,
                    'aiu_valor' => $baseAIU,
                    'descuento_porcentaje' => $movimiento->porcentaje_descuento_gasto,
                    'rete_fuente_porcentaje' => $porcentajeRetencion,
                    'rete_ica_porcentaje' => $porcentajeReteIca,
                    'descuento_valor' => $movimiento->descuento_gasto,
                    'rete_fuente_valor' => $retencionGasto,
                    'rete_ica_valor' => $reteIcaGasto,
                    'iva_porcentaje' => $porcentajeIva,
                    'iva_valor' => $ivaGasto + $movimiento->no_valor_iva,
                    'total' => $totalGasto,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                foreach ($this->cuentasContables as $cuentaKey => $cuenta) {
                    $cuentaRecord = $conceptoGasto->{$cuentaKey};
                    
                    if (!$cuentaRecord) continue;
                    
                    $keyValorItem = $cuenta["valor"];
                    $valorTotal = $detalleGasto->{$keyValorItem};

                    $doc = new DocumentosGeneral([
                        "id_cuenta" => $cuentaRecord->id,
                        "id_nit" => $cuentaRecord->exige_nit ? $gasto->id_proveedor : null,
                        "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $request->get('id_centro_costos') : null,
                        "concepto" => $cuentaRecord->exige_concepto ? 'GASTO: '.$movimiento->observacion : null,
                        "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $gasto->documento_referencia : null,
                        "debito" => $valorTotal,
                        "credito" => $valorTotal,
                        "created_by" => request()->user()->id,
                        "updated_by" => request()->user()->id
                    ]);
                    $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_compras);
                }
            }
            //AGREGAR RETE FUENTE
            if ($this->totalesFactura['total_rete_fuente']) {
                $cuentaRetencion = PlanCuentas::whereId($this->totalesFactura['id_cuenta_rete_fuente'])->first();
                
                if ($cuentaRetencion->naturaleza_compras == PlanCuentas::DEBITO || $cuentaRetencion->naturaleza_compras == PlanCuentas::CREDITO) {
                    $doc = new DocumentosGeneral([
                        "id_cuenta" => $cuentaRetencion->id,
                        "id_nit" => $cuentaRetencion->exige_nit ? $gasto->id_proveedor : null,
                        "id_centro_costos" => $cuentaRetencion->exige_centro_costos ? $request->get('id_centro_costos') : null,
                        "concepto" => $cuentaRetencion->exige_concepto ? 'GASTO: RETENCIÓN' : null,
                        "documento_referencia" => $cuentaRetencion->exige_documento_referencia ? $gasto->documento_referencia : null,
                        "debito" => $this->totalesFactura['total_rete_fuente'],
                        "credito" => $this->totalesFactura['total_rete_fuente'],
                        "created_by" => request()->user()->id,
                        "updated_by" => request()->user()->id
                    ]);
                    $documentoGeneral->addRow($doc, $cuentaRetencion->naturaleza_compras);
                } else {
                    DB::connection('sam')->rollback();
                    return response()->json([
                        "success"=>false,
                        'data' => [],
                        "message"=> ['Cuenta retención' => ['La cuenta '.$cuentaRetencion->cuenta. ' - ' .$cuentaRetencion->nombre. ' no tiene naturaleza en compras']]
                    ], 422);
                }
            }
            //AGREGAR FORMAS DE PAGO
            $totalGasto = $this->totalesFactura['total_pagado'];
            foreach ($request->get('pagos') as $pago) {
                $pago = (object)$pago;

                $formaPago = $this->findFormaPago($pago->id);
                $totalGasto-= $pago->valor;

                ConGastoPagos::create([
                    'id_gasto' => $gasto->id,
                    'id_forma_pago' => $formaPago->id,
                    'valor' => $pago->valor,
                    'saldo' => $totalGasto,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                $doc = new DocumentosGeneral([
                    'id_cuenta' => $formaPago->cuenta->id,
                    'id_nit' => $formaPago->cuenta->exige_nit ? $gasto->id_proveedor : null,
                    'id_centro_costos' => $formaPago->cuenta->exige_centro_costos ? $gasto->id_centro_costos : null,
                    'concepto' => $formaPago->cuenta->exige_concepto ? 'GASTO: PAGO' : null,
                    'documento_referencia' => $formaPago->cuenta->exige_documento_referencia ? $gasto->documento_referencia : null,
                    'debito' => $pago->valor,
                    'credito' => $pago->valor,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_compras);
            }

            if (!$request->get('editing_gasto')) {
                $this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));
            }
            
            if (!$documentoGeneral->save()) {

				DB::connection('sam')->rollback();
				return response()->json([
					'success'=>	false,
					'data' => [],
					'message'=> $documentoGeneral->getErrors()
				], 422);
			}

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => $documentoGeneral->getRows(),
				'impresion' => $comprobanteGasto->imprimir_en_capturas ? $gasto->id : '',
				'message'=> 'Gasto creada con exito!'
			], 200);

        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function find (Request $request) 
    {
        $rules = [
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
			'fecha_manual' => 'required|date',
			'consecutivo' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 400);
        }

        $comprobante = Comprobantes::where('id', $request->get('id_comprobante'))->first();

        $gasto = ConGastos::with('nit', 'detalles.concepto', 'pagos')
            ->where('id_comprobante', $request->get('id_comprobante'))
            ->where('consecutivo', $request->get('consecutivo'));

        if ($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
            $fecha = $request->get('fecha_manual');
        
            $gasto->whereMonth('fecha_manual', Carbon::parse($fecha)->month)
                ->whereYear('fecha_manual', Carbon::parse($fecha)->year);
        }

        return response()->json([
			'success'=>	true,
			'data' => $gasto->first(),
			'message'=> 'Gasto cargado con exito!'
		]);
    }

    public function showPdf(Request $request, $id)
    {
        $gasto = ConGastos::whereId($id)
            ->with('comprobante')
            ->first();

        if(!$gasto) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'El gasto no existe'
            ], 422);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        // $data = (new GastosPdf($empresa, $gasto))->buildPdf()->getData();

        // return view('pdf.facturacion.gastos', $data);
 
        return (new GastosPdf($empresa, $gasto))
            ->buildPdf()
            ->showPdf();
    }

    private function createFacturaGasto($request)
    {
        
        $this->calcularTotales($request->get('gastos'), $request->get('id_proveedor'));
        $this->calcularFormasPago($request->get('pagos'));
        
        $gasto = ConGastos::create([
            'id_concepto' => $request->get('id_concepto'),
            'id_proveedor' => $request->get('id_proveedor'),
            'id_comprobante' => $request->get('id_comprobante'),
            'id_centro_costos' => $request->get('id_centro_costos'),
            'fecha_manual' => $request->get('fecha_manual'),
            'consecutivo' => $request->get('consecutivo'),
            'documento_referencia' => $request->get('documento_referencia'),
            'subtotal' => $this->totalesFactura['subtotal'],
            'total_iva' => $this->totalesFactura['total_iva'] + $this->totalesFactura['total_no_iva'],
            'total_descuento' => $this->totalesFactura['total_descuento'],
            'total_rete_fuente' => $this->totalesFactura['total_rete_fuente'],
            'total_rete_ica' => $this->totalesFactura['total_rete_ica'],
            'id_cuenta_rete_fuente' => $this->totalesFactura['id_cuenta_rete_fuente'],
            'porcentaje_rete_fuente' => $this->totalesFactura['porcentaje_rete_fuente'],
            'total_gasto' => $this->totalesFactura['total_gasto'],
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        return $gasto;
    }

    private function calcularTotales($gastos, $idNit)
    {
        $subtotalGeneral = 0;
        $porcentaje_iva_aiu = VariablesEntorno::where('nombre', 'porcentaje_iva_aiu')->first();
        $nit = Nits::find($idNit);

        foreach ($gastos as $gasto) {
            $gasto = (object)$gasto;
            $subtotalGeneral+= $gasto->valor_gasto - $gasto->descuento_gasto;

            $conceptoGasto = ConConceptoGastos::with(
                'cuenta_gasto',
                'cuenta_descento',
                'cuenta_iva.impuesto',
                'cuenta_reteica.impuesto',
                'cuenta_retencion.impuesto',
                'cuenta_retencion_declarante.impuesto'
            )->find($gasto->id_concepto);

            $id_retencion = null;
            $base_retencion = null;
            $porcentaje_retencion = null;

            if ($conceptoGasto->{$this->tipoRetencion}) {
                $id_retencion = $conceptoGasto->{$this->tipoRetencion}->id;

                if ($conceptoGasto->{$this->tipoRetencion}->impuesto) {
                    $base_retencion = $conceptoGasto->{$this->tipoRetencion}->impuesto->base;
                    $porcentaje_retencion = $conceptoGasto->{$this->tipoRetencion}->impuesto->porcentaje;
                }
            }
            
            if (!array_key_exists($id_retencion, $this->retenciones)) {
                $this->retenciones[$id_retencion] = (object)[
                    'id_retencion' => $id_retencion,
                    'porcentaje' => $porcentaje_retencion,
                    'base' => $base_retencion
                ];
            }
        }

        foreach ($this->retenciones as $retencion) {
            $retencion = (object)$retencion;
            if ($subtotalGeneral >= $retencion->base && $retencion->porcentaje > $this->totalesFactura['porcentaje_rete_fuente']) {
                $this->totalesFactura['porcentaje_rete_fuente'] = floatval($retencion->porcentaje);
                $this->totalesFactura['base_retencion'] = floatval($retencion->base);
                $this->totalesFactura['id_cuenta_rete_fuente'] = floatval($retencion->id_retencion);
            }
        }

        foreach ($gastos as $gasto) {
            $gasto = (object)$gasto;

            $conceptoGasto = ConConceptoGastos::with(
                'cuenta_gasto',
                'cuenta_descento',
                'cuenta_iva.impuesto',
                'cuenta_retencion.impuesto',
                'cuenta_retencion_declarante.impuesto',
                'cuenta_reteica'
            )->find($gasto->id_concepto);
            
            $baseAIU = 0;

            $porcentajeReteIca = floatval($nit->porcentaje_reteica);
            $porcentajeReteIca = $conceptoGasto->cuenta_reteica ? $porcentajeReteIca : 0;
            $porcentajeIva = $conceptoGasto->cuenta_iva ? floatval($conceptoGasto->cuenta_iva->impuesto->porcentaje) : 0;
            $porcentajeRetencion = $this->totalesFactura['porcentaje_rete_fuente'];

            $subtotalGasto = $gasto->valor_gasto - $gasto->descuento_gasto;
            
            if (floatval($this->proveedor->porcentaje_aiu)) {
                $baseAIU = $subtotalGasto * (floatval($this->proveedor->porcentaje_aiu / 100));
            }

            $valorRetencion = 0;
            $valorReteIca = 0;
            $ivaGasto = 0;

            if ($baseAIU) {
                $porcentajeIva = VariablesEntorno::where('nombre', 'porcentaje_iva_aiu')->first();
                $porcentajeIva = $porcentajeIva ? floatval($porcentajeIva->valor) : 0;

                $valorRetencion = $porcentajeRetencion ? ($baseAIU - $gasto->no_valor_iva) * ($porcentajeRetencion / 100) : 0;
                $valorReteIca = $porcentajeReteIca ? $baseAIU * ($porcentajeReteIca / 1000) : 0;
                $ivaGasto = $porcentajeIva ? $baseAIU * ($porcentajeIva / 100) : 0;
            } else {
                $valorRetencion = $porcentajeRetencion ? ($subtotalGasto - $gasto->no_valor_iva) * ($porcentajeRetencion / 100) : 0;
                $valorReteIca = $porcentajeReteIca ? ($subtotalGasto - $gasto->no_valor_iva) * ($porcentajeReteIca / 1000) : 0;
                $ivaGasto = $porcentajeIva ? $subtotalGasto * ($porcentajeIva / 100) : 0;
            }
            
            $valorTotal = 0;
            if ($nit->sumar_aiu) {
                $valorTotal = ($subtotalGasto + $ivaGasto + $baseAIU) - ($valorRetencion + $valorReteIca);
                $subtotalGasto+= $baseAIU;
            }
            else {
                $valorTotal = ($subtotalGasto + $ivaGasto) - ($valorRetencion + $valorReteIca);
            }

            $this->totalesFactura['subtotal']+= $subtotalGasto;
            $this->totalesFactura['total_iva']+= $ivaGasto;
            $this->totalesFactura['total_no_iva']+= $gasto->no_valor_iva;
            $this->totalesFactura['total_descuento']+= $gasto->descuento_gasto;
            $this->totalesFactura['total_rete_fuente']+= $valorRetencion;
            $this->totalesFactura['total_rete_ica']+= $valorReteIca;
            $this->totalesFactura['porcentaje_rete_ica']+= $porcentajeReteIca;
            $this->totalesFactura['total_gasto']+= round($valorTotal, 2);
        }
    }

    private function findProveedor ($id_proveedor)
    {
        return Nits::whereId($id_proveedor)
            ->select(
                '*',
                DB::raw("CASE
                    WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END AS nombre_nit")
            )
            ->first();
    }

    private function findFormaPago ($id_forma_pago)
    {
        return FacFormasPago::where('id', $id_forma_pago)
            ->with(
                'cuenta'
            )
            ->first();
    }

    private function calcularFormasPago($pagos)
    {
        foreach ($pagos as $pago) {
            $pago = (object)$pago;
            $this->totalesFactura['total_pagado']+= floatval($pago->valor);
        }
    }    

}