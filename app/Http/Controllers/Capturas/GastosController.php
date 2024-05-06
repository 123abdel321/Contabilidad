<?php

namespace App\Http\Controllers\Capturas;

use DB;
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
        "cuenta_descento" => ["valor" => "descuento_valor"],
        "cuenta_iva" => ["valor" => "iva_valor"],
    ];
    protected $totalesFactura = [
        'base_retencion' => 0,
        'porcentaje_rete_fuente' => 0,
        'id_cuenta_rete_fuente' => null,
        'subtotal' => 0,
        'total_iva' => 0,
        'total_no_iva' => 0,
        'total_rete_fuente' => 0,
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
        $data = [
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

        $consecutivo = $this->getNextConsecutive($request->get('id_comprobante'), $request->get('fecha_manual'));
        $request->request->add(['consecutivo' => $consecutivo]);

        try {
            DB::connection('sam')->beginTransaction();
            
            $this->proveedor = $this->findProveedor($request->get('id_proveedor'));
            if (!$this->proveedor->declarante) $this->tipoRetencion = 'cuenta_retencion_declarante';
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
                    'cuenta_descento',
                    'cuenta_iva.impuesto',
                    'cuenta_retencion.impuesto',
                    'cuenta_retencion_declarante.impuesto',
                )->find($movimiento->id_concepto);

                $porcentajeRetencion = $this->totalesFactura['porcentaje_rete_fuente'];
                $porcentajeIva = $conceptoGasto->cuenta_iva ? floatval($conceptoGasto->cuenta_iva->impuesto->porcentaje) : 0;
                $subtotalGasto = $movimiento->valor_gasto - $movimiento->descuento_gasto;
                $baseAIU = 0;
                if ($this->proveedor->porcentaje_aiu) {
                    $baseAIU = $subtotalGasto * ($this->proveedor->porcentaje_aiu / 100);
                    $ivaGasto = $porcentajeIva ? $baseAIU * ($porcentajeIva / 100) : 0;
                    $retencionGasto = $porcentajeRetencion ? $baseAIU * ($porcentajeRetencion / 100) : 0;
                    $totalGasto = ($subtotalGasto + $ivaGasto + $gasto->no_valor_iva) - $retencionGasto;
                } else {
                    $ivaGasto = $porcentajeIva ? $subtotalGasto * ($porcentajeIva / 100) : 0;
                    $retencionGasto = $porcentajeRetencion ? $subtotalGasto * ($porcentajeRetencion / 100) : 0;
                    $totalGasto = ($subtotalGasto + $ivaGasto) - $retencionGasto;
                }

                $detalleGasto = ConGastoDetalles::create([
                    'id_gasto' => $gasto->id,
                    'id_concepto_gastos' => $movimiento->id_concepto,
                    'id_cuenta_gasto' => $conceptoGasto->id_cuenta_gasto,
                    'id_cuenta_iva' => $conceptoGasto->id_cuenta_iva,
                    'id_cuenta_retencion' => $conceptoGasto->id_cuenta_retencion,
                    'id_cuenta_retencion_declarante' => $conceptoGasto->id_cuenta_retencion_declarante,
                    'observacion' => $movimiento->observacion,
                    'subtotal' => $subtotalGasto,
                    'aiu_porcentaje' => $this->proveedor->porcentaje_aiu,
                    'aiu_valor' => $baseAIU,
                    'descuento_porcentaje' => $movimiento->porcentaje_descuento_gasto,
                    'rete_fuente_porcentaje' => $porcentajeRetencion,
                    'descuento_valor' => $movimiento->descuento_gasto,
                    'rete_fuente_valor' => $retencionGasto,
                    'iva_porcentaje' => $porcentajeIva,
                    'iva_valor' => $ivaGasto + $movimiento->no_valor_iva,
                    'total' => $totalGasto,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                foreach ($this->cuentasContables as $cuentaKey => $cuenta) {
                    $cuentaRecord = $conceptoGasto->{$cuentaKey};
                    $keyValorItem = $cuenta["valor"];
                    if (!$cuentaRecord) continue;
                    
                    $doc = new DocumentosGeneral([
                        "id_cuenta" => $cuentaRecord->id,
                        "id_nit" => $cuentaRecord->exige_nit ? $gasto->id_proveedor : null,
                        "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $request->get('id_centro_costos') : null,
                        "concepto" => $cuentaRecord->exige_concepto ? 'GASTO: '.$movimiento->observacion : null,
                        "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $gasto->documento_referencia : null,
                        "debito" => $detalleGasto->{$keyValorItem},
                        "credito" => $detalleGasto->{$keyValorItem},
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

            $this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));

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

    private function createFacturaGasto($request)
    {
        $this->calcularTotales($request->get('gastos'));
        $this->calcularFormasPago($request->get('pagos'));

        $gasto = ConGastos::create([
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
            'id_cuenta_rete_fuente' => $this->totalesFactura['id_cuenta_rete_fuente'],
            'porcentaje_rete_fuente' => $this->totalesFactura['porcentaje_rete_fuente'],
            'total_gasto' => $this->totalesFactura['total_gasto'],
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        return $gasto;
    }

    private function calcularTotales($gastos)
    {
        $subtotalGeneral = 0;
        foreach ($gastos as $gasto) {
            $gasto = (object)$gasto;
            $subtotalGeneral+= $gasto->valor_gasto - $gasto->descuento_gasto;

            $conceptoGasto = ConConceptoGastos::with(
                'cuenta_gasto',
                'cuenta_descento',
                'cuenta_iva.impuesto',
                'cuenta_retencion.impuesto',
                'cuenta_retencion_declarante.impuesto',
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
            )->find($gasto->id_concepto);

            $porcentajeIva = $conceptoGasto->cuenta_iva ? floatval($conceptoGasto->cuenta_iva->impuesto->porcentaje) : 0;
            $porcentajeRetencion = $this->totalesFactura['porcentaje_rete_fuente'];

            $subtotalGasto = $gasto->valor_gasto - ($gasto->descuento_gasto + $gasto->no_valor_iva);
            
            if ($this->proveedor->porcentaje_aiu) {
                $baseAIU = $subtotalGasto * ($this->proveedor->porcentaje_aiu / 100);
                $ivaGasto = $porcentajeIva ? $baseAIU * ($porcentajeIva / 100) : 0;
                $retencionGasto = $porcentajeRetencion ? $baseAIU * ($porcentajeRetencion / 100) : 0;
                $totalGasto = ($subtotalGasto + $ivaGasto + $gasto->no_valor_iva) - $retencionGasto;
            } else {
                $ivaGasto = $porcentajeIva ? $subtotalGasto * ($porcentajeIva / 100) : 0;
                $retencionGasto = $porcentajeRetencion ? $subtotalGasto * ($porcentajeRetencion / 100) : 0;
                $totalGasto = ($subtotalGasto + $ivaGasto + $gasto->no_valor_iva) - $retencionGasto;
            }

            $this->totalesFactura['subtotal']+= $subtotalGasto;
            $this->totalesFactura['total_iva']+= $ivaGasto;
            $this->totalesFactura['total_no_iva']+= $gasto->no_valor_iva;
            $this->totalesFactura['total_descuento']+= $gasto->descuento_gasto;
            $this->totalesFactura['total_rete_fuente']+= $retencionGasto;
            $this->totalesFactura['total_gasto']+= $totalGasto;
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
        $data = (new GastosPdf($empresa, $gasto))->buildPdf()->getData();
 
        return (new GastosPdf($empresa, $gasto))
            ->buildPdf()
            ->showPdf();
    }

}