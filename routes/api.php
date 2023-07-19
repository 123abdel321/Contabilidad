<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstaladorController;
use App\Http\Controllers\Tablas\NitController;
use App\Http\Controllers\Tablas\PlanCuentaController;
use App\Http\Controllers\Tablas\CentroCostoController;
use App\Http\Controllers\Tablas\ComprobantesController;
use App\Http\Controllers\Capturas\DocumentoGeneralController;

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

Route::group(['middleware' => ['auth:sanctum']], function() {

    //EMPRESA
    Route::get("empresa","App\Http\Controllers\ApiController@getEmpresas");
    Route::post("empresa","App\Http\Controllers\InstaladorController@createEmpresa");
    Route::post("seleccionar-empresa","App\Http\Controllers\ApiController@setEmpresa");
    
    //EMPRESA SELECCIONADA
    Route::group(['middleware' => ['clientconnection']], function() {
        //INFORMES
        Route::get('cartera', 'App\Http\Controllers\Informes\CarteraController@generate');
        Route::get('balances', 'App\Http\Controllers\Informes\BalanceController@generate');
        Route::get('extracto', 'App\Http\Controllers\Informes\ExtractoController@extracto');
        Route::get('auxiliares', 'App\Http\Controllers\Informes\AuxiliarController@generate');
        //PLAN DE CUENTAS
        Route::controller(PlanCuentaController::class)->group(function () {
            Route::get('plan-cuenta', 'generate');
            Route::post('plan-cuenta', 'create');
            Route::put('plan-cuenta', 'update');
            Route::delete('plan-cuenta', 'delete');
            Route::get('plan-cuenta/combo-cuenta', 'comboCuenta');
        });
        //CENTRO COSTOS
        Route::controller(CentroCostoController::class)->group(function () {
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
        });
        //CAPTURA GENERAL
        Route::controller(DocumentoGeneralController::class)->group(function () {
            Route::get('consecutivo', 'getConsecutivo');
            Route::get('documentos', 'generate');
            Route::post('documentos', 'create');
            Route::get('documento-vacio', 'vacio');
        });
    });
    
});