<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| CONTROLADORES - SISTEMA / AUTH
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ChangePassword;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstaladorController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\UserProfileController;

/*
|--------------------------------------------------------------------------
| CONTROLADORES - SISTEMA
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Sistema\EcoController;
use App\Http\Controllers\Sistema\ArchivosCacheController;

/*
|--------------------------------------------------------------------------
| CONTROLADORES - CONFIGURACIÓN
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Configuracion\EntornoController;
use App\Http\Controllers\Configuracion\ReunionesController;
use App\Http\Controllers\Configuracion\SuscripcionController;
use App\Http\Controllers\Configuracion\UsuariosController;

/*
|--------------------------------------------------------------------------
| CONTROLADORES - INFORMES
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Informes\AuxiliarController;
use App\Http\Controllers\Informes\BalanceController;
use App\Http\Controllers\Informes\CarteraController;
use App\Http\Controllers\Informes\DocumentoController;
use App\Http\Controllers\Informes\DocumentosGeneralesController;
use App\Http\Controllers\Informes\EstadoActualController;
use App\Http\Controllers\Informes\EstadoComprobanteController;
use App\Http\Controllers\Informes\ExtractoController;
use App\Http\Controllers\Informes\ImpuestosController;
use App\Http\Controllers\Informes\MovimientoProductoController;
use App\Http\Controllers\Informes\ResumenCarteraController;
use App\Http\Controllers\Informes\ResumenComprobantesController;
use App\Http\Controllers\Informes\ResultadosController;
use App\Http\Controllers\Informes\VentasAcumuladasController;
use App\Http\Controllers\Informes\VentasGeneralesController;

/*
|--------------------------------------------------------------------------
| CONTROLADORES - TABLAS (Facturación / Contabilidad)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Tablas\BodegasController;
use App\Http\Controllers\Tablas\CargueDescargueController;
use App\Http\Controllers\Tablas\CentroCostoController;
use App\Http\Controllers\Tablas\ComprobantesController;
use App\Http\Controllers\Tablas\ConceptoGastosController;
use App\Http\Controllers\Tablas\ExogenaController;
use App\Http\Controllers\Tablas\FamiliasController;
use App\Http\Controllers\Tablas\FormasPagoController;
use App\Http\Controllers\Tablas\ImpuestoController;
use App\Http\Controllers\Tablas\NitController;
use App\Http\Controllers\Tablas\PlanCuentaController;
use App\Http\Controllers\Tablas\PresupuestoController;
use App\Http\Controllers\Tablas\ProductosController;
use App\Http\Controllers\Tablas\ResolucionesController;
use App\Http\Controllers\Tablas\UbicacionesController;
use App\Http\Controllers\Tablas\VendedoresController;

/*
|--------------------------------------------------------------------------
| CONTROLADORES - TABLAS (Nómina)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Tablas\Nomina\AdministradorasController;
use App\Http\Controllers\Tablas\Nomina\ConceptosNominaController;
use App\Http\Controllers\Tablas\Nomina\ConfiguracionProvisiones;
use App\Http\Controllers\Tablas\Nomina\ContratosController;
use App\Http\Controllers\Tablas\Nomina\PeriodosController;

/*
|--------------------------------------------------------------------------
| CONTROLADORES - CAPTURAS (Facturación / Contabilidad)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Capturas\CambiosDatosController;
use App\Http\Controllers\Capturas\CompraController;
use App\Http\Controllers\Capturas\DocumentoEliminarController;
use App\Http\Controllers\Capturas\DocumentoGeneralController;
use App\Http\Controllers\Capturas\GastosController;
use App\Http\Controllers\Capturas\MovimientoInventarioController;
use App\Http\Controllers\Capturas\NotaCreditoController;
use App\Http\Controllers\Capturas\PagosController;
use App\Http\Controllers\Capturas\ParqueaderoController;
use App\Http\Controllers\Capturas\PedidoController;
use App\Http\Controllers\Capturas\RecibosController;
use App\Http\Controllers\Capturas\ReservaController;
use App\Http\Controllers\Capturas\VentaController;

/*
|--------------------------------------------------------------------------
| CONTROLADORES - CAPTURAS (Nómina)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Capturas\Nomina\CausarNominaController;
use App\Http\Controllers\Capturas\Nomina\CesantiasInteresController;
use App\Http\Controllers\Capturas\Nomina\LiquidacionDefinitivaController;
use App\Http\Controllers\Capturas\Nomina\NominaElectronicaController;
use App\Http\Controllers\Capturas\Nomina\NovedadesGeneralesController;
use App\Http\Controllers\Capturas\Nomina\PagosNominaController;
use App\Http\Controllers\Capturas\Nomina\PrimasController;
use App\Http\Controllers\Capturas\Nomina\VacacionesController;

/*
|--------------------------------------------------------------------------
| CONTROLADORES - IMPORTADORES
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Importador\DocumentosImportadorController;
use App\Http\Controllers\Importador\NitsImportadorController;
use App\Http\Controllers\Importador\ProductoImportadorController;

/*
|--------------------------------------------------------------------------
| MODELOS
|--------------------------------------------------------------------------
*/
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacProductosBodegas;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/* =========================================================================
 |  RUTAS PÚBLICAS
 | ========================================================================= */

Route::get('/', fn () => view('pages.landing-page'));

Route::get('/test-auxiliar-pdf', function () {
    try {
        $data = [
            'empresa'    => \App\Models\Empresas\Empresa::first(),
            'auxiliares' => \App\Models\Informes\InfAuxiliarDetalle::limit(10)->get(),
            'fecha_pdf'  => now()->format('Y-m-d H:i:s'),
            'usuario'    => 'Usuario Test',
        ];

        $html = view('pdf.informes.auxiliar.auxiliar', $data)->render();

        $pdf = PDF::loadHTML($html)
            ->setOption('enable-local-file-access', true)
            ->setOption('no-stop-slow-scripts', true)
            ->setOption('load-error-handling', 'ignore')
            ->setOption('load-media-error-handling', 'ignore')
            ->setOption('orientation', 'landscape')
            ->setOption('page-size', 'A4');

        return $pdf->download('test-auxiliar.pdf');
    } catch (\Exception $e) {
        dd("Error: " . $e->getMessage());
    }
});

Auth::routes();

/* =========================================================================
 |  AUTENTICACIÓN
 | ========================================================================= */

Route::middleware('guest')->group(function () {
    Route::get('/register',         [SuscripcionController::class, 'registerIndex'])->name('register');
    Route::get('/login',            [LoginController::class, 'show'])->name('login');
    Route::post('/login',           [LoginController::class, 'login'])->name('login.perform');
    Route::get('/reset-password',   [ResetPassword::class, 'show'])->name('reset-password');
    Route::post('/reset-password',  [ResetPassword::class, 'send'])->name('reset.perform');
    Route::get('/change-password',  [ChangePassword::class, 'show'])->name('change-password');
    Route::post('/change-password', [ChangePassword::class, 'update'])->name('change.perform');
});

Route::get('/login-direct',  [LoginController::class, 'loginDirectoGet'])->name('login-direct-get');
Route::post('/login-direct', [LoginController::class, 'loginDirectoPost'])->name('login-direct-post');

/* =========================================================================
 |  RUTAS PÚBLICAS (PDF / PAGOS)
 | ========================================================================= */

Route::get('/recibos-print', [RecibosController::class, 'showPdfPublic'])->name('recibos-pdf');
Route::get('/pagar',         [SuscripcionController::class, 'pagoPublicoIndex'])->name('pago-publico');

// PDF POS
Route::prefix('pos')->controller(PosController::class)->group(function () {
    Route::get('pedido-print/{token}/{id}', 'showPdfPedido');
    Route::get('venta-print/{token}/{id}',  'showPdfVenta');
});

// PDF GENERAL QR
Route::controller(DocumentoController::class)->group(function () {
    Route::get('documentos-generales-pdf', 'showGeneralPdfQr');
});

Route::get('/ventas-acuse-recibo', [VentaController::class, 'acuseReciboVista'])->name('venta-acuse-recibo');
Route::post('/ventas-acuse-recibo/procesar', [VentaController::class, 'acuseReciboProcesar'])->name('venta-acuse-recibo.procesar');


/* =========================================================================
 |  RUTAS AUTENTICADAS
 | ========================================================================= */

Route::middleware('auth:sanctum')->group(function () {

    // Selección de empresa
    Route::get('/seleccionar-empresa', [ApiController::class, 'index'])->name('seleccionar-empresa');

    /* =====================================================================
     |  MÓDULO CON CONEXIÓN CLIENTE
     | ===================================================================== */
    Route::middleware('clientconnectionweb')->group(function () {

        /* -------------------------------------------------------------
         |  Utilidades / Sistema
         | ------------------------------------------------------------- */
        Route::get('/productos-primera-bodega', function () {
            DB::connection('sam')->transaction(function () {
                $bodega = FacBodegas::query()->orderBy('id')->first();
                if (!$bodega) {
                    throw new \Exception('No hay bodegas registradas');
                }

                $productos = FacProductos::query()->whereDoesntHave('inventarios')->get();

                foreach ($productos as $producto) {
                    FacProductosBodegas::create([
                        'id_producto' => $producto->id,
                        'id_bodega'   => $bodega->id,
                        'inventario'  => 0,
                        'created_by'  => auth()->id(),
                        'updated_by'  => auth()->id(),
                    ]);
                }
            });

            return 'Productos asignados correctamente';
        });

        Route::get('/notificaciones', [EcoController::class, 'index']);

        Route::controller(ArchivosCacheController::class)->prefix('archivos-cache')->group(function () {
            Route::post('/', 'store');
            Route::delete('/', 'delete');
        });

        /* -------------------------------------------------------------
         |  Dashboard / Home
         | ------------------------------------------------------------- */
        Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
        Route::get('/home',      [HomeController::class, 'index'])->name('home');

        /* -------------------------------------------------------------
         |  INFORMES
         | ------------------------------------------------------------- */
        // Auxiliar
        Route::controller(AuxiliarController::class)->group(function () {
            Route::get('/auxiliar',          'index')->name('auxiliar');
            Route::get('/auxiliar-pdf/{id}', 'showPdf')->name('auxiliar-pdf');
            Route::get('/auxiliar-excel',    'exportExcel')->name('auxiliar-excel');
        });

        // Balance
        Route::controller(BalanceController::class)->group(function () {
            Route::get('/balance',          'index')->name('balance');
            Route::get('/balance-pdf/{id}', 'showPdf')->name('balance-pdf');
            Route::get('/balance-excel',    'exportExcel')->name('balance-excel');
        });

        // Cartera
        Route::get('/cartera', [CarteraController::class, 'index'])->name('cartera');

        // Impuestos
        Route::controller(ImpuestosController::class)->group(function () {
            Route::get('/impuestos',                'index')->name('impuestos');
            Route::get('/impuestos-reteica-pdf',    'showPdfReteica')->name('impuestos-reteica-pdf');
            Route::get('/impuestos-retencion-pdf',  'showPdfRetencion')->name('impuestos-retencion-pdf');
        });

        // Extractos
        Route::get('/extracto', [ExtractoController::class, 'index'])->name('extractos');

        // Resumen comprobantes
        Route::get('/resumencomprobante', [ResumenComprobantesController::class, 'index'])->name('resumencomprobante');

        // Resultados
        Route::get('/resultados', [ResultadosController::class, 'index']);

        // Documento general (captura)
        Route::get('/documentogeneral', [DocumentoGeneralController::class, 'index'])->name('documento-general');

        // Eliminar documentos
        Route::get('/eliminardocumentos', [DocumentoEliminarController::class, 'index'])->name('eliminar-documentos');

        // Movimiento inventario
        Route::get('/movimientoinventario', [MovimientoInventarioController::class, 'index']);

        // Documentos
        Route::controller(DocumentoController::class)->group(function () {
            Route::get('/documentos', 'index')->name('documentos');
            Route::get('/documentos-print/{id}', 'showPdf')->name('documento-pdf');
            Route::get(
                '/documentos-generales-print/{id_comprobante}/{consecutivo}/{fecha_manual}',
                'showGeneralPdf'
            )->name('documento-generales-pdf');
        });

        // Documentos generales
        Route::get('/documentosgenerales', [DocumentosGeneralesController::class, 'index']);

        // Ventas generales
        Route::get('/ventasgenerales', [VentasGeneralesController::class, 'index']);

        // Ventas acumuladas
        Route::get('/ventasacumuladas', [VentasAcumuladasController::class, 'index']);

        // Movimiento producto
        Route::get('/movimientoproducto', [MovimientoProductoController::class, 'index']);

        // Estado actual
        Route::get('/estadoactual', [EstadoActualController::class, 'index']);

        // Resumen cartera
        Route::get('/resumencartera', [ResumenCarteraController::class, 'index']);

        // Estado comprobante
        Route::get('/estadocomprobante', [EstadoComprobanteController::class, 'index']);

        /* -------------------------------------------------------------
         |  CAPTURAS - FACTURACIÓN / CONTABILIDAD
         | ------------------------------------------------------------- */
        // Compras
        Route::controller(CompraController::class)->group(function () {
            Route::get('/compra',            'index')->name('compra');
            Route::get('/compras',           'indexInforme')->name('compras');
            Route::get('/compras-print/{id}','showPdf')->name('compra-pdf');
        });

        // Ventas
        Route::controller(VentaController::class)->group(function () {
            Route::get('/venta',                'index')->name('venta');
            Route::get('/ventas',               'indexInforme')->name('ventas');
            Route::get('/ventas-print/{id}',    'showPdf')->name('venta-pdf');
            Route::get('/ventas-print-informez','showPdfZ');
        });

        // Pedidos
        Route::controller(PedidoController::class)->group(function () {
            Route::get('/pedido',            'index')->name('pedido');
            Route::get('/pedido-print/{id}', 'showPdf')->name('pedido-pdf');
        });

        // Parqueaderos
        Route::controller(ParqueaderoController::class)->group(function () {
            Route::get('/parqueadero',            'index')->name('parqueadero');
            Route::get('/parqueadero-print/{id}', 'showPdf')->name('parqueadero-pdf');
        });

        // Recibos
        Route::controller(RecibosController::class)->group(function () {
            Route::get('/recibo',            'index')->name('recibo');
            Route::get('/recibo-print/{id}', 'showPdf')->name('recibo-pdf');
        });

        // Pagos
        Route::controller(PagosController::class)->group(function () {
            Route::get('/pago',            'index')->name('pago');
            Route::get('/pago-print/{id}', 'showPdf')->name('pago-pdf');
        });

        // Gastos
        Route::controller(GastosController::class)->group(function () {
            Route::get('/gasto',            'index');
            Route::get('/gasto-print/{id}', 'showPdf')->name('gasto-pdf');
        });

        // Nota crédito
        Route::get('/notacredito', [NotaCreditoController::class, 'index']);

        // Reserva
        Route::controller(ReservaController::class)->group(function () {
            Route::get('/reserva',       'index');
            Route::get('/reserva-evento','read');
        });

        /* -------------------------------------------------------------
         |  CAPTURAS - NÓMINA
         | ------------------------------------------------------------- */
        Route::get('/cesantiasintereses',  [CesantiasInteresController::class, 'index']);
        Route::get('/novedadesgenerales',  [NovedadesGeneralesController::class, 'index']);
        Route::get('/causar',              [CausarNominaController::class, 'index']);
        Route::get('/liquidaciondefinitiva',[LiquidacionDefinitivaController::class, 'index']);
        Route::get('/pagos',               [PagosNominaController::class, 'index']);
        Route::get('/nominaelectronica',   [NominaElectronicaController::class, 'index']);
        Route::get('/vacaciones',          [VacacionesController::class, 'index']);
        Route::get('/primas',              [PrimasController::class, 'index']);

        /* -------------------------------------------------------------
         |  TABLAS - FACTURACIÓN / CONTABILIDAD
         | ------------------------------------------------------------- */
        Route::get('/nit',           [NitController::class, 'index'])->name('nit');
        Route::get('/plancuenta',    [PlanCuentaController::class, 'index'])->name('plan-cuenta');
        Route::get('/exogena',       [ExogenaController::class, 'index'])->name('exogena');
        Route::get('/comprobante',   [ComprobantesController::class, 'index'])->name('comprobante');
        Route::get('/cecos',         [CentroCostoController::class, 'index'])->name('cecos');
        Route::get('/impuesto',      [ImpuestoController::class, 'index'])->name('impuesto');
        Route::get('/ubicaciones',   [UbicacionesController::class, 'index'])->name('ubicaciones');
        Route::get('/vendedores',    [VendedoresController::class, 'index'])->name('vendedores');
        Route::get('/familias',      [FamiliasController::class, 'index'])->name('familias');
        Route::get('/resolucion',    [ResolucionesController::class, 'index'])->name('resolucion');
        Route::get('/formapago',     [FormasPagoController::class, 'index'])->name('forma-pago');
        Route::get('/bodegas',       [BodegasController::class, 'index'])->name('bodegas');
        Route::get('/productos',     [ProductosController::class, 'index'])->name('productos');
        Route::get('/carguedescargue',[CargueDescargueController::class, 'index']);
        Route::get('/conceptogastos', [ConceptoGastosController::class, 'index']);
        Route::get('/presupuesto',   [PresupuestoController::class, 'index']);

        /* -------------------------------------------------------------
         |  TABLAS - NÓMINA
         | ------------------------------------------------------------- */
        Route::get('/administradoras',          [AdministradorasController::class, 'index'])->name('administradoras');
        Route::get('/configuracionprovisiones', [ConfiguracionProvisiones::class, 'index'])->name('configuracionprovisiones');
        Route::get('/periodos',                 [PeriodosController::class, 'index'])->name('periodos');
        Route::get('/conceptosnomina',          [ConceptosNominaController::class, 'index'])->name('conceptosnomina');
        Route::get('/contratos',                [ContratosController::class, 'index'])->name('contratos');

        // Cambios en los datos
        Route::get('/cambiodatos', [CambiosDatosController::class, 'index'])->name('cambiodatos');

        /* -------------------------------------------------------------
         |  CONFIGURACIÓN
         | ------------------------------------------------------------- */
        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios');

        // Empresa
        Route::controller(EmpresaController::class)->group(function () {
            Route::get('/empresa', 'index')->name('empresa');
            Route::post('/loadrut', 'rut');
        });

        // Instalador / actualización
        Route::post('/instalacionempresa', [InstaladorController::class, 'instalar']);
        Route::post('/actualizarempresa',  [InstaladorController::class, 'actualizar']);

        // Entorno
        Route::get('/entorno', [EntornoController::class, 'index'])->name('entorno');

        // Reuniones
        Route::controller(ReunionesController::class)->group(function () {
            Route::get('/reuniones',       'index')->name('reuniones');
            Route::get('/reuniones-event', 'read')->name('reuniones.event');
        });

        // Suscripción
        Route::get('/suscripcion', [SuscripcionController::class, 'index'])->name('suscripcion');

        /* -------------------------------------------------------------
         |  IMPORTADORES
         | ------------------------------------------------------------- */
        // Precios de productos
        Route::controller(ProductoImportadorController::class)->group(function () {
            Route::get('/productoprecios',           'indexPrecios');
            Route::get('/productoprecios-exportar',  'exportarTotales');
            Route::post('/productoprecios-importar', 'importarPrecios');

            // Import productos
            Route::get('/importproductos',      'index');
            Route::get('/productos-exportar',   'exportar');
            Route::post('/productos-importar',  'importar');
        });

        // NITs
        Route::controller(NitsImportadorController::class)->group(function () {
            Route::get('/importnits',           'index');
            Route::get('/importnits-exportar',  'exportar');
            Route::post('/importnits-importar', 'importar');
        });

        // Documentos
        Route::controller(DocumentosImportadorController::class)->group(function () {
            Route::get('/importdocumentos',           'index');
            Route::get('/importdocumentos-exportar',  'exportar');
            Route::post('/importdocumentos-importar', 'importar');
        });
    });

    /* =====================================================================
     |  ARGON / PERFIL / PÁGINAS ESTÁTICAS
     | ===================================================================== */
    Route::get('/virtual-reality', [PageController::class, 'vr'])->name('virtual-reality');
    Route::get('/rtl',             [PageController::class, 'rtl'])->name('rtl');

    Route::controller(UserProfileController::class)->group(function () {
        Route::get('/profile',  'show')->name('profile');
        Route::post('/profile', 'update')->name('profile.update');
    });

    Route::get('/profile-static',  [PageController::class, 'profile'])->name('profile-static');
    Route::get('/sign-in-static',  [PageController::class, 'signin'])->name('sign-in-static');
    Route::get('/sign-up-static',  [PageController::class, 'signup'])->name('sign-up-static');

    Route::post('logout-api', [LoginController::class, 'logoutApi'])->name('logout-api');

    // Catch-all: DEBE ir al final del grupo
    Route::get('/{page}', [PageController::class, 'index'])->name('page');
});