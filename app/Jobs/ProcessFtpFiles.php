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
                    // Encontrar el archivo TXT sin importar mayúsculas o minúsculas
                    $txtFilePath = $this->findFileCaseInsensitive('invoices', $filenameWithoutExtension, 'txt');
                    $pdfFilePath = $this->findFileCaseInsensitive('invoices', $filenameWithoutExtension, 'pdf');
    
                    // Verificación adicional de existencia de archivos
                    if (!$txtFilePath) {
                        Log::error('Archivo TXT no encontrado', ['filePath' => $filenameWithoutExtension . '.txt']);
                        continue;
                    }
    
                    if (!$pdfFilePath) {
                        Log::error('Archivo PDF no encontrado', ['filePath' => $filenameWithoutExtension . '.pdf']);
                        continue;
                    }
    
                    // Procesar el archivo TXT
                    $this->processFile($txtFilePath);
                }
            }
    
            // Liberar memoria después de cada lote
            gc_collect_cycles();
        }
    }
    
    private function findFileCaseInsensitive($directory, $filenameWithoutExtension, $extension)
    {
        $files = Storage::disk('local')->files($directory);
    
        foreach ($files as $file) {
            if (strtolower(pathinfo($file, PATHINFO_FILENAME)) === strtolower($filenameWithoutExtension) &&
                strtolower(pathinfo($file, PATHINFO_EXTENSION)) === strtolower($extension)) {
                return storage_path('app/' . $file);
            }
        }
    
        return false;
    }
    
    private function processFile($filePath)
    {
        Log::info('Entrando a Procesado de Archivo', ['filePath' => $filePath, 'memory_usage' => memory_get_usage()]);
    
        try {
            $errorFound = false;
            $errorMessage = '';
            $fileContents = [];
    
            // Verificar la existencia del archivo antes de abrirlo
            if (!file_exists($filePath)) {
                Log::error('Archivo no encontrado antes de fopen', ['filePath' => $filePath]);
                return;
            }
    
            // Leer el archivo completamente
            $file = fopen($filePath, 'r');
            if ($file) {
                Log::info('Inicia Procesado de Archivo', ['filePath' => $filePath, 'memory_usage' => memory_get_usage()]);
    
                while (($line = fgets($file)) !== false) {
                    $fileContents[] = $line;
                }
                fclose($file);
            } else {
                Log::error('No se pudo abrir el archivo', ['filePath' => $filePath]);
                return;
            }
            // Continuar con el procesamiento del archivo
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