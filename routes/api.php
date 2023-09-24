<?php

use Illuminate\Http\Request;
use App\Events\PrivateMessage;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstaladorController;
//TABLAS
use App\Http\Controllers\Tablas\NitController;
use App\Http\Controllers\Tablas\BodegasController;
use App\Http\Controllers\Tablas\FamiliasController;
use App\Http\Controllers\Tablas\VariantesController;
use App\Http\Controllers\Tablas\ProductosController;
use App\Http\Controllers\Tablas\ImpuestosController;
use App\Http\Controllers\Tablas\PlanCuentaController;
use App\Http\Controllers\Tablas\CentroCostoController;
use App\Http\Controllers\Tablas\ComprobantesController;
//CAPTURAS
use App\Http\Controllers\Capturas\CompraController;
use App\Http\Controllers\Capturas\DocumentoGeneralController;
//SISTEMA
use App\Http\Controllers\Sistema\UbicacionController;

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
    
    //EMPRESA SELECCIONADA
    Route::group(['middleware' => ['clientconnection']], function() {
        //INFORMES
        Route::get('extracto', 'App\Http\Controllers\Informes\ExtractoController@extracto');
        Route::get('existe-factura', 'App\Http\Controllers\Informes\ExtractoController@existeFactura');
        //DOCUMENTOS
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

        //IMPUESTOS
        Route::controller(ImpuestosController::class)->group(function () {
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
        });
        //FAMILIAS
        Route::controller(FamiliasController::class)->group(function () {
            Route::get('familia', 'generate');
            Route::post('familia', 'create');
            Route::put('familia', 'update');
            Route::delete('familia', 'delete');
            Route::get('familia/combo-familia', 'comboFamilia');
        });
        //BODEGAS
        Route::controller(BodegasController::class)->group(function () {
            Route::get('bodega', 'generate');
            Route::post('bodega', 'create');
            Route::put('bodega', 'update');
            Route::delete('bodega', 'delete');
            Route::get('bodega/combo-bodega', 'comboBodega');
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
        });
        
        //CAPTURA GENERAL
        Route::controller(DocumentoGeneralController::class)->group(function () {
            Route::get('consecutivo', 'getConsecutivo');
            Route::get('documentos', 'generate');
            Route::put('documentos', 'anular');
            Route::post('documentos', 'create');
            Route::get('documento-vacio', 'vacio');
        });
        //CAPTURA COMPRA
        Route::controller(CompraController::class)->group(function () {
            Route::get('compras', 'generate');
            Route::post('compras', 'create');
        });
    });
    
});