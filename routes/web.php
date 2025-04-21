<?php
//SISTEMA
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;
use App\Http\Controllers\ApiController;
//INFORMES
use App\Http\Controllers\Informes\CarteraController;
use App\Http\Controllers\Informes\BalanceController;
use App\Http\Controllers\Informes\AuxiliarController;
use App\Http\Controllers\Informes\ImpuestosController;
use App\Http\Controllers\Informes\DocumentoController;
use App\Http\Controllers\Informes\ResultadosController;
use App\Http\Controllers\Informes\EstadoActualController;
use App\Http\Controllers\Informes\VentasGeneralesController;
use App\Http\Controllers\Informes\EstadoComprobanteController;
use App\Http\Controllers\Informes\DocumentosGeneralesController;
use App\Http\Controllers\Informes\ResumenComprobantesController;
//TABLAS
use App\Http\Controllers\Tablas\NitController;
use App\Http\Controllers\Tablas\ExogenaController;
use App\Http\Controllers\Tablas\BodegasController;
use App\Http\Controllers\Tablas\FamiliasController;
use App\Http\Controllers\Tablas\ProductosController;
use App\Http\Controllers\Tablas\PlanCuentaController;
use App\Http\Controllers\Tablas\FormasPagoController;
use App\Http\Controllers\Tablas\VendedoresController;
use App\Http\Controllers\Tablas\UbicacionesController;
use App\Http\Controllers\Tablas\CentroCostoController;
use App\Http\Controllers\Tablas\PresupuestoController;
use App\Http\Controllers\Tablas\ComprobantesController;
use App\Http\Controllers\Tablas\ResolucionesController;
use App\Http\Controllers\Tablas\ConceptoGastosController;
use App\Http\Controllers\Tablas\CargueDescargueController;
//CAPTURAS
use App\Http\Controllers\Capturas\VentaController;
use App\Http\Controllers\Capturas\PagosController;
use App\Http\Controllers\Capturas\CompraController;
use App\Http\Controllers\Capturas\GastosController;
use App\Http\Controllers\Capturas\PedidoController;
use App\Http\Controllers\Capturas\RecibosController;
use App\Http\Controllers\Capturas\ReservaController;
use App\Http\Controllers\Capturas\ParqueaderoController;
use App\Http\Controllers\Capturas\NotaCreditoController;
use App\Http\Controllers\Capturas\DocumentoGeneralController;
use App\Http\Controllers\Capturas\DocumentoEliminarController;
use App\Http\Controllers\Capturas\MovimientoInventarioController;
//CONFIGURACION
use App\Http\Controllers\InstaladorController;
use App\Http\Controllers\Configuracion\EntornoController;
use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Configuracion\UsuariosController;
//IMPORTADORES
use App\Http\Controllers\Importador\NitsImportadorController;
use App\Http\Controllers\Importador\ProductoImportadorController;
use App\Http\Controllers\Importador\DocumentosImportadorController;

// use App\Models\Sistema\PlanCuentas;
// use App\Models\Sistema\ConPlanCuentas;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/pdf', function () {

// 	// return view('pdf.facturacion.documentos');
// 	$pdf = app('dompdf.wrapper');
//     $pdf->loadView('pdf.facturacion.documentos');
// 	// $pdf->setPaper('Letter', 'landscape');
// 	$pdf->setPaper('A4', '');
//     return $pdf->stream('mi-archivo.pdf');
// });

Route::get('/', function () {
	return redirect('/home');
});

Auth::routes();

Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::get('/login-direct', [LoginController::class, 'loginDirectoGet'])->name('login-direct-get');
Route::post('/login-direct', [LoginController::class, 'loginDirectoPost'])->name('login-direct-post');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');
Route::get('/recibos-print', [RecibosController::class, 'showPdfPublic'])->name('recibos-pdf');
// Route::get('/documento-print', [DocumentoController::class, 'showPdfPublic']);


Route::group(['middleware' => ['auth:sanctum']], function () {

	//EMPRESA
	Route::get('/seleccionar-empresa', [ApiController::class, 'index'])->name('seleccionar-empresa');
	
	//SISTEMA
	Route::group(['middleware' => ['clientconnectionweb']], function () {
		// >> INFORMES <<
		Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
		Route::get('/home', [HomeController::class, 'index'])->name('home');
		//AUXILIARES
		Route::get('/auxiliar', [AuxiliarController::class, 'index'])->name('auxiliar');
		Route::get('/auxiliar-pdf/{id}', [AuxiliarController::class, 'showPdf'])->name('auxiliar-pdf');
		Route::get('/auxiliar-excel', [AuxiliarController::class, 'exportExcel'])->name('auxiliar-excel');
		//BALANCE
		Route::get('/balance', [BalanceController::class, 'index'])->name('balance');
		Route::get('/balance-pdf/{id}', [BalanceController::class, 'showPdf'])->name('balance-pdf');
		Route::get('/balance-excel', [BalanceController::class, 'exportExcel'])->name('balance-excel');
		//CUENTAS POR COBRAR
		Route::get('/cartera', [CarteraController::class, 'index'])->name('cartera');
		//IMPUESTOS
		Route::get('/impuestos', [ImpuestosController::class, 'index'])->name('impuestos');		
		//CUENTAS POR COBRAR
		Route::get('/resumencomprobante', [ResumenComprobantesController::class, 'index'])->name('resumencomprobante');
		//CUENTAS POR COBRAR
		Route::get('/resultados', [ResultadosController::class, 'index']);
		//DOCUMENTO GENERAL
		Route::get('/documentogeneral', [DocumentoGeneralController::class, 'index'])->name('documento-general');
		//ELIMINAR DOCUMENTOS
		Route::get('/eliminardocumentos', [DocumentoEliminarController::class, 'index'])->name('eliminar-documentos');
		//MOVIMIENTO INVENTARIO
		Route::get('/movimientoinventario', [MovimientoInventarioController::class, 'index']);
		//COMPRAS
		Route::get('/compra', [CompraController::class, 'index'])->name('compra');
		Route::get('/compras', [CompraController::class, 'indexInforme'])->name('compras');
		Route::get('/compras-print/{id}', [CompraController::class, 'showPdf'])->name('compra-pdf');
		//VENTAS
		Route::get('/venta', [VentaController::class, 'index'])->name('venta');
		Route::get('/ventas', [VentaController::class, 'indexInforme'])->name('ventas');
		Route::get('/ventas-print/{id}', [VentaController::class, 'showPdf'])->name('venta-pdf');
		Route::get('/ventas-print-informez', [VentaController::class, 'showPdfZ']);
		//PEDIDOS
		Route::get('/pedido', [PedidoController::class, 'index'])->name('pedido');
		Route::get('/pedido-print/{id}', [PedidoController::class, 'showPdf'])->name('pedido-pdf');
		//PARQUEADEROS
		Route::get('/parqueadero', [ParqueaderoController::class, 'index'])->name('parqueadero');
		Route::get('/parqueadero-print/{id}', [ParqueaderoController::class, 'showPdf'])->name('parqueadero-pdf');
		//RECIBOS
		Route::get('/recibo', [RecibosController::class, 'index'])->name('recibo');
		Route::get('/recibo-print/{id}', [RecibosController::class, 'showPdf'])->name('recibo-pdf');
		//PAGOS
		Route::get('/pago', [PagosController::class, 'index'])->name('pago');
		Route::get('/pago-print/{id}', [PagosController::class, 'showPdf'])->name('pago-pdf');
		//GASTOS
		Route::get('/gasto', [GastosController::class, 'index']);
		Route::get('/gasto-print/{id}', [GastosController::class, 'showPdf'])->name('gasto-pdf');
		//NOTA CREDITO
		Route::get('/notacredito', [NotaCreditoController::class, 'index']);
		Route::get('/ventas-print/{id}', [VentaController::class, 'showPdf'])->name('venta-pdf');
		//RESERVA
		Route::get('/reserva', [ReservaController::class, 'index']);
		Route::get('/reserva-evento', [ReservaController::class, 'read']);
		//NITS
		Route::get('/nit', [NitController::class, 'index'])->name('nit');
		//PLAN CUENTAS
		Route::get('/plancuenta', [PlanCuentaController::class, 'index'])->name('plan-cuenta');
		//EXOGENA
		Route::get('/exogena', [ExogenaController::class, 'index'])->name('exogena');
		//COMPROBANTES
		Route::get('/comprobante', [ComprobantesController::class, 'index'])->name('comprobante');
		//COMPROBANTES
		Route::get('/cecos', [CentroCostoController::class, 'index'])->name('cecos');
		//UBICACIONES
		Route::get('/ubicaciones', [UbicacionesController::class, 'index'])->name('ubicaciones');
		//COMPROBANTES
		Route::get('/vendedores', [VendedoresController::class, 'index'])->name('vendedores');
		//FAMILIAS
		Route::get('/familias', [FamiliasController::class, 'index'])->name('familias');
		//RESOLUCION
		Route::get('/resolucion', [ResolucionesController::class, 'index'])->name('resolucion');
		//FAMILIAS
		Route::get('/formapago', [FormasPagoController::class, 'index'])->name('forma-pago');
		//BODEGAS
		Route::get('/bodegas', [BodegasController::class, 'index'])->name('bodegas');
		//BODEGAS
		Route::get('/productos', [ProductosController::class, 'index'])->name('productos');
		//CARGUE DESCARGUE
		Route::get('/carguedescargue', [CargueDescargueController::class, 'index']);
		//CONCEPTO GASTOS
		Route::get('/conceptogastos', [ConceptoGastosController::class, 'index']);
		//PRESUPUESTO
		Route::get('/presupuesto', [PresupuestoController::class, 'index']);
		//DOCUMENTOS
		Route::get('/documentos', [DocumentoController::class, 'index'])->name('documentos');
		Route::get('/documentos-print/{id}', [DocumentoController::class, 'showPdf'])->name('documento-pdf');
		Route::get('/documentos-generales-print/{id_comprobante}/{consecutivo}/{fecha_manual}', [DocumentoController::class, 'showGeneralPdf'])->name('documento-generales-pdf');
		//DOCUMENTOS GENERALES
		Route::get('/documentosgenerales', [DocumentosGeneralesController::class, 'index']);
		// Route::get('/documentos-print/{id}', [DocumentoController::class, 'showPdf'])->name('documento-pdf');
		//VENTAS GENERALES
		Route::get('/ventasgenerales', [VentasGeneralesController::class, 'index']);
		//ESTADO ACTUAL
		Route::get('/estadoactual', [EstadoActualController::class, 'index']);
		//ESTADO COMPROBANTE
		Route::get('/estadocomprobante', [EstadoComprobanteController::class, 'index']);
		
		//USUARIOS
		Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios');
		//EMPRESA
		Route::get('/empresa', [EmpresaController::class, 'index'])->name('empresa');
		Route::post('/loadrut', [EmpresaController::class, 'rut']);
		Route::post('/instalacionempresa', [InstaladorController::class, 'instalar']);
		Route::post('/actualizarempresa', [InstaladorController::class, 'actualizar']);

		//ENTORNO
		Route::get('/entorno', [EntornoController::class, 'index'])->name('entorno');

		//IMPORTADORES PRODUCTOS
		Route::get('/productoprecios', [ProductoImportadorController::class, 'index']);
		Route::get('/productoprecios-exportar', [ProductoImportadorController::class, 'exportar']);
		Route::post('/productoprecios-importar', [ProductoImportadorController::class, 'importar']);

		//IMPORTADORES NITS
		Route::get('/importnits', [NitsImportadorController::class, 'index']);
		Route::get('/importnits-exportar', [NitsImportadorController::class, 'exportar']);
		Route::post('/importnits-importar', [NitsImportadorController::class, 'importar']);

		//IMPORTADORES DOCUMENTOS
		Route::get('/importdocumentos', [DocumentosImportadorController::class, 'index']);
		Route::get('/importdocumentos-exportar', [DocumentosImportadorController::class, 'exportar']);
		Route::post('/importdocumentos-importar', [DocumentosImportadorController::class, 'importar']);
	});

	//ARGON
	Route::get('/virtual-reality', [PageController::class, 'vr'])->name('virtual-reality');
	Route::get('/rtl', [PageController::class, 'rtl'])->name('rtl');
	Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
	Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
	Route::get('/profile-static', [PageController::class, 'profile'])->name('profile-static'); 
	Route::get('/sign-in-static', [PageController::class, 'signin'])->name('sign-in-static');
	Route::get('/sign-up-static', [PageController::class, 'signup'])->name('sign-up-static'); 
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});