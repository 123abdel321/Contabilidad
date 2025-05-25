<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Printers\GastosPdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
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
    use BegDocumentHelpersTrait;

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
        $redondeo_gastos = VariablesEntorno::where('nombre', 'redondeo_gastos')->first();
        $valor_uvt = VariablesEntorno::where('nombre', 'valor_uvt')->first();

        $data = [
            'comprobantes' => Comprobantes::where('tipo_comprobante', Comprobantes::TIPO_GASTOS)->get(),
            'porcentaje_iva_aiu' => $porcentaje_iva_aiu ? $porcentaje_iva_aiu->valor : 0,
            'redondeo_gastos' => $redondeo_gastos ? $redondeo_gastos->valor : NULL,
            'valor_uvt' => $valor_uvt ? $valor_uvt->valor : 0,
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
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $isFechaCierreLimit = $this->isFechaCierreLimit($request->get('fecha_manual'));

        if ($isFechaCierreLimit) {
			return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>['fecha_manual' => ['mensaje' => 'Se esta grabando en un año cerrado']]
            ], 200);
		}

        try {
            DB::connection('sam')->beginTransaction();
            
            $comprobanteGasto = Comprobantes::where('id', $request->get('id_comprobante'))->first();
            $porcentaje_iva_aiu = VariablesEntorno::where('nombre', 'porcentaje_iva_aiu')->first();
            $porcentaje_iva_aiu = $porcentaje_iva_aiu ? $porcentaje_iva_aiu->valor : 0;
            
            $this->proveedor = $this->findProveedor($request->get('id_proveedor'));
            $responsabilidades = $this->getResponsabilidades($this->proveedor->id_responsabilidades);

            if (in_array('5', $responsabilidades)) {
                $this->tipoRetencion = 'cuenta_retencion_declarante';
            }

            if ($request->get('editing_gasto')) {

                $gasto = ConGastos::where('id_comprobante', $request->get('id_comprobante'))
                    ->where('consecutivo', $request->get('consecutivo'))
                    ->orderBy('id', 'DESC')
                    ->first();

                $consecutivoUsado = $this->consecutivoUsado(
                    $comprobanteGasto,
                    $request->get('consecutivo'),
                    $request->get('fecha_manual'),
                    $gasto
                );

                if ($consecutivoUsado) {
                    return response()->json([
                        "success"=>false,
                        'data' => [],
                        "message"=> "El consecutivo {$request->get('consecutivo')} ya está en uso."
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if ($gasto) {
                    $gasto->documentos()->delete();
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

            $porcentaje_iva_aiu = VariablesEntorno::where('nombre', 'porcentaje_iva_aiu')->first();
            $porcentaje_iva_aiu = $porcentaje_iva_aiu ? $porcentaje_iva_aiu->valor : 0;

            $redondeo_gastos = VariablesEntorno::where('nombre', 'redondeo_gastos')->first();
            $redondeo_gastos = $redondeo_gastos ? floatval($redondeo_gastos->valor) : null;

            //AGREGAR MOVIMIENTO DE CUENTAS POR GASTO
            foreach ($request->get('gastos') as $movimiento) {
                $movimiento = (object)$movimiento;
                
                $conceptoGasto = ConConceptoGastos::with(
                    'cuenta_gasto.tipos_cuenta',
                    'cuenta_reteica.tipos_cuenta',
                    'cuenta_descento.tipos_cuenta',
                    'cuenta_iva.impuesto',
                    'cuenta_retencion.impuesto',
                    'cuenta_retencion_declarante.impuesto',
                    'cuenta_iva.tipos_cuenta',
                    'cuenta_retencion.tipos_cuenta',
                    'cuenta_retencion_declarante.tipos_cuenta',
                )->find($movimiento->id_concepto);

                $porcentajeRetencion = $this->totalesFactura['porcentaje_rete_fuente'];
                $porcentajeReteIca = $this->totalesFactura['porcentaje_rete_ica'];
                $porcentajeIva = 0;

                if ($conceptoGasto->cuenta_iva && floatval($conceptoGasto->cuenta_iva->impuesto->porcentaje)) {
                    $porcentajeIva = floatval($conceptoGasto->cuenta_iva->impuesto->porcentaje);
                } else if (!$conceptoGasto->cuenta_iva && $this->proveedor->porcentaje_aiu && $porcentaje_iva_aiu) {
                    $porcentajeIva = $porcentaje_iva_aiu;
                }

                $subtotalGasto = $this->redondearGasto($movimiento->valor_gasto - $movimiento->descuento_gasto, $redondeo_gastos);
                $baseAIU = 0;

                if (floatval($this->proveedor->porcentaje_aiu)) {

                    $ivaGasto = 0;
                    $baseAIU = $this->redondearGasto($subtotalGasto * ($this->proveedor->porcentaje_aiu / 100), $redondeo_gastos);

                    if ($porcentajeIva) {
                        $ivaGasto = $porcentajeIva ? $baseAIU * ($porcentajeIva / 100) : 0;
                    } else if ($porcentaje_iva_aiu) {
                        $ivaGasto = $baseAIU * ($porcentaje_iva_aiu / 100);
                    }
                    $ivaGasto = $this->redondearGasto($ivaGasto, $redondeo_gastos);
                    $retencionGasto = $this->redondearGasto($porcentajeRetencion ? ($baseAIU - $movimiento->no_valor_iva) * ($porcentajeRetencion / 100) : 0, $redondeo_gastos);
                    $reteIcaGasto = $this->redondearGasto($porcentajeReteIca ? $baseAIU * ($porcentajeReteIca / 1000) : 0, $redondeo_gastos);
                    $totalGasto = $this->redondearGasto(($subtotalGasto + $ivaGasto) - ($retencionGasto + $reteIcaGasto), $redondeo_gastos);
                    
                    $subtotalGasto+= $ivaGasto;
                } else {
                    $ivaGasto = $this->redondearGasto($porcentajeIva ? $subtotalGasto * ($porcentajeIva / 100) : 0, $redondeo_gastos);
                    $retencionGasto = $this->redondearGasto($porcentajeRetencion ? ($subtotalGasto - $movimiento->no_valor_iva) * ($porcentajeRetencion / 100) : 0, $redondeo_gastos);
                    $reteIcaGasto = $this->redondearGasto($porcentajeReteIca ? ($subtotalGasto - $movimiento->no_valor_iva) * ($porcentajeReteIca / 1000) : 0, $redondeo_gastos);
                    $totalGasto = $this->redondearGasto(($subtotalGasto + $ivaGasto) - ($retencionGasto + $reteIcaGasto), $redondeo_gastos);
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
                    
                    $naturalezaCuenta = $cuentaRecord->naturaleza_compras;

                    if (count($cuentaRecord->tipos_cuenta)) {
                        foreach ($cuentaRecord->tipos_cuenta as $tipoCuenta) {
                            if ($tipoCuenta->id_tipo_cuenta == 7) {
                                $naturalezaCuenta = $cuentaRecord->naturaleza_cuenta;
                                break;
                            }
                        }
                    }

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
                    $documentoGeneral->addRow($doc, $naturalezaCuenta);
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
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
            //AGREGAR FORMAS DE PAGO
            $totalGasto = $this->totalesFactura['total_pagado'];
            foreach ($request->get('pagos') as $pagoItem) {
                
                $pagoItem = (object)$pagoItem;
                $totalGasto-= $pagoItem->valor;
                $formaPago = $this->findFormaPago($pagoItem->id);
                $documentoReferenciaAnticipos = $this->isAnticiposDocumentoRefe($formaPago, $this->proveedor->id);

                //CRUSAR ANTICIPOS
                if (count($documentoReferenciaAnticipos)) {

                    $pagoAnticipos = $pagoItem->valor;

                    foreach ($documentoReferenciaAnticipos as $anticipos) {

                        $naturaleza = $formaPago->cuenta->naturaleza_egresos;

                        if ($formaPago->cuenta?->tipos_cuenta) {
                            foreach ($formaPago->cuenta->tipos_cuenta as $tipos_cuenta) {
                                if ($tipos_cuenta->id_tipo_cuenta == 7) {
                                    $naturaleza = $formaPago->cuenta->naturaleza_compras;
                                }
                            }
                        }

                        if (!$pagoAnticipos) {
                            break;
                        }

                        $anticipoUsado = 0;
                        $anticipoDisponible = floatval($anticipos->saldo);

                        if ($anticipoDisponible >= $pagoAnticipos) {
                            $anticipoUsado = $pagoAnticipos;
                        } else {
                            $anticipoUsado = $anticipoDisponible;
                        }

                        $pagoAnticipos-= $anticipoUsado;

                        $doc = $this->addFormaPago(
                            $anticipos->documento_referencia,
                            $formaPago,
                            $this->proveedor,
                            $pagoItem,
                            $gasto,
                            $anticipoUsado,
                            $totalGasto
                        );
                        $documentoGeneral->addRow($doc, $naturaleza);
                    }
                } else {
                    $naturaleza = $formaPago->cuenta->naturaleza_egresos;
                    
                    if ($formaPago->cuenta?->tipos_cuenta) {
                        foreach ($formaPago->cuenta->tipos_cuenta as $tipos_cuenta) {
                            if ($tipos_cuenta->id_tipo_cuenta == 4) {
                                $naturaleza = $formaPago->cuenta->naturaleza_compras;
                            }
                        }
                    }

                    $doc = $this->addFormaPago(
                        $request->get('documento_referencia'),
                        $formaPago,
                        $this->proveedor,
                        $pagoItem,
                        $gasto,
                        $pagoItem->valor,
                        $totalGasto
                    );
                    $documentoGeneral->addRow($doc, $naturaleza);
                }
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
				], Response::HTTP_UNPROCESSABLE_ENTITY);
			}

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => $documentoGeneral->getRows(),
				'impresion' => $comprobanteGasto->imprimir_en_capturas ? $gasto->id : '',
				'message'=> 'Gasto creada con exito!'
			], Response::HTTP_OK);

        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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

        $gasto = ConGastos::with('nit', 'detalles.concepto', 'pagos', 'detalles.cuenta_retencion.impuesto', 'detalles.cuenta_retencion_declarante.impuesto')
            ->where('id_comprobante', $request->get('id_comprobante'))
            ->where('consecutivo', $request->get('consecutivo'));

        if ($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
            $this->filterCapturaMensual($gasto, $request->get('fecha_manual'));
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
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
        // dd($this->totalesFactura);
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
        $redondeo_gastos = VariablesEntorno::where('nombre', 'redondeo_gastos')->first();
        $redondeo_gastos = $redondeo_gastos ? floatval($redondeo_gastos->valor) : null;
        
        $nit = Nits::find($idNit);
        $responsabilidades = $this->getResponsabilidades($nit->id_responsabilidades);

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

            $subtotalGasto = $this->redondearGasto($gasto->valor_gasto - $gasto->descuento_gasto, $redondeo_gastos);
            
            if (floatval($this->proveedor->porcentaje_aiu)) {
                $baseAIU = $subtotalGasto * (floatval($this->proveedor->porcentaje_aiu / 100));
                $baseAIU = $this->redondearGasto($baseAIU, $redondeo_gastos);
            }

            $valorRetencion = 0;
            $valorReteIca = 0;
            $ivaGasto = 0;

            if ($baseAIU) {
                $porcentajeIva = VariablesEntorno::where('nombre', 'porcentaje_iva_aiu')->first();
                $porcentajeIva = $porcentajeIva ? floatval($porcentajeIva->valor) : 0;
                
                if (in_array('7', $responsabilidades) && $porcentajeRetencion) {
                    $valorRetencion = ($baseAIU - $gasto->no_valor_iva) * ($porcentajeRetencion / 100);
                }
                $valorReteIca = $porcentajeReteIca ? $baseAIU * ($porcentajeReteIca / 1000) : 0;
                $ivaGasto = $porcentajeIva ? $baseAIU * ($porcentajeIva / 100) : 0;
            } else {
                if (in_array('7', $responsabilidades) && $porcentajeRetencion) {
                    $valorRetencion = ($subtotalGasto - $gasto->no_valor_iva) * ($porcentajeRetencion / 100);
                }
                $valorReteIca = $porcentajeReteIca ? ($subtotalGasto - $gasto->no_valor_iva) * ($porcentajeReteIca / 1000) : 0;
                $ivaGasto = $porcentajeIva ? $subtotalGasto * ($porcentajeIva / 100) : 0;
            }

            $valorRetencion = $this->redondearGasto($valorRetencion, $redondeo_gastos);
            $valorReteIca = $this->redondearGasto($valorReteIca, $redondeo_gastos);
            $ivaGasto = $this->redondearGasto($ivaGasto, $redondeo_gastos);
            
            $valorTotal = 0;
            if ($nit->sumar_aiu) {
                $valorTotal = ($subtotalGasto + $ivaGasto + $baseAIU) - ($valorRetencion + $valorReteIca);
                $subtotalGasto+= $baseAIU;
            }
            else {
                $valorTotal = ($subtotalGasto + $ivaGasto) - ($valorRetencion + $valorReteIca);
            }

            $valorTotal = $this->redondearGasto($valorTotal, $redondeo_gastos);

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

    private function getResponsabilidades($id_responsabilidades)
    {
        if ($id_responsabilidades) {
            return explode(",", $id_responsabilidades);
        }
        return [];
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

    private function addFormaPago($documentoReferencia, $formaPago, $nit, $pagoItem, $gasto, $valor, $saldo)
    {
        ConGastoPagos::create([
            'id_gasto' => $gasto->id,
            'id_forma_pago' => $pagoItem->id,
            'valor' => abs($valor),
            'saldo' => $saldo,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        $doc = new DocumentosGeneral([
            'id_cuenta' => $formaPago->cuenta->id,
            'id_nit' => $formaPago->cuenta->exige_nit ? $nit->id : null,
            'id_centro_costos' => null,
            'concepto' => $formaPago->cuenta->exige_concepto ? 'TOTAL PAGO: '.$nit->nombre_nit.' - '.$gasto->consecutivo : null,
            'documento_referencia' => $formaPago->cuenta->exige_documento_referencia ? $documentoReferencia : null,
            'debito' => abs($valor),
            'credito' => abs($valor),
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        return $doc;
    }

    private function findFormaPago ($id_forma_pago)
    {
        return FacFormasPago::where('id', $id_forma_pago)
            ->with(
                'cuenta.tipos_cuenta',
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

    private function redondearGasto($valor, $redondeo_valor = null)
    {
        if ($redondeo_valor === null && $redondeo_valor !== 0) return $valor;
        $redondeo_valor = floatval($redondeo_valor);
        if (!$valor) return $valor; // Sin valor a redondear
        if ($redondeo_valor === null) return $valor; // No redondear
        if ($redondeo_valor == 0) return floor($valor); // Quitar decimales (redondear hacia abajo)
        return round($valor / $redondeo_valor) * $redondeo_valor; // Redondear al múltiplo más cercano
    }


    private function isAnticiposDocumentoRefe($formaPago, $idNit)
    {
        $tiposCuenta = $formaPago->cuenta->tipos_cuenta;
        foreach ($tiposCuenta as $tipoCuenta) {
            if ($tipoCuenta->id_tipo_cuenta == 7) {
                $anticipoCuenta = (new Extracto(
                    $idNit,
                    null,
                    null,
                    Carbon::now()->format('Y-m-d H:i:s'),
                    $formaPago->cuenta->id
                ))->anticiposDiscriminados()->get();

                return $anticipoCuenta;
            }
        }

        return [];
    }

}