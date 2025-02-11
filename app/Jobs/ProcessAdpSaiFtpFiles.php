<?php

namespace App\Jobs;



use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProcessAdpSaiFtpFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Ruta en FTP donde se encuentran las carpetas ADP_SAI
    protected $ftpBasePath = '/ADP_SAI'; // Ajusta según tu estructura

    // Ruta en S3 donde se cargarán las carpetas
    protected $s3BasePath = 'Inicio'; // Base path en S3

    // Carpeta en FTP para mover las carpetas sin procesar
    protected $ftpUnprocessedPath = '/ADP_SAI/SIN_PROCESAR';

    // Carpeta en FTP para mover las carpetas vacías
    protected $ftpEmptyPath = '/ADP_SAI/CARPETAS_VACIAS';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Puedes recibir parámetros si es necesario
    }

    /**
     * Execute el job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $ftpDisk = Storage::disk('ftp');
            $s3Disk = Storage::disk('s3');

            // =========================================================================================
            // CÓDIGO ORIGINAL (comentado):
            //
            // $currentYear = Carbon::now()->year;
            // $folders = $ftpDisk->directories($this->ftpBasePath);
            //
            // Se reemplaza para procesar EXCLUSIVAMENTE la carpeta 2024:
            // =========================================================================================

            $yearFolderPath = "{$this->ftpBasePath}/2024"; 
            $folders = $ftpDisk->directories($yearFolderPath);

            foreach ($folders as $folder) {
                $folderName = basename($folder);

                // Ignorar las carpetas "POR PROCESAR", "PROCESADO", "SIN_PROCESAR" y "CARPETAS_VACIAS"
                if (in_array($folderName, ['POR PROCESAR', 'PROCESADO', 'SIN_PROCESAR', 'CARPETAS_VACIAS'])) {
                   // Log::info("Carpeta ignorada: {$folderName}");
                    continue;
                }

                // Comprobar si la carpeta está vacía
                $filesInFolder = $ftpDisk->allFiles($folder);
                if (empty($filesInFolder)) {
                  //  Log::info("Carpeta vacía encontrada: {$folderName}");

                    // Mover la carpeta vacía a 'CARPETAS_VACIAS'
                    $this->moveFtpFolderToEmpty($ftpDisk, $folder);
                    continue;
                }

                // =========================================================================================
                // CÓDIGO ORIGINAL (comentado):
                //
                // $s3FolderPathBase = "{$this->s3BasePath}/{$currentYear}/{$folderName}";
                //
                // Se reemplaza por ruta fija con /2024:
                // =========================================================================================

                $s3FolderPathBase = "{$this->s3BasePath}/2024/{$folderName}";
                $uniqueS3FolderPath = $this->getUniqueFolderNameS3($s3FolderPathBase);

                // Transferir archivos directamente desde FTP a S3
                try {
                    $this->transferFilesFromFtpToS3($ftpDisk, $folder, $uniqueS3FolderPath);
                } catch (\Exception $e) {
                  //  Log::error("Error al transferir la carpeta '{$folderName}': " . $e->getMessage());

                    // Mover la carpeta no procesada a 'SIN_PROCESAR' en FTP
                    $this->moveFtpFolderToUnprocessed($ftpDisk, $folder);

                    // Continuar con la siguiente carpeta sin lanzar la excepción
                    continue;
                }

                // Mover la carpeta en FTP a 'PROCESADO'
                $this->moveFtpFolderToProcessed($ftpDisk, $folder);
            }

         //   Log::info('Proceso ADP SAI FTP (carpeta 2024) completado exitosamente.');

        } catch (\Exception $e) {
          //  Log::error('Error en el Job ProcessAdpSaiFtpFiles: ' . $e->getMessage());
            // Opcional: puedes re-lanzar la excepción para que el Job se reintente
            // throw $e;
        }
    }

    /**
     * Obtener un nombre único para la carpeta en S3 si ya existe.
     *
     * @param string $s3PathBase
     * @return string
     */
    private function getUniqueFolderNameS3($s3PathBase)
    {
        $finalPath = $s3PathBase;
        $counter = 1;

        while (Storage::disk('s3')->exists(rtrim($finalPath, '/') . '/')) {
            $finalPath = rtrim($s3PathBase, '/') . " ({$counter})";
            $counter++;
        }

        return $finalPath;
    }

    /**
     * Transferir archivos desde FTP a S3 utilizando streams.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $ftpDisk
     * @param string $ftpFolderPath
     * @param string $s3FolderPath
     * @return void
     */
    private function transferFilesFromFtpToS3($ftpDisk, $ftpFolderPath, $s3FolderPath)
    {
        // Obtener todos los archivos dentro de la carpeta FTP de manera recursiva
        $files = $ftpDisk->allFiles($ftpFolderPath);

        foreach ($files as $file) {
            $relativePath = Str::after($file, $ftpFolderPath . '/');
            $s3FilePath = "{$s3FolderPath}/{$relativePath}";

            // Leer el stream del archivo desde FTP
            $ftpStream = $ftpDisk->readStream($file);
            if ($ftpStream === false) {
             //   Log::error("No se pudo leer el archivo desde FTP: {$file}");
                continue;
            }

            // Subir el stream directamente a S3
            $s3Upload = Storage::disk('s3')->put($s3FilePath, $ftpStream);

            if ($s3Upload) {
               // Log::info("Archivo subido a S3: {$s3FilePath}");
                // Opcional: Eliminar el archivo del FTP después de subirlo
                // $ftpDisk->delete($file);
            } else {
               // Log::error("Error al subir el archivo a S3: {$s3FilePath}");
            }

            fclose($ftpStream);
        }

     //   Log::info("Carpeta transferida a S3: {$s3FolderPath}");
    }

    /**
     * Mover una carpeta en FTP a 'SIN_PROCESAR'.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $ftpDisk
     * @param string $ftpFolderPath
     * @return void
     */
    private function moveFtpFolderToUnprocessed($ftpDisk, $ftpFolderPath)
    {
        $folderName = basename($ftpFolderPath);
        $unprocessedFolderPath = "{$this->ftpUnprocessedPath}/{$folderName}";

        // Mover la carpeta a 'SIN_PROCESAR'
        $ftpDisk->move($ftpFolderPath, $unprocessedFolderPath);

      //  Log::info("Carpeta movida a: {$unprocessedFolderPath}");
    }

    /**
     * Mover una carpeta en FTP a 'PROCESADO'.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $ftpDisk
     * @param string $ftpFolderPath
     * @return void
     */
    private function moveFtpFolderToProcessed($ftpDisk, $ftpFolderPath)
    {
        $processedFolderPath = "{$this->ftpBasePath}/PROCESADO/" . basename($ftpFolderPath);

        // Mover la carpeta a 'PROCESADO'
        $ftpDisk->move($ftpFolderPath, $processedFolderPath);

      //  Log::info("Carpeta movida a: {$processedFolderPath}");
    }

    /**
     * Mover una carpeta en FTP a 'CARPETAS_VACIAS'.
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $ftpDisk
     * @param string $ftpFolderPath
     * @return void
     */
    private function moveFtpFolderToEmpty($ftpDisk, $ftpFolderPath)
    {
        $folderName = basename($ftpFolderPath);
        $emptyFolderPath = "{$this->ftpEmptyPath}/{$folderName}";

        // Verificar si la carpeta de destino existe (por si acaso)
        if (!$ftpDisk->exists($this->ftpEmptyPath)) {
            $ftpDisk->makeDirectory($this->ftpEmptyPath);
        }

        // Mover la carpeta a 'CARPETAS_VACIAS'
        $ftpDisk->move($ftpFolderPath, $emptyFolderPath);

      //  Log::info("Carpeta vacía movida a: {$emptyFolderPath}");
    }
}
