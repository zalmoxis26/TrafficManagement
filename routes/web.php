<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpresaController; // Verifica esta línea
use App\Http\Controllers\PedimentoController; // Verifica esta línea
use App\Http\Controllers\TraficoController;// Verifica esta línea
use App\http\Controllers\EmbarqueController;
use App\http\Controllers\UsersEmpresaController;
use App\http\Controllers\RevisioneController;
use App\http\Controllers\AnexoController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\CommentController;





Route::get('/', function () {
    return view('welcome');
})->name('inicio');


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/comentario/agregar', [CommentController::class, 'agregarComentario'])->name('comentario.agregar');
Route::post('/comentarioEmbarque/agregar', [CommentController::class, 'agregarComentarioEmbarque'])->name('comentario.agregarEmbarque');
Route::get('/Embarques/exportar/embarques', [EmbarqueController::class, 'export'])->name('exportar-embarques');


//------------RUTAS GENERALES ---------->

Route::middleware(['auth', 'role:revisor|admin|documentador|cliente'])->group(function () {

    //TRAFICOS
     Route::resource('traficos', TraficoController::class);
     Route::get('traficos/status/cerrados', [TraficoController::class, 'indexTraficosCerrados'])->name('traficos.cerrados');
     Route::get('/trafico-factura/stream/{id}', [TraficoController::class, 'streamFactura'])->name('facturas.stream');
    
 
     //ANEXOS 
     Route::resource('/anexos', AnexoController::class);
     Route::post('/anexo', [AnexoController::class, 'store'])->name('anexo.store');
 
     //REVISIONES
      Route::get('revisiones', [RevisioneController::class, 'index'])->name('revisiones.index');
      Route::get('revisiones/{revisione}', [RevisioneController::class, 'show'])->name('revisiones.show');

 
 
 });

//------------RUTAS GENERALES SIN REVISION---------->

 Route::middleware(['auth', 'role:admin|documentador|cliente'])->group(function () {

    //EMBARQUES
    Route::get('/embarques', [EmbarqueController::class, 'index'])->name('embarques.index');
    Route::get('/embarques/create', [EmbarqueController::class, 'create'])->name('embarques.create');
    Route::get('/embarques/{embarque}', [EmbarqueController::class, 'show'])->name('embarques.show');
    Route::get('/embarques/{embarque}/edit', [EmbarqueController::class, 'edit'])->name('embarques.edit');
    Route::delete('/embarques/{embarque}', [EmbarqueController::class, 'destroy'])->name('embarques.destroy');


    //PEDIMENTOS
    Route::get('pedimentos/stream/{id}', [PedimentoController::class, 'streamPedimento'])->name('pedimentos.stream');
    // REPORTE EXCEL
    Route::get('/export-trafico', [TraficoController::class, 'exportTraficos'])->name('trafico.export');
});



//---------------------------------RUTAS DE DOCUEMENTADORES  ------------ //> 

Route::middleware(['auth', 'role:admin|documentador'])->group(function () {

        //EMPRESAS
        Route::resource('empresas', EmpresaController::class);

        
        //EMBARQUES
        Route::patch('/embarques/{embarque}', [EmbarqueController::class, 'update'])->name('embarques.update');
        Route::post('/embarques', [EmbarqueController::class, 'store'])->name('embarques.store');
        Route::post('/embarques/FromTrafico/create', [EmbarqueController::class , 'createFromTrafico'])->name('embarque.createFromTrafico');
        Route::post('/embarques/FromTrafico/store', [EmbarqueController::class , 'storeFromTrafico'])->name('embarquesFromTrafico.store');
        Route::post('/embarques/FromTrafico/desasignar', [EmbarqueController::class , 'desasignarFromTrafico'])->name('embarque.desasignarFromTrafico');

        //PEDIMENTOS
        Route::resource('pedimentos', PedimentoController::class);
        Route::get('pedimentos/create/Trafico/{id}', [PedimentoController::class, 'createFromTrafico'])->name('pedimentoCreateFromTrafico');
        Route::post('pedimentos/from-trafico/{id}', [PedimentoController::class, 'storeFromTrafico'])->name('pedimentos.storeFromTrafico');
        Route::get('pedimentos/edit/Trafico/{id}/{pedimentoId}', [PedimentoController::class, 'editFromTrafico'])->name('pedimentoEditFromTrafico');
        Route::patch('pedimentos/update/from-trafico/{pedimentoId}', [PedimentoController::class, 'updateFromTrafico'])->name('pedimentoUpdateFromTrafico');

    
        //USER EMPRESA
        Route::resource('users-empresas', UsersEmpresaController::class);

        //ASIGNAR ROLES 
        Route::get('/roles/listaRolesAsignados', [UserRoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/assign-roles', [UserRoleController::class, 'create'])->name('users.assign-roles');
        Route::post('/roles/update-roles', [UserRoleController::class, 'update'])->name('users.update-roles');
        Route::get('/roles/{user}/edit', [UserRoleController::class, 'edit'])->name('users.edit');
        Route::put('/roles/{user}/update-roles', [UserRoleController::class, 'updateRoles'])->name('roles.update-roles');



});

//---------------------------------------------RUTAS DE CLIENTES --- //>

Route::middleware(['auth', 'role:admin|cliente'])->group(function () {

        // ruta para crear Trafico desde factura
        Route::get('/trafico-factura', [TraficoController::class, 'createFromFactura'])->name('traficoDesdeFactura');
        Route::post('/trafico-factura', [TraficoController::class, 'storeFromFactura'])->name('trafico.storeFromFactura'); // Ruta para enviar el formulario
        Route::post('/trafico/sustituir-factura', [TraficoController::class, 'sustituirFactura'])->name('trafico.sustituirFactura');
 

});


 //---------------------------------------------RUTAS DE REVISORES --- //>

 Route::middleware(['auth', 'role:revisor|admin'])->group(function () {


    Route::get('revisiones/create', [RevisioneController::class, 'create'])->name('revisiones.create');
    Route::post('revisiones', [RevisioneController::class, 'store'])->name('revisiones.store');
    Route::get('revisiones/{revisione}/edit', [RevisioneController::class, 'edit'])->name('revisiones.edit');
    Route::patch('revisiones/{revisione}', [RevisioneController::class, 'update'])->name('revisiones.update');
    Route::delete('revisiones/{revisione}', [RevisioneController::class, 'destroy'])->name('revisiones.destroy');

});

Auth::routes();













