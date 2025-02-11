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
use App\Http\Controllers\ExpedientePedimentoController;
use App\Http\Controllers\AdpPedimentoSaiAwsController;

use Illuminate\Support\Facades\Storage;



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


/*RUTAS PARA EL GOOGLE DRIVE ADP EXPEDIENTE DE PEDIMENTOS */
/*
// Vista Principal con Parámetro Opcional 'path'
Route::get('/expediente-pedimento', [ExpedientePedimentoController::class, 'index'])->name('pedimento.index');
// Crear Carpeta
Route::post('/expediente-pedimento/crear-carpeta', [ExpedientePedimentoController::class, 'crearCarpeta'])->name('pedimento.crearCarpeta');
// Cargar Archivos
Route::post('/expediente-pedimento/cargar-archivos', [ExpedientePedimentoController::class, 'cargarArchivos'])->name('pedimento.cargarArchivos');
// Eliminar Elementos
Route::post('/expediente-pedimento/eliminar-elementos', [ExpedientePedimentoController::class, 'eliminarElementos'])->name('pedimento.eliminarElementos');
// Descargar Elementos
Route::post('/expediente-pedimento/descargar-elementos', [ExpedientePedimentoController::class, 'descargarElementos'])->name('pedimento.descargarElementos');
// Descargar Archivo Individual
Route::get('/expediente-pedimento/get-file-url', [ExpedientePedimentoController::class, 'getFileUrl'])->name('pedimento.getFileUrl');
//renombrar carpetas o archivos
Route::post('/expediente-pedimento/renombrar', [ExpedientePedimentoController::class, 'renombrarElemento'])->name('pedimento.renombrarElemento');
// Ruta para la búsqueda AJAX
Route::get('/expediente-pedimento/pedimento/buscar', [ExpedientePedimentoController::class, 'buscar'])->name('pedimento.buscar');
*/




// RUTAS PARA AMAZON S3 - EXPEDIENTE DE PEDIMENTOS ADP
Route::prefix('expediente-pedimento')->group(function () {
    // Vista principal con parámetro opcional 'path'
    Route::get('/', [AdpPedimentoSaiAwsController::class, 'index'])->name('pedimento.index');
    
    // Acciones CRUD
    Route::post('/crear-carpeta', [AdpPedimentoSaiAwsController::class, 'crearCarpeta'])->name('pedimento.crearCarpeta');
    Route::post('/cargar-archivos', [AdpPedimentoSaiAwsController::class, 'cargarArchivos'])->name('pedimento.cargarArchivos');
    Route::post('/eliminar-elementos', [AdpPedimentoSaiAwsController::class, 'eliminarElementos'])->name('pedimento.eliminarElementos');
    Route::post('/descargar-elementos', [AdpPedimentoSaiAwsController::class, 'descargarElementos'])->name('pedimento.descargarElementos');
    
    // Acciones adicionales
    Route::get('/obtener-url', [AdpPedimentoSaiAwsController::class, 'getFileUrl'])->name('pedimento.getFileUrl');
    Route::post('/renombrar-archivo', [AdpPedimentoSaiAwsController::class, 'renombrarElemento'])->name('archivo.renombrar');
    Route::get('/buscar', [AdpPedimentoSaiAwsController::class, 'buscar'])->name('pedimento.buscar');

     
    


});



Route::get('/test-gcs', function () {
    try {
        $fileName = 'test-'.now()->timestamp.'.txt';
        $content = 'Contenido de prueba';
        
        // Subir con configuración explícita
        Storage::disk('gcs')->put($fileName, $content, [
            'visibility' => 'private', // Obligatorio con publicAccessPrevention
            'metadata' => [
                'cacheControl' => 'no-cache'
            ]
        ]);
        
        return response()->json([
            'status' => 'success',
            'file' => Storage::disk('gcs')->url($fileName)
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage()
        ], 500);
    }
});



Route::get('/debug-gcs', function () {
    $client = new \Google\Cloud\Storage\StorageClient([
        'projectId' => env('GOOGLE_CLOUD_PROJECT_ID'),
        'keyFilePath' => env('GOOGLE_CLOUD_KEY_FILE')
    ]);
    
    $bucket = $client->bucket(env('GOOGLE_CLOUD_STORAGE_BUCKET'));
    $object = $bucket->object('test-123456789.txt');
    
    return response()->json([
        'exists' => $object->exists(),
        'bucket_info' => $bucket->info()
    ]);
});

Route::get('/test-s3', function() {
    try {
        $oldDir = 'Inicio/test_old/';
        $newDir = 'Inicio/test_new/';
        $fileName = 'test.txt';
        $content = 'contenido de prueba';

        // 1. Subir archivo al directorio antiguo
        Storage::disk('s3')->put($oldDir . $fileName, $content);

        // 2. Mover el archivo al nuevo directorio (sin crear carpetas manualmente)
        Storage::disk('s3')->move( 
            $oldDir . $fileName,
            $newDir . $fileName
        );

        // 3. Verificar que el archivo existe en el nuevo directorio
        if (!Storage::disk('s3')->exists($newDir . $fileName)) {
            throw new Exception("El archivo no se movió correctamente");
        }

        // 4. Limpiar solo el directorio antiguo (opcional)
        // Storage::disk('s3')->deleteDirectory($oldDir);

        return "Éxito. Archivo movido permanentemente a: $newDir";

    } catch (\Exception $e) {
        \Log::error("Error S3: " . $e->getMessage());
        return "Fallo: " . $e->getMessage();
    }
});