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
use App\Http\Controllers\Tablas\FamiliasController;
use App\Http\Controllers\Tablas\PlanCuentaController;
use App\Http\Controllers\Tablas\CentroCostoController;
use App\Http\Controllers\Tablas\ComprobantesController;
//CAPTURAS
use App\Http\Controllers\Capturas\DocumentoGeneralController;


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
    return view('welcome');
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
		//DOCUMENTOS
		Route::get('/documentos', [DocumentoController::class, 'index'])->name('documentos');
		Route::get('/documentos-print/{id}', [DocumentoController::class, 'showPdf'])->name('show-pdf');

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