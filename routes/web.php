<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\OwnersController;
use App\Http\Controllers\ResidencesController;
use App\Http\Controllers\ParametersController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RolesController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ConnectionAzureController;
use PHPUnit\Framework\MockObject\Rule\Parameters;
use PHPUnit\Runner\Extension\ParameterCollection;

//**Check Session: true expirado, false activo**
Route::get('/check-session', function () {
    return response()->json(['guest' => !Auth::check()]);
});

//**Check Conexion azure
Route::get('/check-connection', [ConnectionAzureController::class, 'checkConnection']);

//RedirectIfAuthenticated: Si el usuario está autenticado, bloquear el acceso a vista login.
Route::middleware(['RedirectIfAuthenticated'])->group(function () {
    Route::get('/', function () {
        return view('auth.login');
    });
});

//Auth: Siguientes vistas disponibles solo si esta autenticado
//RedirectNewPassword: Vista ResetPassword obligatorio
//MainRole; Compartamiento segun los roles principales Permitir todo / Bloquear todo.
Route::middleware(['auth', 'RedirectNewPassword', 'MainRole'])->group(function () {


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/account-desactivate', [ProfileController::class, 'desactive'])->name('profile.desactive');
    //Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //**MainController::periodos**
    Route::get('/utilities-periods', [MainController::class, 'index'])->name('utilities-periods'); ///VISTA: inicio periodos de captura
    Route::get('/viewperiod', [MainController::class, 'viewperiod'])->name('viewperiod'); //VISTA: modal ver un periodo en especifico
    Route::patch('/updata-utilities', [MainController::class, 'updateUtilities'])->name('utilities.update');
    Route::post('/create-period-utilities', [MainController::class, 'createPeriod'])->name('createperiod.utilities'); //FUNCTION: Crear nuevo periodo Utilitites
    Route::get('/bills-utilities', [MainController::class, 'billsUtilities'])->name('bills.utilities'); //VISTA: modal ver un periodo en especifico

    //**ImportController:: Importaciones utilities** 
    Route::get('/selectimport', [ImportController::class, 'selectimport'])->name('selectimport'); //VISTA:  importar plantilla excel
    Route::get('/download_utilitiestmp1', [ImportController::class, 'download_utilitiestmp1'])->name('download_utilitiestmp1'); //VISTA:  importar plantilla excel
    Route::get('/logimports', [ImportController::class, 'log_import'])->name('logimport'); //VISTA:  tabla log importaciones
    Route::get('/viewmport', [ImportController::class, 'viewimport'])->name('viewimport'); //FUNCTION: modal ver datos de un excel importado
    Route::put('/checkimport', [ImportController::class, 'checkimport'])->name('checkimport'); //FUNCTION: validar importacion mantenimiento/finanzas
    Route::post('/readsheets', [ImportController::class, 'readsheets'])->name('readsheets'); //FUNCTION: leer hojas excel utilities
    Route::post('/process_sheet', [ImportController::class, 'process_sheet'])->name('process_sheet'); //FUNCTION: validar estroctura de plantilla en la hoja
    Route::post('/importexcel', [ImportController::class, 'importexcel'])->name('importexcel'); //FUNCTION: importar utilities
    Route::get('/readcommentsIntable', [ImportController::class, 'readcommentsIntable'])->name('readcommentsIntable'); //FUNCTION: leer comentarios en celda
    Route::put('/savecommentIntable', [ImportController::class, 'savecommentIntable'])->name('savecommentIntable'); //FUNCTION: actualizar comentarios en celda
    Route::post('/newcommentIntable', [ImportController::class, 'newcommentIntable'])->name('newcommentIntable'); //FUNCTION: agregar comentarios en celda
    Route::delete('/destroycommentIntable', [ImportController::class, 'destroycommentIntable'])->name('destroycommentIntable'); //FUNCTION: eliminar comentarios en celda

    //**ResidenceController:: Modulo residencias**/
    Route::resource('residences', ResidencesController::class);

    //**OwnerController:: Modulo dueños**/
    Route::resource('owners', OwnersController::class);

    //**MeterController:: Modulo parametros**/
    Route::resource('parameters', ParametersController::class);

    //**UserController:: Modulo usuarios**/
    Route::resource('users', UsersController::class);
    Route::get('/change-password', [UsersController::class, 'resetpassword'])->name('auth.change-password');

    //**RoleController:: Modulo roles**/
    Route::resource('roles', RolesController::class);

    //**AuditController:: Modulo auditoria*/
    Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit/data', [AuditController::class, 'getAuditData'])->name('audit.data');

    //**ParametersController:: Modulo parametros/
    Route::get('/load-costs-inperiod', [ParametersController::class, 'load_costs_inperiod'])->name('load_costs_inperiod'); //FUNCTION: cargar servicios y sus #nro de costos asignados
    Route::get('/load-costs', [ParametersController::class, 'load_costs'])->name('loadcosts'); //FUNCTION: cargar datos de costo en especifico
    Route::get('/load-costs-inservice', [ParametersController::class, 'load_costs_inservice'])->name('load_costs_inservice'); //FUNCTION: cargar costos disponibles en servicio
    Route::get('/load-databyresidence', [ParametersController::class, 'load_databyresidence'])->name('load_databyresidence'); //FUNCTION: cargar datos de residencia en especifico
    Route::get('/load-resultcosts', [ParametersController::class, 'load_resultcosts'])->name('load_resultcosts'); //FUNCTION: recargar el costo final del card activo
    Route::post('/find-costbycode', [ParametersController::class, 'find_costbycode'])->name('find_costbycode'); //FUNCTION: buscar codigo de la formula si existen
    Route::post('/save-parameter-tc-utilities', [ParametersController::class, 'save_parameters_tc'])->name('spartc_utilities'); //FUNCTION: guardar parametros generales
    Route::post('/save-parameter-cost-utilities', [ParametersController::class, 'save_parameters_costs'])->name('sparcost_utilities'); //FUNCTION: guardar parametros costos
    Route::post('/new-parameters-costs', [ParametersController::class, 'new_parameters_costs'])->name('new_parameters_costs'); //FUNCTION: guardar  nuevo parametros costos
    Route::get('/find-cost-residences', [ParametersController::class, 'find_cost_residences'])->name('find_cost_residences'); //FUNCTION: buscar si hay costo asociados en residencias
    Route::delete('/delete-parameters-costs', [ParametersController::class, 'delete_parameters_costs'])->name('delete_parameters_costs'); //FUNCTION: eliminar costo de un servicio
    Route::post('/save-parameter-res-utilities', [ParametersController::class, 'save_parameters_res'])->name('sparres_utilities'); //FUNCTION: guardar parametros en residencia en especifica
    Route::post('/save-parameter-someres-utilities', [ParametersController::class, 'save_parameters_someres'])->name('sparsomeres_utilities'); //FUNCTION: guardar parametros varios residenciales
    Route::get('/load-parameters-avaible', [ParametersController::class, 'parametersavaible'])->name('parametersavaible'); //FUNCTION: cargar parametros disponibles a duplicar
    Route::post('/duplicateparameters', [ParametersController::class, 'duplicateparameters'])->name('duplicateparameters'); //FUNCTION: duplicar parametros
 


});

require __DIR__ . '/auth.php';
