<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmpresaController; // Verifica esta línea
use App\Http\Controllers\PedimentoController; // Verifica esta línea
use App\Http\Controllers\TraficoController;// Verifica esta línea
use App\Http\Controllers\EmbarqueController;
use App\Http\Controllers\UsersEmpresaController;
use App\Http\Controllers\RevisioneController;
use App\Http\Controllers\AnexoController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PedimentoTxtController;
use App\Http\Controllers\DocumentFtpController;




Route::get('/', function () {
    return view('welcome');
})->name('inicio');

Auth::routes();



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/comentario/agregar', [CommentController::class, 'agregarComentario'])->name('comentario.agregar');
Route::post('/comentarioEmbarque/agregar', [CommentController::class, 'agregarComentarioEmbarque'])->name('comentario.agregarEmbarque');
Route::get('/Embarques/exportar/embarques', [EmbarqueController::class, 'export'])->name('exportar-embarques');


//DOCUMENTOS FTP
Route::get('/documents/{directory?}', [DocumentFtpController::class, 'index'])->where('directory', '.*')->name('documents.index');
Route::get('/descargar/documents/{filename}', [DocumentFtpController::class, 'download'])->where('filename', '.*')->name('documents.download');

Route::get('/dispatch-ftp-job', [FtpController::class, 'dispatchJob']);//despachas ftp job
Route::post('/upload-to-ftp', [DocumentFtpController::class, 'uploadToFTP']);//subir al ftp
Route::get('/upload', function () {
    return view('pedimentoTxt.uploadFTP');
});

//DOCUMENTOS LOCALES
Route::get('documentslocales/storage/{directory?}', [DocumentFtpController::class, 'indexLocal'])->where('directory', '.*')->name('documentsLocales.index');
Route::get('documentslocales/download/{directory}/{filename}', [App\Http\Controllers\DocumentFtpController::class, 'downloadLocal'])->where(['directory' => '.*', 'filename' => '.*'])->name('documentsLocales.download');
Route::get('documentslocales/view/{directory}/{filename}', [App\Http\Controllers\DocumentFtpController::class, 'viewFile'])->where(['directory' => '.*', 'filename' => '.*'])->name('documentsLocales.view');

//PEDIMENTOS TXT (VIZUALIZAR 1 A LA VEZ, ANTIGUO)

Route::get('Antiguo/cargarPedimentoTxt', [PedimentoTxtController::class, 'cargarUnTxt']);
Route::post('Antiguo/mostrarPedimentoTxt', [PedimentoTxtController::class, 'visualizarUnTxt']);

//PEDIMENTO TXT CARGAR STORE E INDEX 

Route::get('/pedimentosTxt', [PedimentoTxtController::class, 'index'])->name('pedimentoTxt.index');
Route::get('cargarPedimentoTxt', [PedimentoTxtController::class, 'create']);
Route::post('/guardarPedimentoTxt', [PedimentoTxtController::class, 'store']);
Route::get('/pedimentosTxt/opcionesExport', [PedimentoTxtController::class, 'OpcionesExportPedimentosTxt'])->name('opciones.exportPedimentoTxt');






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
    Route::patch('/embarques/{embarque}', [EmbarqueController::class, 'update'])->name('embarques.update');
    Route::post('/embarques', [EmbarqueController::class, 'store'])->name('embarques.store');
    Route::post('/embarques/FromTrafico/create', [EmbarqueController::class , 'createFromTrafico'])->name('embarque.createFromTrafico');
    Route::post('/embarques/FromTrafico/store', [EmbarqueController::class , 'storeFromTrafico'])->name('embarquesFromTrafico.store');
    Route::post('/embarques/FromTrafico/desasignar', [EmbarqueController::class , 'desasignarFromTrafico'])->name('embarque.desasignarFromTrafico');
    
    Route::post('embarques/validadar/numEmbarque', [EmbarqueController::class, 'validateNumEmbarque'])->name('validate.numEmbarque');


     //EMPRESAS
     Route::resource('empresas', EmpresaController::class);


    //PEDIMENTOS
    Route::get('pedimentos/stream/{id}', [PedimentoController::class, 'streamPedimento'])->name('pedimentos.stream');
    // REPORTE EXCEL
    Route::get('/export-trafico', [TraficoController::class, 'exportTraficos'])->name('trafico.export');
});



//---------------------------------RUTAS DE DOCUEMENTADORES  ------------ //> 

Route::middleware(['auth', 'role:admin|documentador'])->group(function () {



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
        Route::delete('/roles/{user_id}/', [UserRoleController::class, 'deleteRole'])->name('roles.delete');


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















