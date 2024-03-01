<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Config;
use Carbon\Carbon;
use DateTimeImmutable;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Helpers\Printers\RecibosPdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConRecibos;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\ConReciboPagos;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\ConReciboDetalles;

class RecibosController extends Controller
{
    use BegConsecutiveTrait;

    protected $id_recibo = 0;
    protected $messages = null;
    protected $fechaManual = null;
    protected $totalesFactura = [
        'total_abonado' => 0,
        'total_anticipo' => 0,
        'total_pagado' => 0
    ];

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

    public function index ()
    {
        $data = [
            'comprobantes' => Comprobantes::where('tipo_comprobante', Comprobantes::TIPO_INGRESOS)->get()
        ];

        return view('pages.capturas.recibo.recibo-view', $data);
    }

    public function generate(Request $request)
    {
        if (!$request->get('id_nit')) {
			return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Recibo generado con exito!'
            ], 200);
		}

        $fechaManual = request()->user()->can('recibo fecha') ? $request->get('fecha_manual', null) : Carbon::now();
        
        try {
            $extractos = (new Extracto(
                $request->get('id_nit'),
                3,
                null,
                $fechaManual
            ))->actual()->get();

            $cxcAnticipos = PlanCuentas::where('auxiliar', 1)
                ->where('exige_documento_referencia', 1)
                ->whereHas('tipos_cuenta', function ($query) {
                    $query->whereIn('id_tipo_cuenta', [8]);
                })->get();
            
            $dataRecibos = [];

            if (count($extractos)) {
                foreach ($extractos as $extracto) {
                    $dataRecibos[] = $this->formatExtracto($extracto);
                }
            } else {
                $this->id_recibo++;
                $dataRecibos[] = [
                    'id' => $this->id_recibo,
                    'id_cuenta' => '',
                    'codigo_cuenta' => '',
                    'nombre_cuenta' => 'SIN CUENTAS POR COBRAR',
                    'fecha_manual' => '',
                    'dias_cumplidos' => '',
                    'plazo' => '',
                    'documento_referencia' => '',
                    'saldo' => '',
                    'valor_recibido' => '',
                    'nuevo_saldo' => '',
                    'total_abono' => '',
                    'concepto' => '',
                    'cuenta_recibo' => 'sin_deuda',
                ];
            }

            foreach ($cxcAnticipos as $cxcAnticipo) {
                $dataRecibos[] = $this->formatCuentaAnticipo($cxcAnticipo, $request->get('id_nit'));
            }

            return response()->json([
                'success'=>	true,
                'data' => $dataRecibos,
                'message'=> 'Recibo generado con exito!'
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

    public function create (Request $request)
    {
        return response()->json([
            'success'=>	true,
            'data' => [],
            'impresion' => 32,
            'message'=> 'Recibo creado con exito!'
        ], 200);
        $comprobanteRecibo = Comprobantes::where('id', $request->get('id_comprobante'))->first();

        $this->fechaManual = request()->user()->can('recibo fecha') ? $request->get('fecha_manual', null) : Carbon::now();

        if(!$comprobanteRecibo) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> ['Comprobante recibo' => ['El Comprobante del recibo es incorrecto!']]
            ], 422);
        } else {
            $consecutivo = $this->getNextConsecutive($request->get('id_comprobante'), $this->fechaManual);
            $request->request->add([
                'consecutivo' => $consecutivo
            ]);
        }

        $rules = [
            'id_nit' => 'required|exists:sam.nits,id',
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
            'fecha_manual' => 'required|date',
            'consecutivo' => 'required',
            'movimiento' => 'array|required',
            'movimiento.*.id_cuenta' => 'required|exists:sam.plan_cuentas,id',
            'movimiento.*.valor_recibido' => 'required',
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

        $empresa = Empresa::where('id', request()->user()->id_empresa)->first();
		$fechaCierre= DateTimeImmutable::createFromFormat('Y-m-d', $empresa->fecha_ultimo_cierre);

        if ($this->fechaManual <= $fechaCierre) {
			return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>['fecha_manual' => ['mensaje' => 'Se esta grabando en un año cerrado']]
            ], 422);
		}
        
        try {
            DB::connection('sam')->beginTransaction();
            //CREAR FACTURA RECIBO
            $recibo = $this->createFacturaRecibo($request);
            $nit = $this->findNit($recibo->id_nit);

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE RECIBOS
            $documentoGeneral = new Documento(
                $request->get('id_comprobante'),
                $recibo,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            foreach ($request->get('movimiento') as $movimiento) {
                $movimiento = (object)$movimiento;
                $cuentaRecord = PlanCuentas::find($movimiento->id_cuenta);

                //CREAR RECIBO DETALLE
                ConReciboDetalles::create([
                    'id_recibo' => $recibo->id,
                    'id_cuenta' => $cuentaRecord->id,
                    'id_nit' => $recibo->id_nit,
                    'fecha_manual' => $recibo->fecha_manual,
                    'documento_referencia' => $movimiento->documento_referencia,
                    'consecutivo' => $recibo->consecutivo,
                    'concepto' => $movimiento->concepto,
                    'total_factura' => 0,
                    'total_abono' => $movimiento->cuenta_recibo ? $movimiento->valor_recibido : 0,
                    'total_saldo' => $movimiento->cuenta_recibo ? $movimiento->saldo : 0,
                    'nuevo_saldo' => $movimiento->cuenta_recibo ? $movimiento->saldo - $movimiento->valor_recibido : 0,
                    'total_anticipo' => $movimiento->cuenta_recibo ? 0 : $movimiento->valor_recibido,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                //AGREGAR MOVIMIENTO CONTABLE
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaRecord->id,
                    "id_nit" => $cuentaRecord->exige_nit ? $recibo->id_nit : null,
                    "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $compra->id_centro_costos : null,
                    "concepto" => $cuentaRecord->exige_concepto ? $movimiento->concepto : null,
                    "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $movimiento->documento_referencia : null,
                    "debito" => $movimiento->valor_recibido,
                    "credito" => $movimiento->valor_recibido,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_ingresos);
            }

            $totalRecibos = $this->totalesFactura['total_pagado'];

            //AGREGAR FORMAS DE PAGO
            foreach ($request->get('pagos') as $pago) {
                $pago = (object)$pago;
                $totalRecibos-= $pago->valor;
                $formaPago = $this->findFormaPago($pago->id);
                $documentoReferenciaAnticipos = $this->isAnticiposDocumentoRefe($formaPago, $nit->id);

                ConReciboPagos::create([
                    'id_recibo' => $recibo->id,
                    'id_forma_pago' => $pago->id,
                    'valor' => $pago->valor,
                    'saldo' => $totalRecibos,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                $doc = new DocumentosGeneral([
                    'id_cuenta' => $formaPago->cuenta->id,
                    'id_nit' => $formaPago->cuenta->exige_nit ? $nit->id : null,
                    'id_centro_costos' => null,
                    'concepto' => $formaPago->cuenta->exige_concepto ? 'TOTAL RECIBO: '.$nit->nombre_nit.' - '.$recibo->consecutivo : null,
                    'documento_referencia' => $documentoReferenciaAnticipos,
                    'debito' => $pago->valor,
                    'credito' => $pago->valor,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
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
                'impresion' => $comprobanteRecibo->imprimir_en_capturas ? $recibo->id : '',
                'message'=> 'Recibo creado con exito!'
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

    public function showPdf(Request $request, $id)
    {
        $recibo = ConRecibos::whereId($id)
            ->with('comprobante')
            ->first();

        if(!$recibo) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'El recibo no existe'
            ], 422);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        $data = (new RecibosPdf($empresa, $recibo))->buildPdf()->getData();
 
        return (new RecibosPdf($empresa, $recibo))
            ->buildPdf()
            ->showPdf();
    }

    public function showPdfPublic(Request $request)
    {
        $token_db = base64_decode($request->get('token_db'));
        $empresa = Empresa::where('token_db', $token_db)->first();

		Config::set('database.connections.sam.database', $token_db);
        
        $recibo = ConRecibos::whereId($request->get('id'))
            ->with('comprobante')
            ->first();

        if(!$recibo) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'El recibo no existe'
            ], 422);
        }

        $data = (new RecibosPdf($empresa, $recibo))->buildPdf()->getData();
 
        return (new RecibosPdf($empresa, $recibo))
            ->buildPdf()
            ->showPdf();
    }

    private function formatExtracto($extracto)
    {
        $this->id_recibo++;
        return [
            'id' => $this->id_recibo,
            'id_cuenta' =>  $extracto->id_cuenta,
            'codigo_cuenta' => $extracto->cuenta,
            'nombre_cuenta' => $extracto->nombre_cuenta,
            'fecha_manual' => $extracto->fecha_manual,
            'dias_cumplidos' => $extracto->dias_cumplidos,
            'plazo' => $extracto->plazo,
            'documento_referencia' => $extracto->documento_referencia,
            'saldo' => $extracto->saldo,
            'valor_recibido' => 0,
            'nuevo_saldo' => $extracto->saldo,
            'total_abono' => $extracto->total_abono,
            'concepto' => '',
            'cuenta_recibo' => true,
        ];
    }

    private function formatCuentaAnticipo($cuenta, $idNit)
    {
        $this->id_recibo++;
        $anticipoCuenta = (new Extracto(
            $idNit,
            null,
            null,
            Carbon::now()->format('Y-m-d H:i:s'),
            $cuenta->id
        ))->anticipos()->first();

        return [
            'id' => $this->id_recibo,
            'id_cuenta' =>  $cuenta->id,
            'codigo_cuenta' => $cuenta->cuenta,
            'nombre_cuenta' => $cuenta->nombre,
            'fecha_manual' => Carbon::now()->format('Y-m-d'),
            'dias_cumplidos' => '',
            'plazo' => '',
            'documento_referencia' => '',
            'saldo' => 0,
            'valor_recibido' => 0,
            'nuevo_saldo' => 0,
            'total_abono' => 0,
            'concepto' => '',
            'cuenta_recibo' => false,
        ];
    }

    private function createFacturaRecibo($request)
    {
        $this->calcularTotales($request->get('movimiento'));
        $this->calcularFormasPago($request->get('pagos'));

        $recibo = ConRecibos::create([
            'id_nit' => $request->get('id_nit'),
            'id_comprobante' => $request->get('id_comprobante'),
            'fecha_manual' => $this->fechaManual,
            'consecutivo' => $request->get('consecutivo'),
            'total_abono' => $this->totalesFactura['total_abonado'],
            'total_anticipo' => $this->totalesFactura['total_anticipo'],
            'observacion' => $request->get(''),
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        return $recibo;
    }

    private function calcularTotales ($movimientos)
    {
        foreach ($movimientos as $movimiento) {
            $movimiento = (object)$movimiento;
            if ($movimiento->cuenta_recibo) {
                $this->totalesFactura['total_abonado']+= floatval($movimiento->valor_recibido);
            } else {
                $this->totalesFactura['total_anticipo']+= floatval($movimiento->valor_recibido);
            }
        }
    }

    public function calcularFormasPago($pagos)
    {
        foreach ($pagos as $pago) {
            $pago = (object)$pago;
            $this->totalesFactura['total_pagado']+= floatval($pago->valor);
        }
    }

    private function findNit ($id_nit)
    {
        return Nits::whereId($id_nit)
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
                'cuenta.tipos_cuenta'
            )
            ->first();
    }

    private function isAnticiposDocumentoRefe($formaPago, $idNit)
    {
        $tiposCuenta = $formaPago->cuenta->tipos_cuenta;
        foreach ($tiposCuenta as $tipoCuenta) {
            if ($tipoCuenta->id_tipo_cuenta == 4 || $tipoCuenta->id_tipo_cuenta == 8) {
                $anticipoCuenta = (new Extracto(
                    $idNit,
                    null,
                    null,
                    Carbon::now()->format('Y-m-d H:i:s'),
                    $formaPago->cuenta->id
                ))->anticipos()->first();
                return $anticipoCuenta ? $anticipoCuenta->documento_referencia : null;
            }
        }
        return null;
    }
}
