<?php

use Illuminate\Http\Request;
use App\Events\PrivateMessage;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstaladorController;
//TABLAS
use App\Http\Controllers\Tablas\NitController;
use App\Http\Controllers\Tablas\ExogenaController;
use App\Http\Controllers\Tablas\BodegasController;
use App\Http\Controllers\Tablas\FamiliasController;
use App\Http\Controllers\Tablas\ImpuestoController;
use App\Http\Controllers\Tablas\VariantesController;
use App\Http\Controllers\Tablas\ProductosController;
use App\Http\Controllers\Tablas\PlanCuentaController;
use App\Http\Controllers\Tablas\VendedoresController;
use App\Http\Controllers\Tablas\FormasPagoController;
use App\Http\Controllers\Tablas\UbicacionesController;
use App\Http\Controllers\Tablas\CentroCostoController;
use App\Http\Controllers\Tablas\PresupuestoController;
use App\Http\Controllers\Tablas\ComprobantesController;
use App\Http\Controllers\Tablas\ResolucionesController;
use App\Http\Controllers\Tablas\ConceptoGastosController;
use App\Http\Controllers\Tablas\CargueDescargueController;
use App\Http\Controllers\Tablas\Nomina\PeriodosController;
use App\Http\Controllers\Tablas\Nomina\ContratosController;
use App\Http\Controllers\Tablas\Nomina\ConfiguracionProvisiones;
use App\Http\Controllers\Tablas\Nomina\AdministradorasController;
use App\Http\Controllers\Tablas\Nomina\ConceptosNominaController;
//INFORMES
use App\Http\Controllers\Informes\ImpuestosController;
use App\Http\Controllers\Informes\ResultadosController;
use App\Http\Controllers\Informes\EstadoActualController;
use App\Http\Controllers\Informes\ResumenCarteraController;
use App\Http\Controllers\Informes\VentasAcumuladasController;
use App\Http\Controllers\Informes\EstadoComprobanteController;
use App\Http\Controllers\Informes\ResumenComprobantesController;
use App\Http\Controllers\Informes\DocumentosGeneralesController;
//CAPTURAS
use App\Http\Controllers\Capturas\VentaController;
use App\Http\Controllers\Capturas\PagosController;
use App\Http\Controllers\Capturas\CompraController;
use App\Http\Controllers\Capturas\GastosController;
use App\Http\Controllers\Capturas\PedidoController;
use App\Http\Controllers\Capturas\ReservaController;
use App\Http\Controllers\Capturas\RecibosController;
use App\Http\Controllers\Capturas\ParqueaderoController;
use App\Http\Controllers\Capturas\NotaCreditoController;
use App\Http\Controllers\Capturas\VentasGeneralesController;
use App\Http\Controllers\Capturas\DocumentoGeneralController;
use App\Http\Controllers\Capturas\MovimientoInventarioController;

use App\Http\Controllers\Capturas\Nomina\VacacionesController;
use App\Http\Controllers\Capturas\Nomina\CausarNominaController;
use App\Http\Controllers\Capturas\Nomina\CesantiasInteresController;
use App\Http\Controllers\Capturas\Nomina\NovedadesGeneralesController;
use App\Http\Controllers\Capturas\Nomina\CausarProvicionadaController;
use App\Http\Controllers\Capturas\Nomina\LiquidacionDefinitivaController;
//IMPORTADORES
use App\Http\Controllers\Importador\NitsImportadorController;
use App\Http\Controllers\Importador\ProductoImportadorController;
use App\Http\Controllers\Importador\DocumentosImportadorController;
//SISTEMA
use App\Http\Controllers\Sistema\UbicacionController;
//CONFIGURACION
use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Configuracion\UsuariosController;
use App\Http\Controllers\Configuracion\ReunionesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', 'App\Http\Controllers\ApiController@login');
Route::post('register', 'App\Http\Controllers\ApiController@register');
Route::post('register-api-token', 'App\Http\Controllers\InstaladorController@createEmpresaApiToken');
Route::post('public-event', function (Request $request) {
    event(new PrivateMessage(['mensaje' => 'hola mundo', 'id_usuario' => 1]));
    return event(new PrivateMessage(['mensaje' => 'hola mundo', 'id_usuario' => 1]));
});
//UBICACION
Route::controller(UbicacionController::class)->group(function () {
    Route::get('paises', 'getPais');
    Route::get('ciudades', 'getCiudad');
    Route::get('departamentos', 'getDepartamento');
});

Route::group(['middleware' => ['auth:sanctum']], function() {

    //EMPRESA
    Route::get("empresa","App\Http\Controllers\ApiController@getEmpresas");
    Route::get("usuario-accion","App\Http\Controllers\ApiController@getUsuario");
    Route::post("empresa","App\Http\Controllers\InstaladorController@createEmpresa");
    Route::post("seleccionar-empresa","App\Http\Controllers\ApiController@setEmpresa");
    Route::get('responsabilidades-combo', 'App\Http\Controllers\Configuracion\EmpresaController@comboResponsabilidades');
    Route::get('actividad-economica-combo', 'App\Http\Controllers\Configuracion\EmpresaController@comboActividadEconomica');
    //CONFIGURACION
    Route::put('empresa', 'App\Http\Controllers\Configuracion\EmpresaController@updateEmpresa');
    Route::put('entorno', 'App\Http\Controllers\Configuracion\EntornoController@updateEntorno');

    //ANIO CERRADO
    Route::get("anio-cerrado","App\Http\Controllers\Capturas\DocumentoGeneralController@getAnioCerrado");
    
    //EMPRESA SELECCIONADA
    Route::group(['middleware' => ['clientconnection']], function() {

        //IMPORTADORES PRECIO PRODUCTOS
        Route::controller(ProductoImportadorController::class)->group(function () {
            Route::get('producto-precio-cache-import', 'generate');
            Route::post('producto-precio-actualizar', 'actualizar');
        });
        //IMPORTADORES NITS
        Route::controller(NitsImportadorController::class)->group(function () {
            Route::get('nits-cache-import', 'generate');
            Route::post('nits-actualizar-import', 'actualizar');
        });
        //IMPORTADORES DOCUMENTOS
        Route::controller(DocumentosImportadorController::class)->group(function () {
            Route::get('documentos-cache-import', 'generate');
            Route::post('documentos-actualizar-import', 'actualizar');
            Route::post('documentos-validar-import', 'validar');
        });
        //EMPRESA
        Route::controller(EmpresaController::class)->group(function () {
            Route::get('empresas', 'generate');
        });

        //INFORMES
        Route::get('extracto', 'App\Http\Controllers\Informes\ExtractoController@extracto');
        Route::get('extracto-anticipos', 'App\Http\Controllers\Informes\ExtractoController@extractoAnticipos');
        Route::get('existe-factura', 'App\Http\Controllers\Informes\ExtractoController@existeFactura');
        Route::get('extractos-informe', 'App\Http\Controllers\Informes\ExtractoController@generateInforme');
        Route::get('extractos-show', 'App\Http\Controllers\Informes\ExtractoController@show');

        //DOCUMENTOS CAPTURADOS
        Route::get('documento', 'App\Http\Controllers\Informes\DocumentoController@generate');
        Route::get('documento-print', 'App\Http\Controllers\Informes\DocumentoController@print');
        //BALANCE
        Route::get('balances', 'App\Http\Controllers\Informes\BalanceController@generate');
        Route::get('balances-show', 'App\Http\Controllers\Informes\BalanceController@show');
        Route::get('balances-find', 'App\Http\Controllers\Informes\BalanceController@find');
        Route::post('balances-excel', 'App\Http\Controllers\Informes\BalanceController@exportExcel');
        //AUXILIAR
        Route::get('auxiliares', 'App\Http\Controllers\Informes\AuxiliarController@generate');
        Route::get('auxiliares-show', 'App\Http\Controllers\Informes\AuxiliarController@show');
        Route::get('auxiliares-find', 'App\Http\Controllers\Informes\AuxiliarController@find');
        Route::post('auxiliares-excel', 'App\Http\Controllers\Informes\AuxiliarController@exportExcel');
        //CARTERA
        Route::get('cartera', 'App\Http\Controllers\Informes\CarteraController@generate');
        Route::get('cartera-show', 'App\Http\Controllers\Informes\CarteraController@show');
        Route::get('cartera-find', 'App\Http\Controllers\Informes\CarteraController@find');
        Route::post('cartera-excel', 'App\Http\Controllers\Informes\CarteraController@exportExcel');
        //CARTERA
        Route::controller(ImpuestosController::class)->group(function () {
            Route::get('impuestos', 'generate');
            Route::get('documentos', 'show');
            Route::get('impuestos-find', 'find');
        });
        //DOCUMENTOS GENERALES
        Route::controller(DocumentosGeneralesController::class)->group(function () {
            Route::get('documentos-generales', 'generate');
            Route::get('documentos-generales-show', 'show');
            Route::post('documentos-generales-delete', 'delete');
            Route::post('documentos-generales-excel', 'exportExcel');
            Route::post('documentos-generales-pdf', 'exportPdf');
        });
        //VENTAS GENERALES
        Route::controller(VentasGeneralesController::class)->group(function () {
            Route::get('ventas-generales', 'generate');
            Route::get('ventas-generales-show', 'show');
        });
        //INFORME ESTADO ACTUAL
        Route::controller(EstadoActualController::class)->group(function () {
            Route::get('estado-actual', 'generate');
            Route::get('estado-actual-show', 'show');
            Route::get('estado-actual-find', 'find');
        });
        //INFORME RESUMEN CARTERA
        Route::controller(ResumenCarteraController::class)->group(function () {
            Route::get('resumen-cartera', 'generate');
            Route::get('resumen-cartera-show', 'show');
            Route::post('resumen-cartera-excel', 'exportExcel');
        });
        //INFORME ESTADO COMPROBANTE
        Route::controller(EstadoComprobanteController::class)->group(function () {
            Route::get('estado-comprobante', 'generate');
            Route::get('estado-comprobante-show', 'show');
            Route::get('estado-comprobante-find', 'find');
        });
        //INFORME RESUMEN COMPROBANTE
        Route::controller(ResumenComprobantesController::class)->group(function () {
            Route::get('resumen-comprobante', 'generate');
            Route::get('resumen-comprobante-show', 'show');
            Route::post('resumen-comprobante-excel', 'exportExcel');
        });
        //INFORME RESULTADOS
        Route::controller(ResultadosController::class)->group(function () {
            Route::get('resultados', 'generate');
            Route::get('resultados-show', 'show');
            Route::post('resultados-excel', 'exportExcel');
        });
        //INFORME VENTAS ACUMULADAS
        Route::controller(VentasAcumuladasController::class)->group(function () {
            Route::get('ventas-acumuladas', 'generate');
            Route::get('ventas-acumuladas-show', 'show');
            Route::post('ventas-acumuladas-excel', 'exportExcel');
        });
        //USUARIOS
        Route::controller(UsuariosController::class)->group(function () {
            Route::get('usuarios', 'generate');
            Route::post('usuarios', 'create');
            Route::put('usuarios', 'update');
            Route::get('usuarios/combo', 'comboUsuario');
        });
        //REUNIONES
        Route::controller(ReunionesController::class)->group(function () {
            Route::post('reuniones', 'create');
            Route::put('reuniones', 'update');
            Route::get('reuniones', 'find');
            Route::delete('reuniones', 'delete');
            Route::get('reuniones-table', 'table');
            Route::get('reuniones-participantes', 'participantes');
            Route::post('reuniones-participantes', 'createParticipantes');
            Route::delete('reuniones-participantes', 'deleteParticipantes');
        });

        //IMPUESTOS
        Route::controller(ImpuestoController::class)->group(function () {
            Route::get('impuesto/combo-impuesto', 'comboImpuesto');
        });
        //PLAN DE CUENTAS
        Route::controller(PlanCuentaController::class)->group(function () {
            Route::get('plan-cuenta', 'generate');
            Route::post('plan-cuenta', 'create');
            Route::put('plan-cuenta', 'update');
            Route::delete('plan-cuenta', 'delete');
            Route::get('plan-cuenta/combo-cuenta', 'comboCuenta');
            Route::get('plan-cuenta/combo-cuenta-cartera', 'comboCuentaCartera');
        });
        //CENTRO COSTOS
        Route::controller(CentroCostoController::class)->group(function () {
            Route::get('cecos', 'generate');
            Route::post('cecos', 'create');
            Route::put('cecos', 'update');
            Route::delete('cecos', 'delete');
            Route::get('centro-costos/combo-centro-costo', 'comboCentroCostos');
        });
        //COMPROBANTES
        Route::controller(ComprobantesController::class)->group(function () {
            Route::get('comprobantes', 'generate');
            Route::post('comprobantes', 'create');
            Route::put('comprobantes', 'update');
            Route::delete('comprobantes', 'delete');
            Route::get('comprobantes/combo-comprobante', 'comboComprobante');
        });
        //NITS
        Route::controller(NitController::class)->group(function () {
            Route::get('nit', 'generate');
            Route::post('nit', 'create');
            Route::put('nit', 'update');
            Route::delete('nit', 'delete');
            Route::get('nit/combo-nit', 'comboNit');
            Route::get('nit/combo-tipo-documento', 'comboTipoDocumento');
            Route::get('nit/informacion', 'getNitInfo');
            Route::get('nit/empleado-activo', 'comboEmpleado');
        });
        //FAMILIAS
        Route::controller(FamiliasController::class)->group(function () {
            Route::get('familia', 'generate');
            Route::post('familia', 'create');
            Route::put('familia', 'update');
            Route::delete('familia', 'delete');
            Route::get('familia/combo-familia', 'comboFamilia');
        });
        //RESOLUCIONES
        Route::controller(ResolucionesController::class)->group(function () {
            Route::get('resoluciones', 'generate');
            Route::post('resoluciones', 'create');
            Route::put('resoluciones', 'update');
            Route::delete('resoluciones', 'delete');
            Route::get('resoluciones/combo-resoluciones', 'comboResolucion');
        });
        //FORMAS PAGO
        Route::controller(FormasPagoController::class)->group(function () {
            Route::get('forma-pago', 'generate');
            Route::post('forma-pago', 'create');
            Route::put('forma-pago', 'update');
            Route::delete('forma-pago', 'delete');
            Route::get('forma-pago/combo-forma-pago', 'comboFormasPago');
            Route::get('forma-pago/combo-tipo-formas-pago', 'comboTipoFormasPago');
        });
        //BODEGAS
        Route::controller(BodegasController::class)->group(function () {
            Route::get('bodega', 'generate');
            Route::post('bodega', 'create');
            Route::put('bodega', 'update');
            Route::delete('bodega', 'delete');
            Route::get('bodega/combo-bodega', 'comboBodega');
            Route::get('bodega-consecutivo', 'consecutivo');
            Route::get('existencias-producto', 'existenciasProducto');
            Route::get('bodega-parqueadero-consecutivo', 'consecutivoParqueadero');
        });
        //VARIANTES
        Route::controller(VariantesController::class)->group(function () {
            Route::post('variante', 'create');
            Route::post('variante/opcion', 'createOpcion');
            Route::get('variante/combo-variante', 'comboVariante');
        });
        //PRODUCTOS
        Route::controller(ProductosController::class)->group(function () {
            Route::get('producto', 'generate');
            Route::post('producto', 'create');
            Route::put('producto', 'update');
            Route::delete('producto', 'delete');
            Route::get('producto/combo-producto', 'comboProducto');
            Route::get('producto/combo-parqueadero', 'comboParqueadero');
            Route::get('productos', 'getAll');
        });
        //CARGUE DESCARGUE
        Route::controller(CargueDescargueController::class)->group(function () {
            Route::get('cargue-descargue', 'generate');
            Route::post('cargue-descargue', 'create');
            Route::put('cargue-descargue', 'update');
            Route::delete('cargue-descargue', 'delete');
            Route::get('cargue-descargue/combo', 'comboCargueDescargue');
        });
        //VENDEDORES
        Route::controller(VendedoresController::class)->group(function () {
            Route::get('vendedores', 'generate');
            Route::post('vendedores', 'create');
            Route::put('vendedores', 'update');
            Route::delete('vendedores', 'delete');
            Route::get('vendedores/combo', 'comboVendedores');
        });
        //EXOGENA
        Route::controller(ExogenaController::class)->group(function () {
            Route::get('exogena', 'generate');
            Route::get('exogena/formato', 'comboFormato');
            Route::get('exogena/columna', 'comboFormatoColumna');
            Route::get('exogena/concepto', 'comboFormatoConcepto');
        });
        //CONCEPTO GASTO
        Route::controller(ConceptoGastosController::class)->group(function () {
            Route::get('concepto-gasto', 'generate');
            Route::post('concepto-gasto', 'create');
            Route::put('concepto-gasto', 'update');
            Route::delete('concepto-gasto', 'delete');
            Route::get('concepto-gasto/combo', 'comboConceptoGasto');
        });
        //PRESUPUESTO
        Route::controller(PresupuestoController::class)->group(function () {
            Route::get('presupuesto', 'generate');
            Route::post('presupuesto', 'create');
            Route::put('presupuesto', 'update');
            Route::put('presupuesto-valor', 'updateValor');
            Route::put('presupuesto-grupo', 'grupo');
        });
        //ADMINISTRADORAS -> NOMINA
        Route::controller(AdministradorasController::class)->group(function () {
            Route::get('administradoras', 'generate');
            Route::post('administradoras', 'create');
            Route::put('administradoras', 'update');
            Route::delete('administradoras', 'delete');
            Route::get('administradoras-combo', 'combo');
            Route::post('administradoras-sincronizar', 'sincronizar');
        });
        //PERIODOS -> NOMINA
        Route::controller(PeriodosController::class)->group(function () {
            Route::get('periodos', 'generate');
            Route::post('periodos', 'create');
            Route::put('periodos', 'update');
            Route::delete('periodos', 'delete');
            Route::get('periodos-combo', 'combo');
        });
        //CONCEPTOS -> NOMINA
        Route::controller(ConceptosNominaController::class)->group(function () {
            Route::get('conceptos-nomina', 'generate');
            Route::post('conceptos-nomina', 'create');
            Route::put('conceptos-nomina', 'update');
            Route::get('conceptos-combo', 'combo');
            Route::delete('conceptos-nomina', 'delete');
        });
        //CONTRATOS -> NOMINA
        Route::controller(ContratosController::class)->group(function () {
            Route::get('contratos', 'generate');
            Route::post('contratos', 'create');
            Route::put('contratos', 'update');
            Route::delete('contratos', 'delete');
        });
        //CONFIGURACION -> NOMINA
        Route::controller(ConfiguracionProvisiones::class)->group(function () {
            Route::get('configuracion-provisiones', 'generate');
            Route::put('configuracion-provisiones', 'update');
        });
        
        //CAPTURA GENERAL
        Route::controller(DocumentoGeneralController::class)->group(function () {
            Route::get('consecutivo', 'getConsecutivo');
            Route::get('documentos', 'generate');
            Route::post('documentos-anular', 'anular');
            Route::post('documentos', 'create');
            Route::post('bulk-documentos', 'bulkDocumentos');
            Route::post('generar-documentos', 'generarDocumentos');
            Route::post('bulk-documentos-delete', 'bulkDocumentosDelete');
            Route::get('documento-vacio', 'vacio');
            Route::get('year-combo', 'comboYear');
        });
        //CAPTURA COMPRA
        Route::controller(CompraController::class)->group(function () {
            Route::get('compras', 'generate');
            Route::post('compras', 'create');
        });
        //CAPTURA VENTA
        Route::controller(VentaController::class)->group(function () {
            Route::get('ventas', 'generate');
            Route::get('facturas', 'read');
            Route::post('ventas', 'create');
            Route::post('ventas-fe', 'facturacionElectronica');
            Route::post('ventas-notificar', 'sendNotification');
        });
        //PEDIDO VENTA
        Route::controller(PedidoController::class)->group(function () {
            Route::post('pedido-ventas', 'venta');
            Route::post('pedido', 'create');
            Route::get('pedido', 'find');
            Route::delete('pedido', 'delete');
        });
        //PARQUEADERO
        Route::controller(ParqueaderoController::class)->group(function () {
            Route::get('parqueadero', 'generate');
            Route::post('parqueadero', 'create');
            Route::put('parqueadero', 'update');
            Route::post('parqueadero-ventas', 'venta');
        });
        //RESERVAS
        Route::controller(ReservaController::class)->group(function () {
            Route::post('reserva', 'create');
            Route::put('reserva', 'update');
            Route::get('reserva', 'table');
            Route::delete('reserva', 'delete');
        });
        //CAPTURA GASTO
        Route::controller(GastosController::class)->group(function () {
            Route::post('gastos', 'create');
            Route::get('gastos', 'find');
        });
        //CAPTURA RECIBO
        Route::controller(RecibosController::class)->group(function () {
            Route::get('recibos', 'generate');
            Route::post('recibos', 'create');
            Route::get('recibos-comprobante', 'generateComprobante');
            Route::post('recibos-comprobante', 'createComprobante');
            Route::put('recibos-comprobante', 'updateComprobante');
            Route::delete('recibos-comprobante', 'deleteComprobante');
        });
        //CAPTURA DE PAGOS
        Route::controller(PagosController::class)->group(function () {
            Route::get('pagos', 'generate');
            Route::post('pagos', 'create');
        });
        
        //CAPTURA MOVIMIENTO INVENTARIO
        Route::controller(MovimientoInventarioController::class)->group(function () {
            Route::get('movimiento-inventario', 'generate');
            Route::post('movimiento-inventario', 'create');
        });
        //CAPTURA NOTA CREDITO
        Route::controller(NotaCreditoController::class)->group(function () {
            Route::get('nota-credito/factura-detalle', 'detalleFactura');
            Route::post('nota-credito-fe', 'facturacionElectronica');
            Route::post('nota-credito', 'create');
        });
        //UBICACIONES
        Route::controller(UbicacionesController::class)->group(function () {
            Route::get('ubicaciones', 'generate');
            Route::get('ubicaciones-combo', 'combo');
            Route::post('ubicaciones', 'create');
            Route::put('ubicaciones', 'update');
            Route::delete('ubicaciones', 'delete');
            Route::get('ubicaciones-combo-general', 'comboUbicacion');
        });

        //CAPTURA CESANTIAS INTERESES
        Route::controller(CesantiasInteresController::class)->group(function () {
            Route::post('cesantias-intereses', 'create');
            Route::get('cesantias-intereses', 'generate');
            Route::get('cesantias-intereses-detalle', 'detalles');
        });
        //CAPTURA NOVEDADES GENERALES
        Route::controller(NovedadesGeneralesController::class)->group(function () {
            Route::get('novedades-generales', 'generate');
            Route::put('novedades-generales', 'update');
            Route::post('novedades-generales', 'create');
            Route::delete('novedades-generales', 'delete');
        });
        //CAUSAR NOMINA GENERALES
        Route::controller(CausarNominaController::class)->group(function () {
            Route::get('causar-periodos-pago', 'generate');
            Route::get('causar-meses-combo', 'comboMeses');
            Route::get('detalle-periodo', 'detallePeriodo');
            Route::post('calcular-nomina', 'calcularNomina');
            Route::get('periodos-pagos-combo', 'comboPeriodoPago');
        });
        //CAUSAR PRESTACIONES SOCIALES
        Route::controller(CausarProvicionadaController::class)->group(function () {
            Route::get('prestaciones-sociales', 'generatePrestaciones');
            Route::post('prestaciones-sociales', 'causarPrestaciones');
            Route::get('seguridad-social', 'generateSeguridad');
            Route::post('seguridad-social', 'causarSeguridad');
            Route::get('parafiscales', 'generateParafiscales');
            Route::post('parafiscales', 'causarParafiscales');
        });
        //CAUSAR LIQUIDACIÓN DEFINITIVA
        Route::controller(LiquidacionDefinitivaController::class)->group(function () {
            Route::get('liquidacion-definitiva', 'generate');
            Route::post('liquidacion-definitiva', 'create');
        });
        //CAUSAR VACACIONES
        Route::controller(VacacionesController::class)->group(function () {
            Route::get('vacaciones', 'generate');
            Route::get('vacaciones-calcular', 'calcular');
            Route::post('vacaciones', 'create');
            Route::delete('vacaciones', 'delete');
        });
        
    });
    
});