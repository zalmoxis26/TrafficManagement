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
use App\Events\FacturaUpdated;
use Illuminate\Support\Facades\Mail;
use App\Models\Historial;
use App\Mail\FacturaMail;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Anexo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessFtpFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Tiempo en minutos antes de mandar huérfanos a /invoices/orphans
    private const ORPHAN_TTL_MINUTOS = 10;
    private const DELETE_ORPHAN_TTL_MINUTOS = 300;

    public function __construct()
    {
        //
    }

    public function handle(): void
{
    Log::info('ProcessFtpFiles: INICIO handle()');

    try {
        $this->descargarArchivosFtp();
        $this->procesarArchivosLocales();
        $this->limpiarOrphansViejos();      // Limpia basura vieja en invoices/orphans/

        Log::info('ProcessFtpFiles: FIN handle() sin excepciones');
    } catch (\Throwable $e) {
        Log::error('Error general en ProcessFtpFiles', [
            'error'        => $e->getMessage(),
            'trace'        => $e->getTraceAsString(),
            'memory_usage' => memory_get_usage(),
        ]);

        // MUY IMPORTANTE: relanzar para que el job quede como "failed"
        throw $e;
    }
}


    /**
     * 1) Descargar .txt/.pdf del FTP a invoices/ y mover SIEMPRE fuera del root.
     */
  



private function descargarArchivosFtp(): void
{
    Log::info('FTP DEBUG: entrando a descargarArchivosFtp()');

    $ftp   = Storage::disk('ftp');
    $local = Storage::disk('local');

    // 1) Intentar listar archivos en root del FTP usando listContents (igual que en tu controller)
    try {
        Log::info('FTP DEBUG: intentando listContents("/")');
        $contents = $ftp->listContents('/', false);

        Log::info('FTP DEBUG: listContents("/") OK', [
            'total' => count($contents),
            // Ojo: esto puede ser muy largo, pero útil 1–2 veces para depurar
            'lista' => $contents,
        ]);

        // Filtramos solo archivos y armamos un arreglo de rutas tipo string, como antes
        $archivos = [];
        foreach ($contents as $item) {
            if (($item['type'] ?? null) === 'file') {
                // path es el que usa Storage internamente, ej: "FACT001.TXT"
                $archivos[] = $item['path'];
            }
        }

    } catch (\Throwable $e) {
        Log::error('FTP DEBUG: error al listar archivos en root con listContents("/")', [
            'error' => $e->getMessage(),
        ]);
        return;
    }

    if (empty($archivos)) {
        Log::info('FTP DEBUG: no hay archivos en root para procesar (después de listContents)');
        return;
    }

    // 2) Asegurar carpetas en FTP (si ya existen, no pasa nada)
    try {
        $ftp->makeDirectory('procesados');
        $ftp->makeDirectory('other');
    } catch (\Throwable $e) {
        Log::warning('FTP DEBUG: no se pudieron asegurar directorios procesados/other', [
            'error' => $e->getMessage(),
        ]);
    }

    $batchSize = 50;

    foreach (array_chunk($archivos, $batchSize) as $lote) {
        foreach ($lote as $rutaFtp) {
            $nombre    = basename($rutaFtp);
            $ext       = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            $rutaLocal = 'invoices/' . $nombre;

            Log::info('FTP DEBUG: procesando archivo', [
                'ruta_ftp'   => $rutaFtp,
                'nombre'     => $nombre,
                'ext'        => $ext,
                'ruta_local' => $rutaLocal,
            ]);

            /**
             * 1) Archivos NO válidos (.txt/.pdf)
             * Se mandan directo a /other para que no estorben.
             */
            if (!in_array($ext, ['txt', 'pdf'])) {
                $destinoOther = 'other/' . $nombre;

                try {
                    $okMove = $ftp->move($rutaFtp, $destinoOther);

                    if ($okMove) {
                        Log::info('FTP DEBUG: archivo no válido movido a /other', [
                            'desde' => $rutaFtp,
                            'hacia' => $destinoOther,
                        ]);
                    } else {
                        Log::warning('FTP DEBUG: move() false al mover archivo no válido a /other, probando fallback', [
                            'desde' => $rutaFtp,
                            'hacia' => $destinoOther,
                        ]);

                        $stream = $ftp->readStream($rutaFtp);
                        if ($stream) {
                            $write = $ftp->writeStream($destinoOther, $stream);
                            if (is_resource($stream)) {
                                fclose($stream);
                            }

                            if ($write) {
                                $ftp->delete($rutaFtp);
                                Log::info('FTP DEBUG: archivo no válido copiado a /other y origen eliminado (fallback)', [
                                    'desde' => $rutaFtp,
                                    'hacia' => $destinoOther,
                                ]);
                            } else {
                                Log::error('FTP DEBUG: fallo writeStream en fallback /other, archivo queda en root', [
                                    'desde' => $rutaFtp,
                                    'hacia' => $destinoOther,
                                ]);
                            }
                        } else {
                            Log::error('FTP DEBUG: fallo readStream en fallback /other, archivo queda en root', [
                                'desde' => $rutaFtp,
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('FTP DEBUG: excepción al mover archivo no válido a /other', [
                        'desde' => $rutaFtp,
                        'hacia' => $destinoOther,
                        'error' => $e->getMessage(),
                    ]);
                }

                continue; // siguiente archivo
            }

            /**
             * 2) Archivos VÁLIDOS (.txt/.pdf)
             * Siempre se descargan a /invoices (sobrescribiendo).
             */

            // Abrir stream desde FTP
            $stream = null;
            try {
                $stream = $ftp->readStream($rutaFtp);
            } catch (\Throwable $e) {
                Log::error('FTP DEBUG: excepción en readStream para archivo válido', [
                    'ruta_ftp' => $rutaFtp,
                    'error'    => $e->getMessage(),
                ]);
            }

            if ($stream === false || $stream === null) {
                Log::error('FTP DEBUG: no se pudo abrir stream para archivo válido', [
                    'ruta_ftp' => $rutaFtp,
                ]);
                continue;
            }

            // Asegurar carpeta local invoices
            $local->makeDirectory('invoices');

            // Si ya existe en invoices, eliminar para sobrescribir limpio
            if ($local->exists($rutaLocal)) {
                Log::info('FTP DEBUG: eliminando archivo previo en invoices para sobrescribir', [
                    'ruta_local' => $rutaLocal,
                ]);
                $local->delete($rutaLocal);
            }

            // Escribir archivo en invoices usando streaming
            $okWrite = $local->writeStream($rutaLocal, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }

            if (!$okWrite) {
                Log::error('FTP DEBUG: fallo al escribir archivo en invoices', [
                    'ruta_local' => $rutaLocal,
                    'ruta_ftp'   => $rutaFtp,
                ]);
                continue;
            }

            Log::info('FTP DEBUG: archivo descargado correctamente a invoices', [
                'ruta_local' => $rutaLocal,
                'ruta_ftp'   => $rutaFtp,
            ]);

            /**
             * 3) Mover SIEMPRE el original del FTP a /procesados
             */
            $destinoProc = 'procesados/' . $nombre;

            try {
                $okMove = $ftp->move($rutaFtp, $destinoProc);

                if ($okMove) {
                    Log::info('FTP DEBUG: archivo movido a /procesados después de descargar', [
                        'desde' => $rutaFtp,
                        'hacia' => $destinoProc,
                    ]);
                } else {
                    Log::warning('FTP DEBUG: move() retornó false al mover archivo procesado, revisando estado', [
                        'desde' => $rutaFtp,
                        'hacia' => $destinoProc,
                    ]);

                    $destinoExiste = false;
                    $origenExiste  = false;

                    try {
                        $destinoExiste = $ftp->exists($destinoProc);
                    } catch (\Throwable $e) {}

                    try {
                        $origenExiste = $ftp->exists($rutaFtp);
                    } catch (\Throwable $e) {}

                    if ($destinoExiste && $origenExiste) {
                        $ftp->delete($rutaFtp);
                        Log::info('FTP DEBUG: archivo ya existía en /procesados, se elimina el original en root', [
                            'desde' => $rutaFtp,
                            'hacia' => $destinoProc,
                        ]);
                    } elseif ($origenExiste && !$destinoExiste) {
                        $fallbackStream = $ftp->readStream($rutaFtp);
                        if ($fallbackStream) {
                            $write = $ftp->writeStream($destinoProc, $fallbackStream);
                            if (is_resource($fallbackStream)) {
                                fclose($fallbackStream);
                            }

                            if ($write) {
                                $ftp->delete($rutaFtp);
                                Log::info('FTP DEBUG: archivo copiado a /procesados y origen eliminado (fallback)', [
                                    'desde' => $rutaFtp,
                                    'hacia' => $destinoProc,
                                ]);
                            } else {
                                Log::error('FTP DEBUG: fallo writeStream en fallback /procesados, archivo queda en root', [
                                    'desde' => $rutaFtp,
                                    'hacia' => $destinoProc,
                                ]);
                            }
                        } else {
                            Log::error('FTP DEBUG: fallo readStream en fallback /procesados, archivo queda en root', [
                                'desde' => $rutaFtp,
                            ]);
                        }
                    } else {
                        Log::warning('FTP DEBUG: estado inconsistente tras fallo de move(), revisar manualmente', [
                            'desde'          => $rutaFtp,
                            'hacia'          => $destinoProc,
                            'origen_existe'  => $origenExiste,
                            'destino_existe' => $destinoExiste,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('FTP DEBUG: excepción al mover archivo a /procesados', [
                    'desde' => $rutaFtp,
                    'hacia' => $destinoProc,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        gc_collect_cycles();
    }

    Log::info('FTP DEBUG: descarga y movimientos completados para todos los archivos del root');
}




    /**
     * 2) Procesar archivos en invoices/: sólo pares TXT+PDF.
     */

    private function procesarArchivosLocales(): void
    {
        $local   = Storage::disk('local');
        $archivos = $local->files('invoices');

        if (empty($archivos)) {
            return;
        }

        $pares = $this->construirParesArchivos($archivos);

        // Mover huérfanos viejos a /invoices/orphans
        $this->moverHuerfanosAntiguos($pares);

        $batchSize = 50;

        foreach (array_chunk($pares, $batchSize, true) as $lote) {
            foreach ($lote as $base => $paths) {
                if (!isset($paths['txt'], $paths['pdf'])) {
                    continue;
                }

                $this->procesarParArchivos($paths['txt'], $paths['pdf']);
            }

            gc_collect_cycles();
        }
    }




    /**
     * Construir arreglo [base => ['txt' => ruta, 'pdf' => ruta]]
     */
    private function construirParesArchivos(array $archivos): array
    {
        $pares = [];

        foreach ($archivos as $ruta) {
            $ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
            $base = strtolower(pathinfo($ruta, PATHINFO_FILENAME));

            if (!in_array($ext, ['txt', 'pdf'])) {
                continue;
            }

            if (!isset($pares[$base])) {
                $pares[$base] = [];
            }

            $pares[$base][$ext] = $ruta;
        }

        return $pares;
    }


    
    /**
     * Mover huérfanos (.txt o .pdf sin su par) viejos a /invoices/orphans
     */
    private function moverHuerfanosAntiguos(array $pares): void
    {
        $disk = Storage::disk('local');
        $ttl  = self::ORPHAN_TTL_MINUTOS;

        foreach ($pares as $base => $paths) {
            $tieneTxt = isset($paths['txt']);
            $tienePdf = isset($paths['pdf']);

            if ($tieneTxt === $tienePdf) {
                // O tiene ambos o ninguno -> no es huérfano claro
                continue;
            }

            $rutaHuérfano = $tieneTxt ? $paths['txt'] : $paths['pdf'];
            $fullPath     = storage_path('app/' . $rutaHuérfano);

            if (!file_exists($fullPath)) {
                continue;
            }

            $edadMin = (time() - filemtime($fullPath)) / 60;

            if ($edadMin >= $ttl) {
                $destDir  = 'invoices/orphans/';
                $destPath = $destDir . basename($rutaHuérfano);

                if ($disk->exists($rutaHuérfano) && !$disk->exists($destPath)) {
                    $disk->makeDirectory($destDir);
                    $disk->move($rutaHuérfano, $destPath);
                }
            }
        }
    }


        /**
     * Mueve el par TXT/PDF a una carpeta de error (invoices/error/).
     * Si hay más de 100 archivos en esa carpeta, los elimina todos antes.
     */
    private function moverParAError(string $rutaTxtRel, string $rutaPdfRel): void
    {
        $disk    = Storage::disk('local');
        $errorDir = 'invoices/error/';

        // Aseguramos carpeta
        $disk->makeDirectory($errorDir);

        // Si ya hay más de 100 archivos en la carpeta, limpiamos todo
        $archivosError = $disk->files($errorDir);
        if (count($archivosError) > 100) {
            foreach ($archivosError as $file) {
                $disk->delete($file);
            }
        }

        // Mover TXT si existe
        if ($disk->exists($rutaTxtRel)) {
            $disk->move($rutaTxtRel, $errorDir . basename($rutaTxtRel));
        }

        // Mover PDF si existe
        if ($disk->exists($rutaPdfRel)) {
            $disk->move($rutaPdfRel, $errorDir . basename($rutaPdfRel));
        }
    }





    /**
 * 3) Procesar un par TXT + PDF: crea o actualiza Trafico y mueve archivos.
 */
private function procesarParArchivos(string $rutaTxtRel, string $rutaPdfRel): void
{
    $fullTxtPath = storage_path('app/' . $rutaTxtRel);

    if (!file_exists($fullTxtPath)) {
        /*Log::error('TXT no encontrado al procesar par', ['txt' => $rutaTxtRel]);*/
        return;
    }

    $lineas = file($fullTxtPath, FILE_IGNORE_NEW_LINES);
    if ($lineas === false) {
        /*Log::error('No se pudo leer TXT', ['txt' => $rutaTxtRel]);*/
        return;
    }

    $flags    = $this->detectarEstatus($lineas);
    $datos501 = $this->parsearLinea501($lineas);

    if (!$datos501) {
        $this->agregarEstatusSiFalta($fullTxtPath, $lineas, '*1001|Formato 501 inválido o incompleto');
        return;
    }

    [$factura, $clave, $clavePed, $tipoOperacion] = $datos501;

    // NUEVO: usamos resolverEmpresaDetallada para distinguir 1003 vs 1004
    [$empresa, $codigoErrorEmpresa] = $this->resolverEmpresaDetallada($clave, $factura);

    if (!$empresa) {
        if ($codigoErrorEmpresa === 'AMBIGUA') {
            // 1004: hay más de una empresa con la misma clave base y SIN subfijo
            $this->agregarEstatusSiFalta(
                $fullTxtPath,
                $lineas,
                '*1004|Más de una empresa encontrada para la clave sin subfijo: ' . $clave
            );
        } else {
            // 1003 clásico: no se encontró empresa
            $this->agregarEstatusSiFalta(
                $fullTxtPath,
                $lineas,
                '*1003|Empresa no encontrada para la clave: ' . $clave
            );
        }

         // NUEVO: mover TXT + PDF a carpeta de error
        $this->moverParAError($rutaTxtRel, $rutaPdfRel);

        return;
    }


    $validador = Validator::make(
        [
            'factura'        => $factura,
            'empresa_id'     => $empresa->id,
            'fechaReg'       => Carbon::now('America/Los_Angeles'),
            'adjuntoFactura' => '/storage/' . $rutaTxtRel, // provisional; se reasigna al final
            'aduana'         => config('trafico.aduana_default', '400-TIJ'),
            'patente'        => config('trafico.patente_default', '3875'),
            'clavePed'       => $clavePed,
            'Toperacion'     => $tipoOperacion,
        ],
        [
            'factura'        => 'required|string|max:255',
            'empresa_id'     => 'required|exists:empresas,id',
            'fechaReg'       => 'required|date',
            'adjuntoFactura' => 'required|string',
            'aduana'         => 'required|string',
            'patente'        => 'required|string',
            'clavePed'       => 'required|string',
            'Toperacion'     => 'required|string',
        ]
    );

    if ($validador->fails()) {
        $this->agregarEstatusSiFalta(
            $fullTxtPath,
            $lineas,
            '*1001|Validación fallida para la factura: ' . $factura
        );
        return;
    }

    try {
        DB::transaction(function () use (
            $validador,
            $factura,
            $rutaTxtRel,
            $rutaPdfRel,
            $tipoOperacion,
            $clavePed,
            $flags
        ) {
            $data = $validador->validated();
            $data['MxDocs']        = 'PENDIENTE';
            $data['statusTrafico'] = 'ABIERTO';
            $data['Toperacion']    = $tipoOperacion;
            $data['clavePed']      = $clavePed;

            // Ver si ya existe un tráfico con esa factura
            $traficoExistente = Trafico::where('factura', $factura)->first();
            $esActualizacion  = (bool) $traficoExistente;

            if ($esActualizacion) {
                // 1) PRIMERO: convertir lo viejo en Anexos (mueve desde /Facturas/... → /Anexos/...)
                $trafico = $traficoExistente;
                $this->archivarFacturaAnterior($trafico);

                // 2) Actualizar campos SIN pisar adjuntoFactura (se re-asignará al final)
                unset($data['adjuntoFactura']);
                $trafico->fill($data);
                $trafico->save();
            } else {
                // Creación: se guarda y luego se re-asigna adjuntoFactura al final
                $trafico = Trafico::create($data + ['factura' => $factura]);
            }

            // 3) Mover los NUEVOS archivos desde invoices/ a la carpeta del tráfico
            $nuevoTxtAdjunto = $this->moverDesdeInvoicesAlTrafico($rutaTxtRel, $trafico->id);
            $nuevoPdfAdjunto = $this->moverDesdeInvoicesAlTrafico($rutaPdfRel, $trafico->id);

            // 4) Apuntar adjuntoFactura a la NUEVA versión (prefiere PDF)
            if ($nuevoPdfAdjunto) {
                $trafico->adjuntoFactura = $nuevoPdfAdjunto;
            } elseif ($nuevoTxtAdjunto) {
                $trafico->adjuntoFactura = $nuevoTxtAdjunto;
            }

            // 5) Revisiones
            $this->sincronizarRevision($trafico);
            $trafico->save();

            // 6) Historial (distinguir creación / actualización)
            $this->asegurarHistorialRecepcion($trafico, $esActualizacion);

            // 7) Eventos y correo
            if ($esActualizacion) {
                event(new FacturaUpdated($trafico));
            } else {
                $this->enviarFacturaPorCorreoPrueba($trafico);
                event(new TraficoCreated($trafico));
            }

            // 8) *1000 en el TXT final si no hay códigos previos
            $this->asegurarLineaExito(
                $trafico,
                basename($rutaTxtRel),
                $flags
            );
        });
    } catch (\Throwable $e) {
        /*Log::error('Error en transacción al procesar par', [
            'txt'   => $rutaTxtRel,
            'pdf'   => $rutaPdfRel,
            'error' => $e->getMessage(),
        ]);*/
    }
}


    /**
     * Detecta si ya hay líneas *1000, *1001, *1003.
     */
       /**
     * Detecta si el TXT ya contiene códigos de estatus (*1000, *1001, *1003, *1004)
     * revisando desde la ÚLTIMA línea hacia arriba para mayor eficiencia.
     *
     * Beneficios del enfoque:
     *  - Los estatus siempre se escriben al final del TXT, así que casi siempre
     *    los encontramos en las últimas 4–6 líneas.
     *  - Evitamos recorrer todo el archivo (que a veces viene muy largo).
     *  - Cortamos la búsqueda temprano si ya encontramos todos los códigos.
     *
     * Devuelve:
     *  [
     *      '1000' => true/false,
     *      '1001' => true/false,
     *      '1003' => true/false,
     *      '1004' => true/false,
     *  ]
     */
    private function detectarEstatus(array $lineas): array
    {
        $flags = [
            '1000' => false,
            '1001' => false,
            '1003' => false,
            '1004' => false,
        ];

        $total = count($lineas);

        // Recorremos el TXT desde la última línea hacia arriba
        for ($i = $total - 1; $i >= 0; $i--) {

            $linea = $lineas[$i];

            // Revisamos los códigos
            if (str_starts_with($linea, '*1000|')) $flags['1000'] = true;
            if (str_starts_with($linea, '*1001|')) $flags['1001'] = true;
            if (str_starts_with($linea, '*1003|')) $flags['1003'] = true;
            if (str_starts_with($linea, '*1004|')) $flags['1004'] = true;

            // Si ya encontramos todos, cortamos aquí
            if ($flags['1000'] && $flags['1001'] && $flags['1003'] && $flags['1004']) {
                break;
            }

            // OPTIMIZACIÓN EXTRA (opcional):
            // Si llegamos a la línea 501, dejamos de buscar porque
            // todo lo de arriba son datos del pedimento, NO estatus.
            if (str_starts_with($linea, '501|')) {
                break;
            }
        }

        return $flags;
    }


    /**
     * Extrae factura, clave, clavePed, tipoOperacion desde línea 501.
     */
    private function parsearLinea501(array $lineas): ?array
    {
        foreach ($lineas as $linea) {
            $data = explode('|', $linea);

            if (($data[0] ?? null) !== '501') {
                continue;
            }

            if (count($data) <= 16) {
                continue;
            }

            $clave         = $data[4] ?? null;
            $factura       = $data[16] ?? null;
            $clavePed      = $data[2] ?? null;
            $tipoOperacion = ($data[1] ?? null) === '1'
                ? 'Importacion'
                : 'Exportacion';

            if ($clave && $factura && $clavePed) {
                return [$factura, $clave, $clavePed, $tipoOperacion];
            }
        }

        return null;
    }

        /**
         * Resolver Empresa a partir de la clave Darwin (ej. SAI013PRI)
         * y el número de factura.
         *
         * Reglas PARA IDENTIFICAR EMPRESA CON LA CLAVE DEL TXT:
         * 1) Se separa SAI + 3 dígitos = clave base. (si cambia dara error inforamrle al cliente)
         * 2) Si NO hay sufijo y hay +1 empresa con esa clave → ERROR.
         * 3) Si hay sufijo, se busca match exacto contra prefijoFactura (que puede tener comas).
         * 4) sino hay subfijo y hay mas de 2 empresas con la misma clave base → ERROR.
         * 
         * Códigos de error:
         *  - null     → todo bien
         *  - 'NO_ENCONTRADA' → no se encontró empresa para la clave/sufijo
         *  - 'AMBIGUA'       → hay más de una empresa con la misma clave base y SIN subfijo
         */
        

 
    private function resolverEmpresaDetallada(string $claveDesdeTxt, string $numeroFactura): array
    {
        $claveDesdeTxt = trim(strtoupper($claveDesdeTxt));

        // Extraer clave base (SAI + 3 dígitos) y sufijo (opcional)
        if (!preg_match('/^([A-Z]{3}\d{3})([A-Z]*)$/', $claveDesdeTxt, $m)) {
            // Formato raro → tratamos como "no encontrada"
            return [null, 'NO_ENCONTRADA'];
        }

        $claveBase = $m[1];           // SAI013
        $sufijo    = trim($m[2] ?? ''); // PRI (o vacío)

        // Buscar SOLO empresas con esa clave base → rendimiento
        $empresas = Empresa::where('clave', $claveBase)->get();
        if ($empresas->isEmpty()) {
            return [null, 'NO_ENCONTRADA'];
        }

        /**
         * CASO A: NO hay subfijo (ej. SAI079, SAI013 sin PRI)
         *
         * Regla que tú pediste:
         *  - Si hay MÁS de una empresa con esa clave → error 1004 (AMBIGUA)
         *  - Si hay solo UNA → se acepta
         */
        if ($sufijo === '') {
            if ($empresas->count() > 1) {
                // Hay más de una empresa con esa clave y sin subfijo → 1004
                return [null, 'AMBIGUA'];
            }

            // Solo una empresa con esa clave → OK
            return [$empresas->first(), null];
        }

        /**
         * CASO B: Sí hay subfijo (PRI, JL, OUT, NH, SU, etc.)
         * Matcheamos contra prefijoFactura, que puede ser "PRI" o "NH,SU".
         */
        foreach ($empresas as $empresa) {
            $prefijos = $this->tokensPrefijo($empresa->prefijoFactura); // maneja "NH,SU"
            foreach ($prefijos as $token) {
                if ($token === $sufijo) {
                    return [$empresa, null]; // Match exacto por clave base + sufijo
                }
            }
        }

        // No se encontró empresa que matchee sufijo → 1003 (NO_ENCONTRADA)
        return [null, 'NO_ENCONTRADA'];
    }

    /**
     * Convierte "NH,SU" → ['NH','SU'], "PRI" → ['PRI'], null → []
     */
    private function tokensPrefijo(?string $value): array
    {
        if (!$value) return [];

        return array_filter(
            array_map('trim', explode(',', strtoupper($value))),
            fn ($v) => $v !== ''
        );
    }





    /**
     * Agregar línea de estatus si no existe ya una igual o del mismo código.
     */
    private function agregarEstatusSiFalta(string $fullTxtPath, array $lineas, string $estatus): void
    {
        $flags = $this->detectarEstatus($lineas);
        $codigo = substr($estatus, 1, 4); // 1000 / 1001 / 1003

        if (isset($flags[$codigo]) && $flags[$codigo]) {
            return;
        }

        foreach ($lineas as $linea) {
            if (trim($linea) === trim($estatus)) {
                return;
            }
        }

        if ($fp = fopen($fullTxtPath, 'a')) {
            fwrite($fp, PHP_EOL . $estatus . PHP_EOL);
            fclose($fp);
        }
    }

    /**
     * Mueve todos los archivos actuales del tráfico a Historial/FacturaSustituidaTrafico_{id}
     * antes de colocar la nueva versión.
     */

/**
 * Convierte TODO lo viejo del tráfico (PDF, TXT, etc.) en ANEXOS:
 * - Mueve desde /public/Facturas/FacturaTrafico_{id}/ a /public/Anexos/AnexoTrafico_{id}/
 * - Genera nombre de archivo ÚNICO
 * - Crea un Anexo por archivo con asunto: FACTURA ANTERIOR, SUSTITUIDA EL: "dd-mm-YYYY"
 */

    private function archivarFacturaAnterior(Trafico $trafico): void
{
    $disk = Storage::disk('local');

    // Carpeta donde están los archivos viejos del tráfico
    $carpetaVieja = 'public/Facturas/FacturaTrafico_' . $trafico->id . '/';
    if (!$disk->exists($carpetaVieja)) {
        /*Log::info('ARCHIVAR→ANEXO: carpeta del tráfico no existe', [
            'trafico_id' => $trafico->id,
            'carpeta'    => $carpetaVieja,
        ]);*/
        return;
    }

    // Destino: carpeta de anexos del tráfico
    $destDir = 'public/Anexos/AnexoTrafico_' . $trafico->id . '/';
    $disk->makeDirectory($destDir);

    $fechaHuman = Carbon::now('America/Los_Angeles')->format('d-m-Y');
    $asunto     = 'FACTURA ANTERIOR, SUSTITUIDA EL: "' . $fechaHuman . '"';

    $archivos = $disk->files($carpetaVieja);
    $creados  = 0;

    foreach ($archivos as $origen) {
        $ext = strtolower(pathinfo($origen, PATHINFO_EXTENSION));

        // Solo procesar PDFs
        if ($ext !== 'pdf') {
            continue;
        }

        // Obtener nombre original sin extensión
        $nombreOriginal = pathinfo($origen, PATHINFO_FILENAME);

        // Formato deseado: DIA MES AÑO 2 DIGITOS + MINUTOS + SEGUNDOS
        $fechaCorta = Carbon::now('America/Los_Angeles')->format('dmy_His');
        // Extraer solo últimos 4 dígitos de minutos y segundos
        $fechaCompacta = substr($fechaCorta, 0, 6) . '_' . substr($fechaCorta, -4);

        // Resultado: JBA2020__121125_2335.pdf
        $destNombre = "{$nombreOriginal}__{$fechaCompacta}.pdf";
        $destino    = $destDir . $destNombre;

        // Evitar colisión
        $i = 1;
        while ($disk->exists($destino)) {
            $destNombre = "{$nombreOriginal}__{$fechaCompacta}_{$i}.pdf";
            $destino    = $destDir . $destNombre;
            $i++;
        }

        try {
            // Mover archivo
            $disk->move($origen, $destino);

            // Guardar en BD
            $rutaDb = str_replace('public/', '', $destino);

            $anexo = new Anexo();
            $anexo->descripcion = 'Auto-generado: factura anterior sustituida el ' . $fechaHuman;
            $anexo->archivo     = $rutaDb;
            $anexo->asunto      = $asunto;
            $anexo->save();

            $trafico->anexos()->attach($anexo->id);
            $creados++;
        } catch (\Throwable $e) {
            /*Log::error('ARCHIVAR→ANEXO: error moviendo/creando anexo', [
                'trafico_id' => $trafico->id,
                'error'      => $e->getMessage(),
                'desde'      => $origen,
                'hacia'      => $destino,
            ]);*/
        }
    }

    /*Log::info('ARCHIVAR→ANEXO: anexos creados', [
        'trafico_id' => $trafico->id,
        'destDir'    => $destDir,
        'creados'    => $creados,
    ]);*/
}






    /**
     * Mover archivo desde invoices/ a carpeta del tráfico.
     * Devuelve la ruta pública (/Facturas/FacturaTrafico_ID/archivo.ext) o null.
     */
    private function moverDesdeInvoicesAlTrafico(string $rutaRel, int $traficoId): ?string
    {
        $disk = Storage::disk('local');

        if (!$disk->exists($rutaRel)) {
            return null;
        }

        $nombre = basename($rutaRel);
        $destDir = 'public/Facturas/FacturaTrafico_' . $traficoId . '/';
        $disk->makeDirectory($destDir);

        $destino = $destDir . $nombre;

        if ($disk->exists($destino)) {
            $disk->delete($destino);
        }

        $disk->move($rutaRel, $destino);

        return '/Facturas/FacturaTrafico_' . $traficoId . '/' . $nombre;
    }

    /**
     * Crear / asegurar relación de revisión según reglas.
     */
    private function sincronizarRevision(Trafico $trafico): void
    {
        $excepcionesOperaciones = ['Exportacion'];
        $excepcionesClavePed    = ['A3', 'V1', 'F3'];

        $llevaRevision = !in_array($trafico->Toperacion, $excepcionesOperaciones)
            && !in_array($trafico->clavePed, $excepcionesClavePed);

        if ($llevaRevision) {
            if (!$trafico->revision_id) {
                $revision = new Revisione();
                $revision->nombreRevisor     = 'sinAsignar';
                $revision->facturaCorrecta   = $trafico->fechaReg;
                $revision->status            = 'PENDIENTE';
                $revision->ubicacionRevision = 'DefaultLocation';
                $revision->correccionFactura = 'NO';
                $revision->save();

                $trafico->revision()->associate($revision);
                $trafico->Revision    = 'PENDIENTE';
                $trafico->revision_id = $revision->id;
            }
        } else {
            $trafico->Revision = 'N/A';
        }
    }

    /**
     * Crear historial para recepción o actualización.
     */
    private function asegurarHistorialRecepcion(Trafico $trafico, bool $esActualizacion): void
    {
        if ($esActualizacion) {
            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre'     => 'Actualización de Factura',
                'descripcion'=> 'La factura ha sido actualizada o reimportada correctamente.',
                'hora'       => Carbon::now('America/Los_Angeles'),
                'adjunto'    => $trafico->adjuntoFactura,
            ]);
        } else {
            $existe = Historial::where('trafico_id', $trafico->id)
                ->where('nombre', 'Recepción de Factura')
                ->exists();

            if (!$existe) {
                Historial::create([
                    'trafico_id' => $trafico->id,
                    'nombre'     => 'Recepción de Factura',
                    'descripcion'=> 'Recepción de Factura se inicia nuevo Proceso de Tráfico.',
                    'hora'       => Carbon::now('America/Los_Angeles'),
                    'adjunto'    => $trafico->adjuntoFactura,
                ]);
            }
        }
    }

    /**
     * Asegura que exista línea *1000 en el TXT final si no hay errores previos.
     */
    private function asegurarLineaExito(Trafico $trafico, string $nombreTxt, array $flags): void
    {
        if ($flags['1000'] || $flags['1001'] || $flags['1003'] || $flags['1004']) {
            return;
        }

        $ruta = storage_path(
            'app/public/Facturas/FacturaTrafico_' . $trafico->id . '/' . $nombreTxt
        );

        if (!file_exists($ruta)) {
            return;
        }

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lineas as $linea) {
            if (str_starts_with($linea, '*1000|Factura Ingresada con éxito')) {
                return;
            }
        }

        if ($fp = fopen($ruta, 'a')) {
            fwrite($fp, PHP_EOL . '*1000|Factura Ingresada con éxito' . PHP_EOL);
            fclose($fp);
        }
    }


    /**
     * Limpia la carpeta invoices/orphans/ borrando archivos huérfanos
     * que lleven más de ORPHAN_TTL_MINUTOS (ej. 300 min / 5 horas).
     *
     * Esta función se puede llamar cada hora desde handle().
     */

        private function limpiarOrphansViejos(): void
        {
            $disk     = Storage::disk('local');
            $orphanDir = 'invoices/orphans/';

            // Si no existe la carpeta, no hay nada que hacer
            if (!$disk->exists($orphanDir)) {
                return;
            }

            $archivos = $disk->files($orphanDir);
            if (empty($archivos)) {
                return;
            }

            $ttl = self::DELETE_ORPHAN_TTL_MINUTOS; // 300 minutos = 5 horas
            $ahora = time();

            foreach ($archivos as $rutaRel) {
                $fullPath = storage_path('app/' . $rutaRel);

                if (!file_exists($fullPath)) {
                    continue;
                }

                $edadMin = ($ahora - filemtime($fullPath)) / 60;

                // Si el archivo huérfano lleva más de 5 horas en orphans → lo borramos
                if ($edadMin >= $ttl) {
                    $disk->delete($rutaRel);

                    // Si quieres depuración, descomenta:
                    /*
                    Log::info('ORPHAN: archivo eliminado por antigüedad en invoices/orphans', [
                        'ruta'     => $rutaRel,
                        'edad_min' => $edadMin,
                        'ttl_min'  => $ttl,
                    ]);
                    */
                }
            }
        }




    /**
     * Enviar correo de nueva factura.
     */
   private function enviarFacturaPorCorreo(Trafico $trafico): void
    {
        try {
            // 1) Correos de la empresa (campo emailNotify)
            $emailsEmpresa = [];
            if ($trafico->empresa && !empty($trafico->empresa->emailNotify)) {

                $emailsEmpresa = collect(explode(',', $trafico->empresa->emailNotify))
                    ->map(fn ($email) => trim($email))
                    // Solo correos con formato válido
                    ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
                    ->values()
                    ->toArray();
            }

            // 2) Usuarios con rol "documentador" (Spatie)
            $documentadores = User::role('documentador')->get(['id', 'name', 'email']);
            $emailsDocumentadores = $documentadores
                ->pluck('email')
                ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
                ->values()
                ->toArray();

            // 3) Correos fijos
            $principalEmail  = 'osvaldo@rentasgmp.com';
            $revisionesEmail = 'revisiones@agenciasai.com';

            // 4) TO: principal + empresa (solo válidos y sin duplicado)
            $to = array_values(array_filter(array_unique(array_merge(
                [$principalEmail],
                $emailsEmpresa
            ))));

            // 5) CC: documentadores + revisiones (solo válidos y sin duplicado)
            $cc = array_values(array_filter(array_unique(array_merge(
                $emailsDocumentadores,
                [$revisionesEmail]
            ))));

            // Si de plano no hay nadie en TO, no intentamos enviar
            if (empty($to)) {
                return;
            }

            Mail::to($to)
                ->cc($cc)
                ->queue(new FacturaMail($trafico));

        } catch (\Throwable $e) {
            // Log opcional
            /*
            Log::error('Error al enviar correo de factura', [
                'error'      => $e->getMessage(),
                'trafico_id' => $trafico->id ?? null,
            ]);
            */
        }
    }

    private function enviarFacturaPorCorreoPrueba(Trafico $trafico): void
{
    try {
        Mail::to('osvaldo@rentasgmp.com')
            ->queue(new FacturaMail($trafico));

    } catch (\Throwable $e) {
        // Log opcional
        /*
        Log::error('Error al enviar correo de PRUEBA', [
            'error'      => $e->getMessage(),
            'trafico_id' => $trafico->id ?? null,
        ]);
        */
    }
}



}
