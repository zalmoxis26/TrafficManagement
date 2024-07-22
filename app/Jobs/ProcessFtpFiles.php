<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Trafico;
use App\Models\Revisione;
use App\Models\Empresa;
use App\Events\TraficoCreated;
use Illuminate\Support\Facades\Mail;
use App\Models\Historial;
use App\Mail\FacturaMail;
use Carbon\Carbon;
use App\Models\User;
use Log;


class ProcessFtpFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        
        try {
            $ftpDisk = Storage::disk('ftp');
            $localDisk = Storage::disk('local');
            $files = $ftpDisk->files('/');

          //  Log::info('Starting FTP process job', ['memory_usage' => memory_get_usage()]);

            // Procesar en lotes de 4 archivos a la vez
            $batchSize = 4;
            $chunks = array_chunk($files, $batchSize);

            foreach ($chunks as $chunk) {
                foreach ($chunk as $file) {
                    $contents = $ftpDisk->get($file);
                    $filename = basename($file);
                    $localDisk->put('invoices/' . $filename, $contents);

                 //   Log::info('Archivo descargado', ['filename' => $filename]);

                    $ftpDisk->delete($file);

                    // Liberar memoria después de cada archivo
                    unset($contents);

                    // Forzar la recolección de basura para liberar memoria
                    gc_collect_cycles();
                }
                // Log memory usage after each batch
            //    Log::info('Processed batch', ['memory_usage' => memory_get_usage()]);
            }

          //  Log::info('Finished FTP process job', ['memory_usage' => memory_get_usage()]);

            // Procesar los archivos locales después de la descarga
            $this->processLocalFiles();

        } catch (\Exception $e) {
            Log::error('Error in FTP process job', ['error' => $e->getMessage(), 'memory_usage' => memory_get_usage()]);
        }
    }

    private function processLocalFiles()
    {
        $localDisk = Storage::disk('local');
        $files = $localDisk->files('invoices');
    
        // Crear un array asociativo para almacenar los nombres de los archivos sin extensión
        $filePairs = [];
    
        // Separar los archivos por su nombre base sin extensión
        foreach ($files as $file) {
            $filenameWithoutExtension = pathinfo($file, PATHINFO_FILENAME);
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
            if (!isset($filePairs[$filenameWithoutExtension])) {
                $filePairs[$filenameWithoutExtension] = ['txt' => false, 'pdf' => false];
            }
    
            if ($extension == 'txt') {
                $filePairs[$filenameWithoutExtension]['txt'] = true;
            } elseif ($extension == 'pdf') {
                $filePairs[$filenameWithoutExtension]['pdf'] = true;
            }
        }
    
        // Definir el tamaño del lote
        $batchSize = 4; // Puedes ajustar el tamaño del lote según tus necesidades
        $filePairsChunks = array_chunk($filePairs, $batchSize, true);
    
        foreach ($filePairsChunks as $chunk) {
            foreach ($chunk as $filenameWithoutExtension => $extensions) {
                // Verificar si ambos archivos (TXT y PDF) están presentes
                if ($extensions['txt'] && $extensions['pdf']) {
                    $txtFilePath = storage_path('app/invoices/' . $filenameWithoutExtension . '.txt');
                  //  Log::info('Procesando archivo local TXT', ['filename' => $txtFilePath]);
                    $this->processFile($txtFilePath);
                }
            }
    
            // Liberar memoria después de cada lote
            gc_collect_cycles();
         //   Log::info('Processed batch of local files', ['memory_usage' => memory_get_usage()]);
        }
    }
    


    private function processFile($filePath)
    {
       // Log::info('Entrando a Procesado de FTP', ['filePath' => $filePath, 'memory_usage' => memory_get_usage()]);
    
        try {
            $errorFound = false;
            $errorMessage = '';
            $fileContents = [];
    
            // Leer el archivo completamente
            $file = fopen($filePath, 'r');
            if ($file) {
            //    Log::info('Inicia Procesado de Archivo FTP', ['filePath' => $filePath, 'memory_usage' => memory_get_usage()]);
    
                while (($line = fgets($file)) !== false) {
                    $fileContents[] = $line;
                }
                fclose($file);
            } else {
                Log::error('No se pudo abrir el archivo', ['filePath' => $filePath]);
                return;
            }
    
    
            foreach ($fileContents as $line) {
                $data = explode('|', $line);
    
                if ($data[0] == '501') {
                    
                    // Buscar el ID de la empresa usando la clave
                    $empresa = Empresa::where('clave', $data[4])->first();

    //SINO EXISTE LA EMPRESA

                    if (!$empresa) {
                        $errorMessage = '*1003|Empresa no encontrada para la clave: ' . $data[4] . PHP_EOL;
                    
                        // Verificar si ya existen mensajes 1003 en el archivo
                        $existingMessages = array_filter($fileContents, function($line) {
                            return strpos($line, '*1003|') === 0;
                        });
                    
                        if (empty($existingMessages)) {
                            // Verificar si el mensaje ya existe en el archivo original
                            if (!in_array(trim($errorMessage), array_map('trim', $fileContents))) {
                                $file = fopen($filePath, 'a');
                                if ($file) {
                                    fwrite($file, $errorMessage);
                                    fclose($file);
                                }
                          //      Log::info('Mensaje de error añadido al archivo original', ['filePath' => $filePath, 'errorMessage' => $errorMessage]);
                            }
                        } else {
                         //   Log::info('Mensajes de estado ya existentes, no se agregarán nuevos mensajes', ['filePath' => $filePath]);
                        }
                    
                        $errorFound = true;
                        break; // Romper el bucle y pasar al siguiente archivo
                    }
                    
//CREAR VALIDACION

                    $validatedData = [
                        'factura' => 'required|string|max:255',
                        'empresa_id' => 'required|exists:empresas,id',
                        'fechaReg' => 'required|date',
                        'adjuntoFactura' => 'required|string',
                        'aduana' => 'required|string',
                        'patente' => 'required|string',
                        'Toperacion' => 'required|string',
                    ];
    
                    $validator = Validator::make([
                        'factura' => $data[16],
                        'empresa_id' => $empresa->id,
                        'fechaReg' => Carbon::now(), // Esto debería ser la fecha real del archivo o la fecha de recepción
                        'adjuntoFactura' => '/storage/invoices/' . basename($filePath),
                        'aduana' => '400-TIJ', // Cambiar según el contexto real
                        'patente' => '3875', // Cambiar según el contexto real
                        'Toperacion' => $data[1] == '1' ? 'Importacion' : 'Exportacion', // Determinar la operación basada en el valor
                    ], $validatedData);
    

// SI VALIDACION FALLA   

                    if ($validator->fails()) {
                        $errorMessage = '*1001|Validación fallida para la factura: ' . $data[16] . PHP_EOL;
                    
                        // Verificar si ya existen mensajes 1000, 1001, o 1003
                        $existingMessages = array_filter($fileContents, function($line) {
                            return  strpos($line, '*1001|');
                        });
                    
                        if (empty($existingMessages)) {
                            // Verificar si el mensaje ya existe en el archivo original
                            if (!in_array(trim($errorMessage), array_map('trim', $fileContents))) {
                                $file = fopen($filePath, 'a');
                                if ($file) {
                                    fwrite($file, $errorMessage);
                                    fclose($file);
                                }
                          //      Log::info('Mensaje de error añadido al archivo original', ['filePath' => $filePath, 'errorMessage' => $errorMessage]);
                            }
                        } else {
                          //  Log::info('Mensajes de estado ya existentes, no se agregarán nuevos mensajes', ['filePath' => $filePath]);
                        }
                    
                        $errorFound = true;
                        break; // Romper el bucle y pasar al siguiente archivo si la validación falla
                    }
                    
    // CREAR TRAFICO

                    $trafico = Trafico::create($validator->validated());
                    $trafico->MxDocs = "PENDIENTE";
                    $trafico->statusTrafico = "ABIERTO";
    
                    // Mover el archivo TXT y PDF a la nueva ubicación
                    $nombreOriginalTxt = basename($filePath);
                    $nombreOriginalPdf = str_ireplace('.txt', '.pdf', $nombreOriginalTxt); // str_ireplace es insensible a mayúsculas y minúsculas
    
                    // Mover el archivo TXT
                    $rutaArchivoTxt = Storage::disk('local')->move(
                        'invoices/' . $nombreOriginalTxt,
                        'public/Facturas/FacturaTrafico_' . $trafico->id . '/' . $nombreOriginalTxt
                    );
    
                  //  Log::info('Archivo TXT movido', ['filename' => $nombreOriginalTxt, 'new_location' => $rutaArchivoTxt]);
    
                    // Mover el archivo PDF
                    if (Storage::disk('local')->exists('invoices/' . $nombreOriginalPdf)) {
                        $rutaArchivoPdf = Storage::disk('local')->move(
                            'invoices/' . $nombreOriginalPdf,
                            'public/Facturas/FacturaTrafico_' . $trafico->id . '/' . $nombreOriginalPdf
                        );
    
                      //  Log::info('Archivo PDF movido', ['filename' => $nombreOriginalPdf, 'new_location' => $rutaArchivoPdf]);
                    } else {
                        $rutaArchivoPdf = null;
                        Log::warning('Archivo PDF no encontrado', ['filename' => $nombreOriginalPdf]);
                    }
    
                    $trafico->adjuntoFactura = '/Facturas/FacturaTrafico_' . $trafico->id . '/' . $nombreOriginalPdf;
    
    //REVISION CREAR EN TRUE

                    if (true) { // Supongamos que siempre lleva revisión
                        $revision = new Revisione();
                        $revision->nombreRevisor = 'sinAsignar';
                        $revision->facturaCorrecta = $trafico->fechaReg;
                        $revision->status = 'PENDIENTE';
                        $revision->ubicacionRevision = 'DefaultLocation'; // Cambiar según el contexto real
                        $revision->correccionFactura = 'NO';
                        $revision->save();
    
                        $trafico->revision()->associate($revision);
                        $trafico->Revision = 'PENDIENTE';
                        $trafico->revision_id = $revision->id;
                    } else {
                        $trafico->Revision = 'N/A';
                    }
    
                    $trafico->save();
    
    // Emitir el evento y registrar en el historial

                    event(new TraficoCreated($trafico));
    
    //CREA EL HISTORIAL

                    Historial::create([
                        'trafico_id' => $trafico->id,
                        'nombre' => 'Recepción de Factura',
                        'descripcion' => 'Recepción de Factura se inicia nuevo Proceso de Tráfico.',
                        'hora' => Carbon::now('America/Los_Angeles'),
                        'adjunto' => $trafico->adjuntoFactura,
                    ]);
    
    //CREA EL METODO DE ENVIAR CORREO

                    // Enviar la factura por correo
                    $this->sendFacturaByEmail($trafico);
    
                    // Escribir el mensaje de error o confirmación en el archivo en su nueva ubicación
                    $newFilePath = storage_path('app/public/Facturas/FacturaTrafico_' . $trafico->id . '/' . $nombreOriginalTxt);
                    if (!$errorFound) {
                        $errorMessage = '*1000|Factura Ingresada con éxito' . PHP_EOL;
                    }

                    $newFile = fopen($newFilePath, 'a');

                    if ($newFile) {
                        fwrite($newFile, $errorMessage);
                        fclose($newFile);
                    }
    
                  //  Log::info('Archivo procesado y actualizado', ['filePath' => $newFilePath, 'errorFound' => $errorFound]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar el archivo', ['error' => $e->getMessage(), 'filePath' => $filePath]);
        }
    }
    


    private function sendFacturaByEmail($trafico)
    {
        try {
            // Obtener los correos electrónicos de los usuarios asociados a la empresa
            $userIds = \DB::table('users_empresa')->where('empresa_id', $trafico->empresa_id)->pluck('user_id');
            $emails = User::whereIn('id', $userIds)->pluck('email')->toArray();

            // Dividir los correos electrónicos en lotes de 10 (ajustar según sea necesario)
            $batchSize = 10;
            $chunks = array_chunk($emails, $batchSize);

            foreach ($chunks as $chunk) {
                foreach ($chunk as $email) {
                    Mail::to($email)->send(new FacturaMail($trafico));
                }
                //Log::info('Batch of emails sent', ['trafico_id' => $trafico->id, 'emails' => $chunk]);
            }

            //Log::info('Correos enviados con éxito', ['trafico_id' => $trafico->id, 'emails' => $emails]);
        } catch (\Exception $e) {
            Log::error('Error al enviar los correos', ['error' => $e->getMessage(), 'trafico_id' => $trafico->id]);
        }
    }
}