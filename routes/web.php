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
use App\Http\Controllers\Informes\DocumentoController;
//TABLAS
use App\Http\Controllers\Tablas\NitController;
use App\Http\Controllers\Tablas\BodegasController;
use App\Http\Controllers\Tablas\FamiliasController;
use App\Http\Controllers\Tablas\ProductosController;
use App\Http\Controllers\Tablas\PlanCuentaController;
use App\Http\Controllers\Tablas\FormasPagoController;
use App\Http\Controllers\Tablas\CentroCostoController;
use App\Http\Controllers\Tablas\ComprobantesController;
use App\Http\Controllers\Tablas\ResolucionesController;
//CAPTURAS
use App\Http\Controllers\Capturas\VentaController;
use App\Http\Controllers\Capturas\CompraController;
use App\Http\Controllers\Capturas\DocumentoGeneralController;
//CONFIGURACION
use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Configuracion\UsuariosController;



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
	// $n = 7;

	// if($n == 0) echo "ERROR";

	// for ($i = 0; $i < $n; $i++) {
	// 	$x = '';

	// 	for ($j = 0; $j < $n; $j++) {

	// 		if ($j === $i || $j === $n - $i - 1) $x.= 'X';
	// 		else $x.= '_';

	// 	}
	// 	echo $x.'<br/>';
	// }

	// $arr = array(1,2,9,2,5,3,5,1,5);

	// $array = [
	// 	0 => [],
	// 	1 => [],
	// 	2 => [],
	// ];
	// $count = 0;
	// $position = 0;
	
	// for ($i = 0; $i < count($arr); $i++) {
	// 	$count++;
	// 	$array[$position][] = $arr[$i];

	// 	if (!($count % 3)) $position++;
	// }
	// dd($array)
	// $valorActual = 0;
	// $columnaActual = 0;
	// $movimientos = [];
	// $validarPosicion = false;
	// do {
	// 	for ($i=0; $i < count($array); $i++) {
	// 		$valor = $array[$i][$columnaActual];
	// 		if ($validarPosicion) {
	// 			if () {

	// 			}
	// 		}
	// 		if (!$valorActual) {
	// 			$valorActual = $valor;
	// 			$movimientos[$columnaActual] = $i;
	// 		}else if ($valorActual > $valor) {
	// 			$valorActual = $valor;
	// 			$movimientos[$columnaActual] = $i;
	// 		}
	// 	}
	// 	$columnaActual++;
	// 	$valorActual = 0;
	// 	$validarPosicion = true;
	// } while ($columnaActual != 3);
	// dd($movimientos);



	// $arr = array(1,2,9,2,5,3,5,1,5);
	$myArray = array(1,2,9,2,5,3,5,1,5);

	$count = 0;
	$arrayOrder = [0 => [], 1 => [], 2 => []];
	for ($i = 0; $i < count($myArray); $i++) {

		$arrayOrder[$count][] = $myArray[$i];
		$count++;
		
		if ($count && !($count % 3)) $count = 0;
	}

	$pasos = [];

	for ($i = 0; $i < count($arrayOrder); $i++) {
		$valorActual = $arrayOrder[$i][0];
		$pasos[$i] = 0;
		for($j = 0; $j < count($arrayOrder[$i]); $j++){
			$valor = $arrayOrder[$i][$j];
			if ($i == 0) {
				if ($valorActual > $valor) {
					$valorActual = $valor;
					$pasos[$i] = $j;
				}
			} else {
				$pasoAnterior = $pasos[$i - 1];
				$diferenciaPasos = ($pasoAnterior - $j) < 0 ? ($pasoAnterior - $j) *-1 : $pasoAnterior - $j;
				if ($diferenciaPasos == 0 || $diferenciaPasos == 1) {
					if ($valorActual > $valor) {
						$valorActual = $valor;
						$pasos[$i] = $j;
					}
				}
			}
		}
		if ( $i+1 < count($arrayOrder)) {
			echo $arrayOrder[$i][$pasos[$i]].' ';
		} else {
			echo $arrayOrder[$i][$pasos[$i]];
		}
	}
});

Auth::routes();


Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');


Route::group(['middleware' => ['auth']], function () {

	//EMPRESA
	Route::get('/seleccionar-empresa', [ApiController::class, 'index'])->name('seleccionar-empresa');

	//SISTEMA
	Route::group(['middleware' => ['clientconnectionweb']], function () {
		// >> INFORMES <<
		Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
		Route::get('/home', [HomeController::class, 'index'])->name('home');
		//AUXILIARES
		Route::get('/auxiliar', [AuxiliarController::class, 'index'])->name('auxiliar');
		Route::get('/auxiliar-excel', [AuxiliarController::class, 'exportExcel'])->name('auxiliar-excel');
		//BALANCE
		Route::get('/balance', [BalanceController::class, 'index'])->name('balance');
		Route::get('/balance-excel', [BalanceController::class, 'exportExcel'])->name('balance-excel');
		//CUENTAS POR COBRAR
		Route::get('/cartera', [CarteraController::class, 'index'])->name('cartera');
		//DOCUMENTO GENERAL
		Route::get('/documentogeneral', [DocumentoGeneralController::class, 'index'])->name('documento-general');
		//COMPRAS
		Route::get('/compra', [CompraController::class, 'index'])->name('compra');
		Route::get('/compras', [CompraController::class, 'indexInforme'])->name('compras');
		Route::get('/compras-print/{id}', [CompraController::class, 'showPdf'])->name('compra-pdf');
		//VENTAS
		Route::get('/venta', [VentaController::class, 'index'])->name('venta');
		Route::get('/ventas', [VentaController::class, 'indexInforme'])->name('ventas');
		Route::get('/ventas-print/{id}', [VentaController::class, 'showPdf'])->name('venta-pdf');
		//NITS
		Route::get('/nit', [NitController::class, 'index'])->name('nit');
		//PLAN CUENTAS
		Route::get('/plancuenta', [PlanCuentaController::class, 'index'])->name('plan-cuenta');
		//COMPROBANTES
		Route::get('/comprobante', [ComprobantesController::class, 'index'])->name('comprobante');
		//COMPROBANTES
		Route::get('/cecos', [CentroCostoController::class, 'index'])->name('cecos');
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
		//DOCUMENTOS
		Route::get('/documentos', [DocumentoController::class, 'index'])->name('documentos');
		Route::get('/documentos-print/{id}', [DocumentoController::class, 'showPdf'])->name('documento-pdf');
		//USUARIOS
		Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios');
		//EMPRESA
		Route::get('/empresa', [EmpresaController::class, 'index'])->name('empresa');
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