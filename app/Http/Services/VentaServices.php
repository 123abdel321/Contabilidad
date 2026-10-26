<?php

namespace App\Http\Services;

use Config;
use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

// PRINTERS
use App\Helpers\Printers\VentasPdf;
use App\Helpers\Eco\SendEcoWhatsApp;
// TRAITS
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
use App\Http\Controllers\Traits\BegFacturacionElectronica;
// MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\Nits;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacVentaPagos;
use App\Models\Sistema\PlanCuentasTipo;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\FacVentaDetalles;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacTipoFormasPago;
use App\Models\Sistema\FacProductosCombos;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacProductosBodegasMovimiento;

// HELPERS
use App\Helpers\Extracto;
use App\Helpers\Documento;
use App\Helpers\FacturaElectronica\VentaElectronicaSender;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;

use Exception;

class VentaServices
{
    use BegConsecutiveTrait;
    use BegDocumentHelpersTrait;
    use BegFacturacionElectronica;

    protected $cuentasContables = [
        "cuenta_venta"           => ["valor" => "subtotal"],
        "cuenta_venta_descuento" => ["valor" => "descuento_valor"],
        "cuenta_venta_iva"       => ["valor" => "iva_valor"],
        "cuenta_inventario"      => ["valor" => "costo_total"],
        "cuenta_costos"          => ["valor" => "costo_total"],
    ];

    // ============================================================
    // ESTADO INTERNO DE CADA VENTA
    // ============================================================

    protected $nit = null;
    protected $bodega = null;
    protected $resolucion = null;
    protected $ivaIncluido = null;

    protected $totalesPagos = [
        'total_efectivo'   => 0,
        'total_otrospagos' => 0,
    ];

    protected $totalesFactura = [
        'tope_retencion'          => 0,
        'porcentaje_rete_fuente'  => 0,
        'id_cuenta_rete_fuente'   => null,
        'propina'                 => 0,
        'subtotal'                => 0,
        'total_iva'               => 0,
        'total_rete_fuente'       => 0,
        'total_descuento'         => 0,
        'total_factura'           => 0,
    ];

    // ============================================================
    // MÉTODO CENTRAL: CREATE
    // ============================================================

    /**
     * Crea una venta completa con todos sus movimientos.
     * Es el único lugar con la lógica de venta.
     *
     * @param array $contexto  Ver documentación en cada llave.
     * @return FacVentas
     * @throws Exception
     */
    public function create(array $contexto): FacVentas
    {
        // ── 0. Validar contexto mínimo ──
        $required = ['id_usuario', 'id_empresa', 'token_db', 'id_bodega', 'id_resolucion', 'productos', 'pagos'];
        foreach ($required as $key) {
            if (empty($contexto[$key])) {
                throw new Exception("VentaServices::create - falta '{$key}' en el contexto");
            }
        }

        // ── 1. Cambiar conexión sam (el servicio es autocontenido) ──
        Config::set('database.connections.sam.database', $contexto['token_db']);
        DB::purge('sam');
        DB::reconnect('sam');

        // ── 2. Resetear estado interno (por si es una instancia reutilizada) ──
        $this->resetEstado();

        // ── 3. Resolución ──
        $this->resolucion = FacResoluciones::whereId($contexto['id_resolucion'])
            ->with('comprobante')
            ->first();

        if (!$this->resolucion) {
            throw new Exception("La resolución {$contexto['id_resolucion']} no existe");
        }

        if (!$this->resolucion->isValid) {
            throw new Exception("La resolución {$this->resolucion->nombre_completo} está agotada");
        }

        // ── 4. Consecutivo ──
        $consecutivo = $this->getNextConsecutive(
            $this->resolucion->comprobante->id,
            $contexto['fecha_manual'] ?? Carbon::now()->format('Y-m-d')
        );

        // ── 5. Empresa (para FE) ──
        $empresa = Empresa::where('id', $contexto['id_empresa'])->first();
        if (!$empresa) {
            throw new Exception("La empresa {$contexto['id_empresa']} no existe");
        }

        // ── 6. Bodega ──
        $this->bodega = FacBodegas::whereId($contexto['id_bodega'])->first();
        if (!$this->bodega) {
            throw new Exception("La bodega {$contexto['id_bodega']} no existe");
        }

        // ── 7. NIT cliente ──
        if (!empty($contexto['id_cliente'])) {
            $this->nit = $this->findCliente(
                $contexto['id_cliente'],
                $contexto['calcula_retencion'] ?? false
            );
        } elseif (!empty($contexto['auto_crear_nit']) && !empty($contexto['empresa_compradora'])) {
            $this->nit = $this->obtenerOCrearNit(
                $contexto['empresa_compradora'],
                $contexto['id_usuario']
            );
        }

        if (!$this->nit) {
            throw new Exception("No se pudo determinar el NIT del cliente");
        }

        // ── 8. Auto-crear productos si aplica ──
        $productos = $contexto['productos'];
        if (!empty($contexto['auto_crear_producto'])) {
            $productos = $this->prepararProductosAutoCreacion($productos, $contexto);
        }

        // ── 9. Ejecutar la venta ──
        DB::connection('sam')->beginTransaction();

        try {
            $venta = $this->createFacturaVenta($contexto, $productos, $consecutivo, $empresa);

            $enviarFacturaElectronica = false;

            $documentoGeneral = new Documento(
                $this->resolucion->comprobante->id,
                $venta,
                $contexto['fecha_manual'] ?? Carbon::now()->format('Y-m-d'),
                $consecutivo,
                false,
                true
            );

            // ── Detalle de productos ──
            foreach ($productos as $producto) {
                $producto   = (object) $producto;
                $productoDb = $this->findProducto($producto->id_producto);

                // COMBOS
                if ($productoDb->tipo_producto == 2) {
                    $this->procesarCombo(
                        $producto,
                        $venta,
                        $documentoGeneral,
                        $contexto['id_usuario']
                    );
                    continue;
                }

                $producto->costo_total = $productoDb->precio_inicial * $producto->cantidad;

                $bodegaProducto = FacProductosBodegas::where('id_bodega', $this->bodega->id)
                    ->where('id_producto', $producto->id_producto)
                    ->first();

                if (
                    $productoDb->familia->inventario &&
                    $bodegaProducto &&
                    $producto->cantidad > $bodegaProducto->cantidad &&
                    !$this->puedeVentaNegativa($contexto['id_usuario'])
                ) {
                    throw new Exception(
                        'La cantidad del producto ' . $productoDb->codigo . ' - ' . $productoDb->nombre . ' supera la cantidad en bodega'
                    );
                }

                $subTotal = (float) $producto->costo * $producto->cantidad;

                if ($this->ivaIncluido) {
                    $ivaIncluido = round($subTotal * ($producto->iva_porcentaje / ($producto->iva_porcentaje + 100)), 2);
                    $subTotal -= $ivaIncluido;
                }

                FacVentaDetalles::create([
                    'id_venta'                  => $venta->id,
                    'id_producto'               => $productoDb->id,
                    'id_cuenta_venta'           => $productoDb->familia->id_cuenta_venta,
                    'id_cuenta_venta_retencion' => $productoDb->familia->id_cuenta_venta_retencion,
                    'id_cuenta_venta_iva'       => $productoDb->familia->id_cuenta_venta_iva,
                    'id_cuenta_venta_descuento' => $productoDb->familia->id_cuenta_venta_descuento,
                    'descripcion'               => $productoDb->codigo . ' - ' . $productoDb->nombre,
                    'cantidad'                  => $producto->cantidad,
                    'costo'                     => $producto->costo,
                    'subtotal'                  => $subTotal,
                    'descuento_porcentaje'      => $producto->descuento_porcentaje,
                    'descuento_valor'           => $producto->descuento_valor,
                    'iva_porcentaje'            => $producto->iva_porcentaje,
                    'iva_valor'                 => $producto->iva_valor,
                    'total'                     => $producto->total,
                    'observacion'               => $producto->concepto,
                    'created_by'                => $contexto['id_usuario'],
                    'updated_by'                => $contexto['id_usuario'],
                ]);

                $this->movimientoContable(
                    $venta,
                    $productoDb,
                    $documentoGeneral,
                    (array) $producto,
                    $contexto['id_usuario']
                );

                if ($productoDb->tipo_producto != 2) {
                    if (!$bodegaProducto) {
                        $bodegaProducto = FacProductosBodegas::create([
                            'id_producto' => $producto->id_producto,
                            'id_bodega'   => $venta->id_bodega,
                            'cantidad'    => 0,
                            'created_by'  => $contexto['id_usuario'],
                            'updated_by'  => $contexto['id_usuario'],
                        ]);
                    }

                    $movimiento = new FacProductosBodegasMovimiento([
                        'id_producto'       => $producto->id_producto,
                        'id_bodega'         => $venta->id_bodega,
                        'cantidad_anterior' => $bodegaProducto->cantidad,
                        'cantidad'          => $producto->cantidad,
                        'tipo_tranferencia' => 2,
                        'inventario'        => $productoDb->familia->inventario ? 1 : 0,
                        'created_by'        => $contexto['id_usuario'],
                        'updated_by'        => $contexto['id_usuario'],
                    ]);

                    if ($bodegaProducto && $productoDb->familia->inventario) {
                        $bodegaProducto->updated_by = $contexto['id_usuario'];
                        $bodegaProducto->cantidad  -= $producto->cantidad;
                        $bodegaProducto->save();
                    }

                    $movimiento->relation()->associate($venta);
                    $venta->bodegas()->save($movimiento);
                }
            }

            // ── Rete fuente ──
            if ($this->totalesFactura['total_rete_fuente']) {
                $cuentaRetencion = PlanCuentas::whereId($this->totalesFactura['id_cuenta_rete_fuente'])->first();

                if (!in_array($cuentaRetencion->naturaleza_ventas, [PlanCuentas::DEBITO, PlanCuentas::CREDITO])) {
                    throw new Exception('La cuenta ' . $cuentaRetencion->cuenta . ' - ' . $cuentaRetencion->nombre . ' no tiene naturaleza en ventas');
                }

                $doc = new DocumentosGeneral([
                    "id_cuenta"            => $cuentaRetencion->id,
                    "id_nit"               => $cuentaRetencion->exige_nit ? $venta->id_cliente : null,
                    "id_centro_costos"     => $cuentaRetencion->exige_centro_costos ? $venta->id_centro_costos : null,
                    "concepto"             => $cuentaRetencion->exige_concepto ? 'TOTAL: ' . $this->nit->nombre_nit . ' - ' . $venta->documento_referencia : null,
                    "documento_referencia" => $cuentaRetencion->exige_documento_referencia ? $venta->documento_referencia : null,
                    "debito"               => $this->totalesFactura['total_rete_fuente'],
                    "credito"              => $this->totalesFactura['total_rete_fuente'],
                    "created_by"           => $contexto['id_usuario'],
                    "updated_by"           => $contexto['id_usuario'],
                ]);
                $documentoGeneral->addRow($doc, $cuentaRetencion->naturaleza_ventas);
            }

            // ── Propina ──
            if ($this->totalesFactura['propina']) {
                $cuentaPropina = VariablesEntorno::where('nombre', 'cuenta_propina')->first();
                $cuentaPropina = $cuentaPropina && $cuentaPropina->valor ? $cuentaPropina->valor : null;
                $cuentaPropina = PlanCuentas::whereCuenta($cuentaPropina)->first();

                if (!in_array($cuentaPropina->naturaleza_ventas, [PlanCuentas::DEBITO, PlanCuentas::CREDITO])) {
                    throw new Exception('La cuenta ' . $cuentaPropina->cuenta . ' - ' . $cuentaPropina->nombre . ' no tiene naturaleza en ventas');
                }

                $doc = new DocumentosGeneral([
                    "id_cuenta"            => $cuentaPropina->id,
                    "id_nit"               => $cuentaPropina->exige_nit ? $venta->id_cliente : null,
                    "id_centro_costos"     => $cuentaPropina->exige_centro_costos ? $venta->id_centro_costos : null,
                    "concepto"             => $cuentaPropina->exige_concepto ? 'TOTAL: ' . $this->nit->nombre_nit . ' - ' . $venta->documento_referencia : null,
                    "documento_referencia" => $cuentaPropina->exige_documento_referencia ? $venta->documento_referencia : null,
                    "debito"               => $this->totalesFactura['propina'],
                    "credito"              => $this->totalesFactura['propina'],
                    "created_by"           => $contexto['id_usuario'],
                    "updated_by"           => $contexto['id_usuario'],
                ]);
                $documentoGeneral->addRow($doc, $cuentaPropina->naturaleza_ventas);
            }

            // ── Formas de pago ──
            $saldoPendiente = $this->totalesFactura['total_factura'];

            foreach ($contexto['pagos'] as $pagoItem) {
                $pagoItem  = (object) $pagoItem;
                $formaPago = $this->findFormaPago($pagoItem->id);

                $pagoValor = $pagoItem->valor;
                if ($formaPago->tipoFormaPago && $formaPago->tipoFormaPago->codigo == FacTipoFormasPago::EFECTIVO) {
                    $pagoValor = $pagoItem->valor - $this->totalesPagos['total_cambio'];
                }

                $saldoPendiente -= $pagoValor;

                $documentoReferenciaAnticipos = $this->isAnticiposDocumentoRefe($formaPago, $venta->id_nit);

                if (count($documentoReferenciaAnticipos)) {
                    $pagoAnticipos = $pagoItem->valor;

                    foreach ($documentoReferenciaAnticipos as $anticipos) {
                        if (!$pagoAnticipos) break;

                        $anticipoUsado      = 0;
                        $anticipoDisponible = floatval($anticipos->saldo);

                        $anticipoUsado  = $anticipoDisponible >= $pagoAnticipos ? $pagoAnticipos : $anticipoDisponible;
                        $pagoAnticipos -= $anticipoUsado;

                        $doc = $this->addFormaPago(
                            $anticipos->documento_referencia,
                            $formaPago,
                            $this->nit,
                            $pagoItem,
                            $venta,
                            $anticipoUsado,
                            $saldoPendiente,
                            $contexto['id_usuario']
                        );
                        $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
                    }
                } else {
                    $doc = $this->addFormaPago(
                        $venta->documento_referencia,
                        $formaPago,
                        $this->nit,
                        $pagoItem,
                        $venta,
                        $pagoValor,
                        $saldoPendiente,
                        $contexto['id_usuario']
                    );
                    $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
                }
            }

            if (!$documentoGeneral->save()) {
                throw new Exception(json_encode($documentoGeneral->getErrors()));
            }

            $this->updateConsecutivo($this->resolucion->comprobante->id, $consecutivo);

            // ── Facturación electrónica ──
            if ($this->resolucion->tipo_resolucion == FacResoluciones::TIPO_FACTURA_ELECTRONICA) {
                $ventaElectronica = (new VentaElectronicaSender($venta))->send();

                if ($ventaElectronica["status"] >= 400) {
                    if ($ventaElectronica["zip_key"]) {
                        $venta->fe_zip_key = $ventaElectronica["zip_key"];
                        $venta->save();

                        if (($ventaElectronica["message_object"] ?? null) == 'Batch en proceso de validación.') {
                            \App\Jobs\ProcessConsultarFE::dispatch(
                                $venta->id,
                                $ventaElectronica["zip_key"],
                                $contexto['id_usuario'],
                                $empresa->id
                            )->delay(now()->addSeconds(10));
                        }
                    }

                    if (!$ventaElectronica["zip_key"] && $ventaElectronica["status"] == 500) {
                        throw new Exception($ventaElectronica["error_message"]);
                    }
                }

                if ($ventaElectronica['status'] == 200) {
                    $feSended = $ventaElectronica['status'] == 200;
                    $hasCufe  = (isset($ventaElectronica['cufe']) && $ventaElectronica['cufe']);

                    if ($feSended || $hasCufe) {
                        $venta = $this->SetFeFields($venta, $ventaElectronica['cufe'], $empresa->nit);
                        $venta->fe_zip_key   = $ventaElectronica['zip_key'];
                        $venta->fe_xml_file  = $ventaElectronica['xml_url'];
                        $venta->save();

                        if ($venta->cliente && $venta->cliente->email) {
                            $enviarFacturaElectronica = true;
                        }
                    }
                }
            }

            DB::connection('sam')->commit();

            // ── Email (fuera de la transacción) ──
            if ($enviarFacturaElectronica) {
                try {
                    $claveUrl = $this->generarClavePDF(
                        $empresa->id,
                        $this->resolucion->comprobante->id,
                        $venta->consecutivo,
                        $venta->fecha_manual
                    );

                    $pdf = (new VentasPdf($empresa, $venta, $claveUrl))->buildPdf()->saveStorage();

                    $this->sendEmailFactura(
                        $contexto['token_db'],
                        $venta->cliente->email,
                        $venta,
                        $pdf
                    );
                } catch (\Throwable $e) {
                    Log::warning('Venta creada, pero falló el envío de email', [
                        'venta_id' => $venta->id,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }

            // ── Factura WhatsApp ──
            if ($this->nit->notificacion_whatsapp) {
                try {
                    $this->enviarWhatsappFactura($venta, $empresa, $contexto);
                } catch (\Throwable $e) {
                    Log::warning('Venta creada, pero falló el envío de WhatsApp', [
                        'venta_id' => $venta->id,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }

            return $venta;

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            throw $e;
        }
    }

    // ============================================================
    // MÉTODOS PRIVADOS (movidos del controller)
    // ============================================================

    protected function resetEstado(): void
    {
        $this->nit          = null;
        $this->bodega       = null;
        $this->resolucion   = null;
        $this->ivaIncluido  = null;

        $this->totalesPagos = [
            'total_efectivo'   => 0,
            'total_otrospagos' => 0,
        ];

        $this->totalesFactura = [
            'tope_retencion'         => 0,
            'porcentaje_rete_fuente' => 0,
            'id_cuenta_rete_fuente'  => null,
            'propina'                => 0,
            'subtotal'               => 0,
            'total_iva'              => 0,
            'total_rete_fuente'      => 0,
            'total_descuento'        => 0,
            'total_factura'          => 0,
        ];
    }

    /**
     * Auto-crear productos (suscripción). Busca por nombre, si no existe lo crea.
     */
    protected function prepararProductosAutoCreacion(array $productos, array $contexto): array
    {
        $idFamilia = $contexto['id_familia'] ?? null;

        if (!$idFamilia) {
            throw new Exception('Falta id_familia en el contexto para auto_crear_producto');
        }

        $resultado = [];

        foreach ($productos as $item) {
            $nombre = $item['nombre_producto'] ?? null;
            $precio = (float) ($item['costo'] ?? 0);
            $cantidad = max(1, (int) ($item['cantidad'] ?? 1));

            if (!$nombre || $precio <= 0) continue;

            $producto = FacProductos::where('nombre', $nombre)->first();

            if (!$producto) {
                $producto = $this->crearProductoServicio($nombre, $idFamilia, $precio, $contexto['id_usuario']);
            }

            $resultado[] = [
                'id_producto'          => $producto->id,
                'cantidad'             => $cantidad,
                'costo'                => $precio,
                'costo_total'          => 0,
                'subtotal'             => $precio * $cantidad,
                'descuento_porcentaje' => 0,
                'descuento_valor'      => 0,
                'iva_porcentaje'       => 0,
                'iva_valor'            => 0,
                'total'                => $precio * $cantidad,
                'concepto'             => $item['concepto'] ?? null,
            ];
        }

        return $resultado;
    }

    protected function crearProductoServicio(string $nombre, int $idFamilia, float $precio, int $userId): FacProductos
    {
        $ultimoCodigo = FacProductos::where('codigo', 'REGEXP', '^[0-9]{2}$')
            ->orderByDesc('codigo')
            ->value('codigo');

        $siguiente = $ultimoCodigo ? ((int) $ultimoCodigo + 1) : 1;
        $codigo    = str_pad($siguiente, 2, '0', STR_PAD_LEFT);

        $producto = FacProductos::create([
            'id_familia'          => $idFamilia,
            'id_padre'            => null,
            'estado'              => 1,
            'tipo_producto'       => 0,
            'fraccion_hora'       => 0,
            'codigo'              => $codigo,
            'nombre'              => $nombre,
            'precio'              => $precio,
            'precio_2'            => $precio,
            'precio_minimo'       => $precio,
            'precio_inicial'      => 0,
            'porcentaje_utilidad' => 100,
            'valor_utilidad'      => $precio,
            'variante'            => 0,
            'created_by'          => $userId,
            'updated_by'          => $userId,
        ]);

        Log::info('Producto servicio creado', [
            'producto_id' => $producto->id,
            'codigo'      => $codigo,
            'nombre'      => $nombre,
        ]);

        return $producto;
    }

    protected function obtenerOCrearNit(Empresa $empresa, int $userId): ?Nits
    {
        if ($empresa->nit) {
            $nit = Nits::where('numero_documento', $empresa->nit)->first();
            if ($nit) return $nit;
        }

        $idTipoDocumento = $empresa->tipo_contribuyente == 2 ? 3 : 6;
        $idPaisColombia  = 53;

        return Nits::create([
            'id_tipo_documento'   => $idTipoDocumento,
            'id_pais'             => $idPaisColombia,
            'numero_documento'    => $empresa->nit,
            'digito_verificacion' => $empresa->dv,
            'tipo_contribuyente'  => $empresa->tipo_contribuyente,
            'razon_social'        => $empresa->tipo_contribuyente == 1 ? $empresa->razon_social : null,
            'primer_nombre'       => $empresa->primer_nombre,
            'otros_nombres'       => $empresa->otros_nombres,
            'primer_apellido'     => $empresa->primer_apellido,
            'segundo_apellido'    => $empresa->segundo_apellido,
            'direccion'           => $empresa->direccion,
            'email'               => $empresa->email,
            'telefono_1'          => $empresa->telefono,
            'created_by'          => $userId,
            'updated_by'          => $userId,
        ]);
    }

    protected function puedeVentaNegativa(int $idUsuario): bool
    {
        $user = \App\Models\User::find($idUsuario);
        return $user ? $user->can('venta negativa') : false;
    }

    protected function createFacturaVenta(array $contexto, array $productos, int $consecutivo, Empresa $empresa): FacVentas
    {
        $this->calcularTotales($productos, $contexto['calcula_retencion'] ?? false);

        $propina = $contexto['propina'] ?? 0;
        if ($propina) {
            $this->totalesFactura['propina'] = $propina;
            $this->totalesFactura['total_factura'] += $propina;
        }

        $this->calcularFormasPago($contexto['pagos']);

        $venta = FacVentas::create([
            'id_cliente'                 => $this->nit->id,
            'id_resolucion'              => $contexto['id_resolucion'],
            'id_comprobante'             => $this->resolucion->comprobante->id,
            'id_bodega'                  => $contexto['id_bodega'],
            'id_centro_costos'           => $this->bodega->id_centro_costos,
            'id_vendedor'                => $contexto['id_vendedor'] ?? null,
            'fecha_manual'               => $contexto['fecha_manual'] ?? Carbon::now()->format('Y-m-d'),
            'consecutivo'                => $consecutivo,
            'documento_referencia'       => $contexto['documento_referencia'],
            'subtotal'                   => $this->totalesFactura['subtotal'],
            'total_iva'                  => $this->totalesFactura['total_iva'],
            'total_descuento'            => $this->totalesFactura['total_descuento'],
            'total_rete_fuente'          => $this->totalesFactura['total_rete_fuente'],
            'total_cambio'               => $this->totalesPagos['total_cambio'],
            'propina'                    => $this->totalesFactura['propina'],
            'porcentaje_rete_fuente'     => $this->totalesFactura['porcentaje_rete_fuente'],
            'codigo_tipo_documento_dian' => CodigoDocumentoDianTypes::VENTA_NACIONAL,
            'total_factura'              => $this->totalesFactura['total_factura'],
            'observacion'                => $contexto['observacion'] ?? null,
            'created_by'                 => $contexto['id_usuario'],
            'updated_by'                 => $contexto['id_usuario'],
        ]);

        return $venta;
    }

    protected function addFormaPago($documentoReferencia, $formaPago, $nit, $pagoItem, $venta, $valor, $saldo, int $idUsuario)
    {
        FacVentaPagos::create([
            'id_venta'      => $venta->id,
            'id_forma_pago' => $pagoItem->id,
            'valor'         => $valor,
            'saldo'         => $saldo,
            'created_by'    => $idUsuario,
            'updated_by'    => $idUsuario,
        ]);

        $doc = new DocumentosGeneral([
            'id_cuenta'            => $formaPago->cuenta->id,
            'id_nit'               => $formaPago->cuenta->exige_nit ? $nit->id : null,
            'id_centro_costos'     => $formaPago->cuenta->exige_centro_costos ? $venta->id_centro_costos : null,
            'concepto'             => $formaPago->cuenta->exige_concepto ? 'TOTAL: ' . $nit->nombre_nit . ' - ' . $venta->documento_referencia : null,
            'documento_referencia' => $formaPago->cuenta->exige_documento_referencia ? $documentoReferencia : null,
            'debito'               => $valor,
            'credito'              => $valor,
            'created_by'           => $idUsuario,
            'updated_by'           => $idUsuario,
        ]);

        return $doc;
    }

    protected function calcularFormasPago($pagos)
    {
        $totalCambio = 0;
        $totalPagos  = 0;

        foreach ($pagos as $pago) {
            $pago = (object) $pago;
            $totalPagos += $pago->valor;

            if ($pago->id == 1) $this->totalesPagos['total_efectivo']   += $pago->valor;
            else                $this->totalesPagos['total_otrospagos'] += $pago->valor;
        }

        if ($this->totalesFactura['total_factura'] < $totalPagos) {
            $totalCambio = $totalPagos - $this->totalesFactura['total_factura'];
        }

        $this->totalesPagos['total_cambio'] = $totalCambio;
    }

    protected function isAnticiposDocumentoRefe($formaPago, $idNit)
    {
        $tiposCuenta = $formaPago->cuenta->tipos_cuenta;
        foreach ($tiposCuenta as $tipoCuenta) {
            if ($tipoCuenta->id_tipo_cuenta == PlanCuentasTipo::TIPO_CUENTA_ANTICIPO_CLIENTES_XP) {
                return (new Extracto(
                    $idNit,
                    null,
                    null,
                    Carbon::now()->format('Y-m-d H:i:s'),
                    $formaPago->cuenta->id
                ))->anticiposDiscriminados()->get();
            }
        }
        return [];
    }

    protected function calcularTotales($productos, $calcula_retencion)
    {
        $ivaIncluido = VariablesEntorno::where('nombre', 'iva_incluido')->first();
        $this->ivaIncluido = $ivaIncluido ? $ivaIncluido->valor : false;

        $this->totalesFactura['subtotal_sin_iva'] = 0;
        $this->totalesFactura['subtotal_con_iva'] = 0;

        foreach ($productos as $producto) {
            $producto   = (object) $producto;
            $productoDb = FacProductos::where('id', $producto->id_producto)
                ->with(
                    'familia.cuenta_venta',
                    'familia.cuenta_venta_retencion.impuesto',
                    'familia.cuenta_venta_iva.impuesto',
                    'familia.cuenta_venta_descuento'
                )
                ->first();

            $cuentaRetencion = $productoDb->familia->cuenta_venta_retencion;
            if ($cuentaRetencion && $cuentaRetencion->impuesto) {
                $impuesto  = $cuentaRetencion->impuesto;
                $baseActual = floatval($impuesto->base);
                if (floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente'] && $baseActual > floatval($this->totalesFactura['tope_retencion'])) {
                    $this->totalesFactura['porcentaje_rete_fuente'] = floatval($impuesto->porcentaje);
                    $this->totalesFactura['tope_retencion']         = floatval($impuesto->base);
                    $this->totalesFactura['id_cuenta_rete_fuente']  = $cuentaRetencion->id;
                }
            }

            $iva = 0;
            $costo = $producto->costo;
            $totalPorCantidad = $producto->cantidad * $costo;
            $cuentaIva = $productoDb->familia->cuenta_venta_iva;

            if ($cuentaIva && $cuentaIva->impuesto) {
                $impuesto = $cuentaIva->impuesto;
                if (floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente']) {
                    $this->totalesFactura['porcentaje_iva'] = floatval($impuesto->porcentaje);
                    $this->totalesFactura['id_cuenta_iva']  = $cuentaIva->id;
                }

                if ($this->ivaIncluido) {
                    $subTotal      = $totalPorCantidad - $producto->descuento_valor;
                    $porcentajeIva = $this->totalesFactura['porcentaje_iva'];
                    $iva           = round($subTotal * ($porcentajeIva / (100 + $porcentajeIva)), 2);
                    $valor_linea_sin_iva = round($subTotal - $iva, 2);
                    $valor_linea_con_iva = round($subTotal, 2);
                } else {
                    $iva = round(($totalPorCantidad - $producto->descuento_valor) * ($this->totalesFactura['porcentaje_iva'] / 100), 2);
                    $valor_linea_sin_iva = round(($producto->cantidad * $costo) - $producto->descuento_valor, 2);
                    $valor_linea_con_iva = $valor_linea_sin_iva + $iva;
                }
            } else {
                $valor_linea_sin_iva = round(($producto->cantidad * $costo) - $producto->descuento_valor, 2);
                $valor_linea_con_iva = $valor_linea_sin_iva;
            }

            $this->totalesFactura['subtotal_sin_iva'] += $valor_linea_sin_iva;
            $this->totalesFactura['subtotal_con_iva'] += $valor_linea_con_iva;
            $this->totalesFactura['total_iva']        += $iva;
            $this->totalesFactura['total_descuento']  += $producto->descuento_valor;
            $this->totalesFactura['subtotal']          = $this->totalesFactura['subtotal_sin_iva'];
            $this->totalesFactura['total_factura']     = $this->totalesFactura['subtotal_con_iva'];
        }

        if (
            $calcula_retencion &&
            $this->totalesFactura['total_factura'] >= $this->totalesFactura['tope_retencion'] &&
            $this->totalesFactura['porcentaje_rete_fuente'] > 0
        ) {
            $base_retencion    = $this->totalesFactura['subtotal_sin_iva'];
            $total_rete_fuente = round($base_retencion * ($this->totalesFactura['porcentaje_rete_fuente'] / 100), 2);

            $this->totalesFactura['total_rete_fuente'] = $total_rete_fuente;
            $this->totalesFactura['total_factura']     = round($this->totalesFactura['total_factura'] - $total_rete_fuente, 2);
        } else {
            $this->totalesFactura['id_cuenta_rete_fuente'] = null;
            $this->totalesFactura['total_rete_fuente']     = 0;
        }
    }

    protected function findCliente($id_cliente, $calcula_retencion)
    {
        $nit = Nits::whereId($id_cliente)
            ->select(
                '*',
                DB::raw("CASE
                    WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END AS nombre_nit"),
                'id_responsabilidades'
            )
            ->first();

        if (!$nit) {
            throw new Exception("El NIT {$id_cliente} no existe");
        }

        if ($calcula_retencion != $nit->retencion) {
            $nit->retencion = $calcula_retencion;
            $nit->save();
        }

        return $nit;
    }

    protected function findProducto($id_producto)
    {
        $producto = FacProductos::where('id', $id_producto)
            ->with(
                'familia.cuenta_venta',
                'familia.cuenta_venta_retencion.impuesto',
                'familia.cuenta_venta_iva.impuesto',
                'familia.cuenta_venta_descuento',
                'familia.cuenta_inventario',
                'familia.cuenta_costos'
            )
            ->first();

        if ($producto->utilizado_captura == 0) {
            $producto->utilizado_captura = 1;
            $producto->save();
        }

        return $producto;
    }

    protected function findFormaPago($id_forma_pago)
    {
        return FacFormasPago::where('id', $id_forma_pago)
            ->with('cuenta', 'tipoFormaPago')
            ->first();
    }

    // ============================================================
    // MOVIMIENTO CONTABLE (adaptado con $idUsuario)
    // ============================================================

    public function movimientoContable(
        FacVentas $venta,
        FacProductos $productoDb,
        Documento $documentoGeneral,
        array $producto,
        int $idUsuario
    ) {
        if (!$venta->relationLoaded('cliente')) {
            $venta->load('cliente');
        }

        foreach ($this->cuentasContables as $cuentaKey => $cuenta) {
            $cuentaRecord = $productoDb->familia->{$cuentaKey};
            $keyTotalItem = $cuenta["valor"];

            if ($productoDb->tipo_producto == 1 && in_array($cuentaKey, ['cuenta_inventario', 'cuenta_costos'])) continue;
            if ($productoDb->precio_inicial <= 0 && $cuentaKey == 'cuenta_costos') continue;
            if (!$productoDb->familia->id_cuenta_inventario && in_array($cuentaKey, ['cuenta_inventario', 'cuenta_costos'])) continue;

            if (($producto[$keyTotalItem] ?? 0) > 0) {
                if (!$cuentaRecord) {
                    throw new Exception(
                        'La cuenta ' . str_replace('cuenta_venta_', '', $cuentaKey) .
                        ' no se encuentra configurada en la familia: ' .
                        $productoDb->familia->codigo . ' - ' . $productoDb->familia->nombre
                    );
                }

                $concepto = "VENTA: {$venta->cliente->nombre_completo} - {$venta->documento_referencia}";
                if (!empty($producto['concepto'])) {
                    $concepto .= " - {$producto['concepto']}";
                }

                $doc = new DocumentosGeneral([
                    "id_cuenta"            => $cuentaRecord->id,
                    "id_nit"               => $cuentaRecord->exige_nit ? $venta->id_cliente : null,
                    "id_centro_costos"     => $cuentaRecord->exige_centro_costos ? $venta->id_centro_costos : null,
                    "concepto"             => $cuentaRecord->exige_concepto ? $concepto : null,
                    "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $venta->documento_referencia : null,
                    "debito"               => $cuentaRecord->naturaleza_ventas == PlanCuentas::DEBITO  ? $producto[$keyTotalItem] : 0,
                    "credito"              => $cuentaRecord->naturaleza_ventas == PlanCuentas::CREDITO ? $producto[$keyTotalItem] : 0,
                    "created_by"           => $idUsuario,
                    "updated_by"           => $idUsuario,
                ]);

                $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_ventas);
            }
        }
    }

    // ============================================================
    // PROCESAR COMBO (adaptado con $idUsuario)
    // ============================================================

    public function procesarCombo(
        $productoCombo,
        FacVentas $venta,
        Documento $documentoGeneral,
        $userId
    ) {
        $hijos = FacProductosCombos::where('id_combo', $productoCombo->id_producto)->get();

        if ($hijos->isEmpty()) {
            throw new Exception("El combo {$productoCombo->id_producto} no tiene productos asociados.");
        }

        $productosHijos = FacProductos::whereIn('id', $hijos->pluck('id_producto'))
            ->with([
                'familia.cuenta_venta',
                'familia.cuenta_venta_retencion.impuesto',
                'familia.cuenta_venta_iva.impuesto',
                'familia.cuenta_venta_descuento',
                'familia.cuenta_inventario',
                'familia.cuenta_costos',
            ])
            ->get()
            ->keyBy('id');

        $costosTotalesHijos = [];
        $sumaCostos = 0;

        foreach ($hijos as $hijo) {
            $productoHijo = $productosHijos->get($hijo->id_producto);
            if (!$productoHijo) {
                throw new Exception("Producto hijo {$hijo->id_producto} no encontrado.");
            }

            $cantidadHijo    = $productoCombo->cantidad * $hijo->cantidad;
            $costoInventario = $productoHijo->precio_inicial * $cantidadHijo;

            $costosTotalesHijos[$hijo->id_producto] = [
                'producto'         => $productoHijo,
                'cantidad'         => $cantidadHijo,
                'costo_inventario' => $costoInventario,
            ];

            $sumaCostos += $costoInventario;
        }

        if ($sumaCostos == 0) {
            throw new Exception("La suma de costos de inventario del combo es cero.");
        }

        $totalesAcumulados = ['subtotal' => 0, 'iva_valor' => 0, 'descuento_valor' => 0, 'total' => 0, 'costo_total' => 0];

        foreach ($costosTotalesHijos as $data) {
            $productoHijo        = $data['producto'];
            $cantidadHijo        = $data['cantidad'];
            $costoInventarioHijo = $data['costo_inventario'];
            $porcentaje          = $costoInventarioHijo / $sumaCostos;

            $subtotalHijo  = round($productoCombo->subtotal * $porcentaje, 2);
            $ivaHijo       = round($productoCombo->iva_valor * $porcentaje, 2);
            $descuentoHijo = round($productoCombo->descuento_valor * $porcentaje, 2);
            $totalHijo     = round($productoCombo->total * $porcentaje, 2);

            $productoVirtual = [
                'id_producto'          => $productoHijo->id,
                'cantidad'             => $cantidadHijo,
                'costo'                => $productoHijo->precio_inicial,
                'costo_total'          => $costoInventarioHijo,
                'subtotal'             => $subtotalHijo,
                'iva_valor'            => $ivaHijo,
                'descuento_valor'      => $descuentoHijo,
                'total'                => $totalHijo,
                'concepto'             => $productoCombo->concepto ?? null,
                'iva_porcentaje'       => $productoCombo->iva_porcentaje ?? 0,
                'descuento_porcentaje' => $productoCombo->descuento_porcentaje ?? 0,
            ];

            FacVentaDetalles::create([
                'id_venta'                  => $venta->id,
                'id_producto'               => $productoHijo->id,
                'id_cuenta_venta'           => $productoHijo->familia->id_cuenta_venta ?? null,
                'id_cuenta_venta_retencion' => $productoHijo->familia->id_cuenta_venta_retencion ?? null,
                'id_cuenta_venta_iva'       => $productoHijo->familia->id_cuenta_venta_iva ?? null,
                'id_cuenta_venta_descuento' => $productoHijo->familia->id_cuenta_venta_descuento ?? null,
                'descripcion'               => $productoHijo->codigo . ' - ' . $productoHijo->nombre,
                'cantidad'                  => $cantidadHijo,
                'costo'                     => $productoHijo->precio_inicial,
                'subtotal'                  => $subtotalHijo,
                'descuento_porcentaje'      => $productoVirtual['descuento_porcentaje'],
                'descuento_valor'           => $descuentoHijo,
                'iva_porcentaje'            => $productoVirtual['iva_porcentaje'],
                'iva_valor'                 => $ivaHijo,
                'total'                     => $totalHijo,
                'observacion'               => $productoVirtual['concepto'],
                'created_by'                => $userId,
                'updated_by'                => $userId,
            ]);

            $this->movimientoContable($venta, $productoHijo, $documentoGeneral, $productoVirtual, $userId);

            $bodegaProducto = FacProductosBodegas::where('id_bodega', $venta->id_bodega)
                ->where('id_producto', $productoHijo->id)
                ->first();

            if (!$bodegaProducto) {
                $bodegaProducto = FacProductosBodegas::create([
                    'id_producto' => $productoHijo->id,
                    'id_bodega'   => $venta->id_bodega,
                    'cantidad'    => 0,
                    'created_by'  => $userId,
                    'updated_by'  => $userId,
                ]);
            }

            $movimiento = new FacProductosBodegasMovimiento([
                'id_producto'       => $productoHijo->id,
                'id_bodega'         => $venta->id_bodega,
                'cantidad_anterior' => $bodegaProducto->cantidad,
                'cantidad'          => $cantidadHijo,
                'tipo_tranferencia' => 2,
                'inventario'        => $productoHijo->familia->inventario ? 1 : 0,
                'created_by'        => $userId,
                'updated_by'        => $userId,
            ]);

            if ($bodegaProducto && $productoHijo->familia->inventario) {
                $bodegaProducto->updated_by = $userId;
                $bodegaProducto->cantidad  -= $cantidadHijo;
                $bodegaProducto->save();
            }

            $movimiento->relation()->associate($venta);
            $venta->bodegas()->save($movimiento);

            $totalesAcumulados['subtotal']        += $subtotalHijo;
            $totalesAcumulados['iva_valor']       += $ivaHijo;
            $totalesAcumulados['descuento_valor'] += $descuentoHijo;
            $totalesAcumulados['total']           += $totalHijo;
            $totalesAcumulados['costo_total']     += $costoInventarioHijo;
        }

        return $totalesAcumulados;
    }

    /**
     * Envía la factura al cliente por WhatsApp (ECO).
     * Solo se llama si el NIT tiene notificacion_whatsapp = true.
     */
    protected function enviarWhatsappFactura(FacVentas $venta, Empresa $empresa, array $contexto): void
    {
        // ── Teléfono del cliente ──
        $telefono = $this->nit->telefono_1 ?? null;

        if (!$telefono) {
            Log::info('WhatsApp factura omitido: NIT sin teléfono', [
                'venta_id' => $venta->id,
                'nit_id'   => $this->nit->id,
            ]);
            return;
        }

        $telefono = preg_replace('/\D/', '', (string) $telefono);

        if (strlen($telefono) < 10) {
            Log::info('WhatsApp factura omitido: teléfono inválido', [
                'venta_id' => $venta->id,
                'telefono' => $telefono,
            ]);
            return;
        }

        if (strlen($telefono) === 10) {
            $telefono = '57' . $telefono;
        }

        // ── Token ECO ──
        $ecoToken = VariablesEntorno::where('nombre', 'eco_login')->first();
        $ecoToken = $ecoToken?->valor ?? null;

        if (!$ecoToken) {
            Log::info('WhatsApp factura omitido: token ECO no configurado', [
                'venta_id' => $venta->id,
            ]);
            return;
        }

        // ── Datos para la plantilla ──
        $nombreCliente   = $this->nit->nombre_completo ?: 'Cliente';
        $tipoDocumento   = 'Factura';
        $numeroDocumento = $venta->documento_referencia ?: $venta->consecutivo;
        $valorDocumento  = number_format((float) $venta->total_factura, 2, ',', '.');
        $fechaDocumento  = $venta->fecha_manual
            ? Carbon::parse($venta->fecha_manual)->format('Y-m-d')
            : Carbon::now()->format('Y-m-d');
        $estadoDocumento = '';
        $nombreEmpresa   = $empresa->razon_social ?: 'PORTAFOLIO ERP SAS';

        // ── Clave del PDF ──
        $claveUrl = $this->generarClavePDF(
            $empresa->id,
            $this->resolucion->comprobante->id,
            $venta->consecutivo,
            $venta->fecha_manual
        );

        $linkFactura = 'documentos-generales-pdf?code=' . $claveUrl;

        // ── Parámetros ──
        $whatsappData = [
            "1" => $nombreCliente,
            "2" => $tipoDocumento,
            "3" => $numeroDocumento,
            "4" => $valorDocumento,
            "5" => $fechaDocumento,
            "6" => $estadoDocumento,
            "7" => $nombreEmpresa,
            "8" => $linkFactura,
        ];

        // ── Enviar ──
        $sender = new SendEcoWhatsApp(
            $telefono,
            $whatsappData,
            [
                'id_nit'           => $this->nit->id,
                'nombre_completo'  => $nombreCliente,
                'numero_documento' => $this->nit->numero_documento,
            ],
            'general_documento_portafolioerp',
            'general_documento_portafolioerp'
        );

        $sender->setToken($ecoToken);
        $response = $sender->send();

        Log::info('WhatsApp factura enviada', [
            'venta_id' => $venta->id,
            'telefono' => $telefono,
            'link'     => $linkFactura,
            'response' => $response,
        ]);

        if (!$response || !isset($response->status) || $response->status >= 400) {
            Log::warning('WhatsApp factura falló en ECO', [
                'venta_id' => $venta->id,
                'response' => $response,
            ]);
        }
    }
}