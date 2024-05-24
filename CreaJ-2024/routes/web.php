<?php
use App\Http\Controllers\MercadoLocalController;
use Illuminate\Support\Facades\Route;

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

/*Vistas de usuarios*/
Route::view('/','Index')->name('Index');
Route::view('/LoginUser','LoginUser')->name('LoginUser');
Route::view('/RegistroUser','RegistroUser')->name('RegistroUser');
Route::view('/ProductosUser','ProductosUser')->name('ProductosUser');
Route::view('/EditarPerfilUser','EditarPerfilUser')->name('EditarPerfilUser');
Route::view('/CarritoGeneralUser','CarritoGeneralUser')->name('CarritoGeneralUser');
Route::view('/CarritoDePuestoUser','CarritoDePuestoUser')->name('CarritoDePuestoUser');
Route::view('/HomeMercadoUser','HomeMercadoUser')->name('HomeMercadoUser');
Route::view('/ProductoPuesto','ProductoPuesto')->name('ProductoPuesto');
Route::view('/HomeUser','HomeUser')->name('HomeUser');
Route::view('/EstadoPedidosUser','EstadoPedidosUser')->name('EstadoPedidosUser');
Route::view('/Profile','Profile')->name('ProfileUser');
Route::view('/HistorialPedidosUser','HistorialPedidosUser')->name('HistorialPedidosUser');



/*Vistas de Vendedores*/
Route::view('/RegistroProductoVendedor','RegistroProductoVendedor')->name('RegistroProductoVendedor');
Route::view('/EditarProductoVendedor','EditarProductoVendedor')->name('EditarProductoVendedor');
Route::view('/EditarPuestoVendedor','EditarPuestoVendedor')->name('EditarPuestoVendedor');
Route::view('/CarritoPuestoVendedor','CarritoPuestoVendedor')->name('CarritoPuestoVendedor');
Route::view('/CarritoPuestoVendedor','CarritoPuestoVendedor')->name('CarritoPuestoVendedor');
Route::view('/EditPuestoVendedor','EditPuestoVendedor')->name('EditPuestoVendedor');
Route::view('/ProfileVendedor','ProfileVendedor')->name('ProfileVendedor');
Route::view('/ProductoVendedor','ProductoVendedor')->name('ProductoVendedor');
Route::view('/HomeVendedor','HomeVendedor')->name('HomeVendedor');





/*Vistas de adminsitrador*/
Route::view('/EditarMercadoAdmin','EditarMercadoAdmin')->name('EditarMercadoAdmin');
Route::view('/RegistrarVendedorAdmin','RegistrarVendedorAdmin')->name('RegistrarVendedorAdmin');
Route::view('/ProfileAdmin','ProfileAdmin')->name('ProfileAdmin');
Route::view('/EditarPuestoAdmin','EditarPuestoAdmin')->name('EditarPuestoAdmin');
Route::view('/ListadoVendedoresAdmin','ListadoVendedoresAdmin')->name('ListadoVendedoresAdmin');
Route::view('/PerfilVendedor','PerfilVendedor')->name('PerfilVendedor');
// Route::view('/AgregarMercadoVendedor','AgregarMercadoVendedor')->name('AgregarMercadoVendedor');

/*Administrador General*/ 
Route::view('/ProfileAdminGeneral','ProfileAdminGeneral')->name('ProfileAdminGeneral');
Route::view('/HomeAdminGeneral','HomeAdminGeneral')->name('HomeAdminGeneral');
Route::view('/HomeAdminGeneral','HomeAdminGeneral')->name('HomeAdminGeneral');
Route::view('/ListadoVendedoresAdminGeneral','ListadoVendedoresAdminGeneral')->name('ListadoVendedoresAdminGeneral');
Route::view('/HomeMercadoAdminGeneral','HomeMercadoAdminGeneral')->name('HomeMercadoAdminGeneral');
Route::view('/AgregarMercadoAdminGeneral','AgregarMercadoAdminGeneral')->name('AgregarMercadoAdminGeneral');
Route::view('/HistorialPedidosAdminGeneral','HistorialPedidosAdminGeneral')->name('HistorialPedidosAdminGeneral');
Route::view('/EstadoPedidosAdminGeneral','EstadoPedidosAdminGeneral')->name('EstadoPedidosAdminGeneral');
Route::view('/PerfilVendedorAdminGeneral','PerfilVendedorAdminGeneral')->name('PerfilVendedorAdminGeneral');



// Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::resource('mercado-locals', MercadoLocalController::class);

