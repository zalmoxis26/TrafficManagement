<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;
use Illuminate\Support\Str; // Para manejar cadenas
use Aws\Exception\AwsException;
use Carbon\Carbon;


class AdpPedimentoSaiAwsController extends Controller
{
    public function index(Request $request)
    {
        $currentPath = $request->input('path', 'Inicio');
        $prefix = rtrim($currentPath, '/') . '/';
    
        // Obtener elementos del storage
        // Usar disco S3
        $carpetas = Storage::disk('s3')->directories($prefix);
        $archivos = Storage::disk('s3')->files($prefix);
    
        // Procesar elementos
        $items = collect($carpetas)->map(function ($directory) {
            return [
                'type' => 'folder',
                'name' => basename($directory),
                'path' => $directory
            ];
        })->merge(collect($archivos)->map(function ($file) use ($prefix) {
            return [
                'type' => 'file',
                'name' => str_replace($prefix, '', $file),
                'path' => $file
            ];
        }))->sortBy('name');
    
        // Paginación simple
        $page = $request->input('page', 1);
        $perPage = 16;
        $paginated = $items->forPage($page, $perPage);
        $combinedCount = $items->count(); // Calcular total de elementos
    
        return $request->ajax() ? 
            response()->json([
                'items' => $paginated->values(),
                'next_page' => $items->count() > ($page * $perPage) ? $page + 1 : null
            ]) : 
            view('expedientePedimento.index', [
                'paginated' => $paginated,
                'currentPath' => $currentPath,
                'parentPath' => $this->getParentPath($currentPath),
                'carpetas' => $carpetas,
                'archivos' => $archivos,
                'combinedCount' => $combinedCount, // Añadir esta línea
                'page' => $page,          // Añadir esta línea
                'perPage' => $perPage     // Añadir esta línea
            ]);
    }




    public function buscar(Request $request)
    {
        // Obtener los parámetros de la solicitud
        $query = $request->input('query', '');
        $currentPath = $request->input('path', 'Inicio'); // Ruta actual, por defecto 'Inicio'
        $prefix = rtrim($currentPath, '/') . '/';
        $extend = $request->input('extend', false); // Nuevo parámetro para extender la búsqueda manualmente
    
        // Si la consulta está vacía, retornar una respuesta vacía con mensaje
        if (empty($query)) {
            return response()->json([
                'items' => [],
                'next_page' => null,
                'mensaje' => 'Por favor, ingresa un término de búsqueda.',
            ]);
        }
    
        // Parámetros de paginación
        $page = (int) $request->input('page', 1);
        $perPage = 16;
        $offset = ($page - 1) * $perPage;
    
        // Inicializar arrays para carpetas y archivos
        $carpetas = [];
        $archivos = [];
    
        try {
            if($extend){
                // Realizar búsqueda recursiva directamente
                $combined = $this->buscarRecursivo($prefix, $query);
                $busquedaExtendida = true;
            } else {
                // Búsqueda no recursiva en la ruta actual
                // Obtener archivos directamente bajo el prefijo actual (no recursivo)
                $files = Storage::disk('s3')->files($prefix);
    
                // Obtener subcarpetas directamente bajo el prefijo actual
                $directories = $this->getDirectories($prefix);
    
                // Procesar carpetas
                foreach ($directories as $carpeta) {
                    $nombreCarpeta = basename($carpeta);
                    if (stripos($nombreCarpeta, $query) !== false) {
                        $carpetas[] = [
                            'type' => 'folder',
                            'name' => $nombreCarpeta,
                            'path' => $carpeta,
                        ];
                    }
                }
    
                // Procesar archivos
                foreach ($files as $filePath) {
                    $nombreArchivo = basename($filePath);
                    if (stripos($nombreArchivo, $query) !== false) {
                        $archivos[] = [
                            'type' => 'file',
                            'name' => $nombreArchivo,
                            'path' => $filePath,
                        ];
                    }
                }
    
                // Combinar carpetas y archivos
                $combined = array_merge($carpetas, $archivos);
                $busquedaExtendida = false;
            }
    
        } catch (\Exception $e) {
            Log::error("Error al realizar la búsqueda en S3: " . $e->getMessage());
            return response()->json([
                'items' => [],
                'next_page' => null,
                'error' => 'Error al acceder a S3.',
            ], 500);
        }
    
        // Si no hay resultados
        if (empty($combined)) {
            // Solo si no se ha extendido la búsqueda
            if(!$extend){
                return response()->json([
                    'items' => [],
                    'next_page' => null,
                    'mensaje' => 'No se encontraron resultados para tu búsqueda en la ruta actual.',
                    'can_extend' => true, // Indica que se puede extender la búsqueda
                ]);
            } else {
                // Si ya se ha extendido y aún no hay resultados
                return response()->json([
                    'items' => [],
                    'next_page' => null,
                    'mensaje' => 'No se encontraron resultados para tu búsqueda incluso al ampliar a subcarpetas.',
                    'can_extend' => false,
                ]);
            }
        }
    
        // Ordenar los resultados: carpetas primero, luego archivos, ambos alfabéticamente
        usort($combined, function($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcmp($a['name'], $b['name']);
            }
            return ($a['type'] === 'folder') ? -1 : 1;
        });
    
        // Convertir el resultado filtrado a un array indexado
        $resultadosFiltrados = array_values($combined);
    
        // Paginación de los resultados
        $totalResultados = count($resultadosFiltrados);
        $paginated = array_slice($resultadosFiltrados, $offset, $perPage);
        $next_page = ($page * $perPage) < $totalResultados ? $page + 1 : null;
    
        // Preparar la respuesta JSON
        $respuesta = [
            'items' => $paginated,
            'next_page' => $next_page,
        ];
    
        if (isset($busquedaExtendida) && $busquedaExtendida) {
            $respuesta['mensaje'] = 'No se encontraron resultados en la ruta actual.';
        }
    
        return response()->json($respuesta);
    }
    
    /**
     * Obtener subcarpetas directamente bajo un prefijo dado.
     *
     * @param string $prefix
     * @return array
     */
    private function getDirectories($prefix)
    {
        try {
            // Obtener todos los contenidos bajo el prefijo actual de manera no recursiva
            $contents = Storage::disk('s3')->listContents($prefix, false);
    
            $directories = [];
    
            foreach ($contents as $object) {
                if ($object['type'] === 'dir') {
                    $directories[] = $object['path'];
                }
            }
    
            return $directories;
    
        } catch (\Exception $e) {
            Log::error("Error al obtener subcarpetas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Realizar una búsqueda recursiva en todas las subcarpetas.
     *
     * @param string $prefix
     * @param string $query
     * @return array
     */
    private function buscarRecursivo($prefix, $query)
    {
        $resultados = [];
    
        try {
            // Obtener todos los contenidos bajo el prefijo actual de manera recursiva
            $allContents = Storage::disk('s3')->listContents($prefix, true);
    
            foreach ($allContents as $object) {
                $nombre = basename($object['path']);
                if (stripos($nombre, $query) !== false) {
                    if ($object['type'] === 'file') {
                        $resultados[] = [
                            'type' => 'file',
                            'name' => $nombre,
                            'path' => $object['path'],
                        ];
                    } elseif ($object['type'] === 'dir') {
                        $resultados[] = [
                            'type' => 'folder',
                            'name' => $nombre,
                            'path' => $object['path'],
                        ];
                    }
                }
            }
    
            return $resultados;
    
        } catch (\Exception $e) {
            Log::error("Error al realizar búsqueda recursiva en S3: " . $e->getMessage());
            return [];
        }
    }
    











    public function crearCarpeta(Request $request)
    {
        $validated = $request->validate([
            'nombreCarpeta' => 'required|string|max:255|regex:/^[^\/\\\\]+$/',
            'currentPath' => 'required|string'
        ]);

        $path = rtrim($validated['currentPath'], '/') . '/' . $validated['nombreCarpeta'];
        
        if (Storage::disk('s3')->exists($path)) {
            return response()->json(['error' => 'Ya existe un elemento con este nombre'], 400);
        }

        Storage::disk('s3')->makeDirectory($path);
        return response()->json(['success' => true]);
    }


    


    public function cargarArchivos(Request $request)
    {
        try {
            // Validación de entrada
            $request->validate([
                'currentPath' => 'required|string',
                'archivos' => 'required_without:carpetasArchivos|array',
                'archivos.*' => 'file|max:102400', // 100MB por archivo
                'carpetasArchivos' => 'required_without:archivos|array'
            ]);

            $currentPath = rtrim($request->currentPath, '/');

            $existingFiles = [];
            $existingFolders = [];
            $filesToUpload = [];
            $foldersToUpload = [];

            // Procesar archivos individuales
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $file) {
                    $fileName = $this->sanitizeFileName($file->getClientOriginalName());
                    $fullPath = $currentPath . '/' . $fileName;

                    // Verificar si el archivo ya existe
                    if (Storage::disk('s3')->exists($fullPath)) {
                        $existingFiles[] = $fileName;
                    } else {
                        $filesToUpload[] = [
                            'path' => $currentPath,
                            'file' => $file,
                            'name' => $fileName
                        ];
                    }
                }
            }

            // Procesar carpetas y archivos dentro de ellas
            if ($request->has('carpetasArchivos')) {
                foreach ($request->carpetasArchivos as $relativePath => $file) {
                    // Determinar si es una carpeta o un archivo
                    if ($this->isFolder($relativePath)) {
                        // Es una carpeta
                        $folderName = rtrim($relativePath, '/');
                        $folderPrefix = $currentPath . '/' . $folderName . '/';

                        // Verificar si la carpeta ya existe
                        if ($this->folderExistsInS3($folderPrefix)) {
                            $existingFolders[] = $folderName;
                        } else {
                            $foldersToUpload[] = $folderPrefix;
                        }
                    } else {
                        // Es un archivo dentro de una carpeta
                        $fullPath = $currentPath . '/' . $relativePath;
                        $fileName = $this->sanitizeFileName(basename($relativePath));
                        $filesToUpload[] = [
                            'path' => $currentPath . '/' . dirname($relativePath),
                            'file' => $file,
                            'name' => $fileName
                        ];
                    }
                }
            }

            // Si hay carpetas existentes, retornar mensaje de error
            if (!empty($existingFolders)) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Las siguientes carpetas ya existen en la ruta de destino: ' . implode(', ', $existingFolders)
                ], 400);
            }

            // Si hay archivos existentes, retornar mensaje de error
            if (!empty($existingFiles)) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Los siguientes archivos ya existen en la ruta de destino: ' . implode(', ', $existingFiles)
                ], 400);
            }

            // Proceder a crear carpetas (crear un objeto vacío con el prefijo)
            foreach ($foldersToUpload as $folderPrefix) {
                Storage::disk('s3')->put($folderPrefix, '');
            }

            // Proceder a subir los archivos
            foreach ($filesToUpload as $fileData) {
                Storage::disk('s3')->putFileAs(
                    $fileData['path'],
                    $fileData['file'],
                    $fileData['name']
                );
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Contenido cargado exitosamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Manejar errores de validación
            return response()->json([
                'success' => false,
                'mensaje' => $e->validator->errors()->first()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error subiendo archivos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al subir contenido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determina si una ruta relativa representa una carpeta.
     *
     * @param string $relativePath
     * @return bool
     */
    private function isFolder($relativePath)
    {
        // Si la ruta termina con una barra inclinada, es una carpeta
        if (substr($relativePath, -1) === '/') {
            return true;
        }

        // Alternativamente, puedes determinar si no tiene una extensión
        return pathinfo($relativePath, PATHINFO_EXTENSION) === '';
    }

    /**
     * Verifica si una carpeta existe en S3 comprobando si hay al menos un objeto con el prefijo dado.
     *
     * @param string $prefix El prefijo que representa la carpeta (debe terminar con '/').
     * @return bool True si la carpeta existe, false en caso contrario.
     */
    private function folderExistsInS3($prefix)
    {
        // Lista objetos con el prefijo de la carpeta
        $objects = Storage::disk('s3')->files($prefix, false, 1); // Limitar a 1 para optimizar

        // Si hay al menos un objeto, consideramos que la carpeta existe
        return !empty($objects);
    }

    /**
     * Sanitiza el nombre del archivo para evitar caracteres no permitidos.
     *
     * @param string $name
     * @return string
     */
    protected function sanitizeFileName($name)
    {
        // Permitir espacios, eliminar sólo caracteres peligrosos
        return preg_replace('/[^ \w\-\.]/', '', $name);
    }






    public function eliminarElementos(Request $request)
    {
        try {
            $validated = $request->validate([
                'paths' => 'required|array',
                'paths.*' => 'string'
            ]);
    
            $s3 = Storage::disk('s3')->getClient();
            $bucket = config('filesystems.disks.s3.bucket');
    
            $deletedItems = [];
            $failedItems = [];
    
            foreach ($request->input('paths') as $encodedPath) {
                // Si decidiste no codificar las rutas en el cliente, elimina el urldecode
                $path = urldecode($encodedPath);
                $isFolder = substr($path, -1) === '/';
    
                try {
                    if ($isFolder) {
                        // Eliminar contenido recursivo
                        $objects = [];
                        $listParams = [
                            'Bucket' => $bucket,
                            'Prefix' => $path
                        ];
    
                        do {
                            $result = $s3->listObjectsV2($listParams);
                            foreach ($result->get('Contents') ?? [] as $object) {
                                $objects[] = ['Key' => $object['Key']];
                            }
    
                            // Eliminar en lotes de 1000
                            if (!empty($objects)) {
                                $s3->deleteObjects([
                                    'Bucket' => $bucket,
                                    'Delete' => ['Objects' => $objects]
                                ]);
                                $objects = [];
                            }
    
                            $listParams['ContinuationToken'] = $result->get('NextContinuationToken');
                        } while ($result->get('IsTruncated'));
    
                        // Eliminar marcador de carpeta
                        if ($s3->doesObjectExist($bucket, $path)) { // Corregido aquí
                            $s3->deleteObject([
                                'Bucket' => $bucket,
                                'Key' => $path
                            ]);
                        }
                    } else {
                        // Eliminar archivo individual
                        $s3->deleteObject([
                            'Bucket' => $bucket,
                            'Key' => $path
                        ]);
                    }
    
                    // Verificar eliminación exitosa
                    if (!$s3->doesObjectExist($bucket, $path)) { // Corregido aquí
                        $deletedItems[] = $encodedPath;
                    } else {
                        $failedItems[] = $encodedPath;
                    }
    
                } catch (\Exception $e) {
                    Log::error("Error eliminando {$encodedPath}: " . $e->getMessage());
                    $failedItems[] = $encodedPath;
                }
            }
    
            $response = [
                'success' => empty($failedItems),
                'mensaje' => empty($failedItems) 
                    ? 'Elementos eliminados correctamente' 
                    : 'Algunos elementos no se pudieron eliminar',
                'deleted' => $deletedItems
            ];
    
            if (!empty($failedItems)) {
                $response['failed'] = $failedItems;
            }
    
            return response()->json($response, empty($failedItems) ? 200 : 207);
    
        } catch (\Exception $e) {
            Log::error('Error crítico en eliminarElementos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error del sistema: ' . $e->getMessage()
            ], 500);
        }
    }
    






    private function getParentPath($currentPath)
        {
            if ($currentPath === 'Inicio') {
                return null;
            }
            
            $pathParts = explode('/', $currentPath);
            $parentParts = array_slice($pathParts, 0, -1);
            $parentPath = implode('/', $parentParts);
            
            return $parentPath ?: 'Inicio'; // Usar valor predeterminado si está vacío
        }








        public function renombrarElemento(Request $request)
        {
            $validated = $request->validate([
                'path' => 'required|string',
                'nuevoNombre' => 'required|string|max:255'
            ]);
        
            // Decodificación directa sin sanitizar
            $oldPath = urldecode($validated['path']);
            $nuevoNombre = urldecode($validated['nuevoNombre']);
        
            // Determinar si es carpeta
            $isFolder = substr($oldPath, -1) === '/';
            $oldPath = rtrim($oldPath, '/') . ($isFolder ? '/' : '');
        
            Log::info("Renombrando: {$oldPath} -> {$nuevoNombre}");
        
            return $isFolder 
                ? $this->renombrarCarpeta($oldPath, $nuevoNombre)
                : $this->renombrarArchivo($oldPath, $nuevoNombre);
        }









        private function renombrarArchivo($oldPath, $nuevoNombre)
{
    $newPath = dirname($oldPath) . '/' . $nuevoNombre;

    if (Storage::disk('s3')->exists($newPath)) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Ya existe un archivo con ese nombre'
        ], 409);
    }

    try {
        Storage::disk('s3')->move($oldPath, $newPath);
        return response()->json([
            'success' => true,
            'mensaje' => 'Archivo renombrado exitosamente'
        ]);
        
    } catch (\Exception $e) {
        Log::error("Error renombrando archivo: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'mensaje' => 'Error al renombrar el archivo'
        ], 500);
    }
}




private function renombrarCarpeta($oldPath, $nuevoNombre) {

    $oldDir = rtrim($oldPath, '/') . '/';
    $parentDir = dirname($oldDir) === '.' ? '' : dirname($oldDir) . '/';
    $newDir = $parentDir . $nuevoNombre . '/';

    // Obtener la configuración de S3
    $config = config('filesystems.disks.s3');

    // Crear una nueva instancia del cliente de S3
    try {
        $s3 = new S3Client([
            'credentials' => [
                'key'    => $config['key'],
                'secret' => $config['secret'],
            ],
            'region' => $config['region'],
            'version' => 'latest',
            'bucket' => $config['bucket'],
            // Agrega 'endpoint' si estás usando un endpoint personalizado
            // 'endpoint' => $config['endpoint'] ?? null,
        ]);
    } catch (\Exception $e) {
        Log::error("Error al crear el cliente de S3: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'mensaje' => 'Error al configurar el cliente de S3.'
        ], 500);
    }

    // Validar colisión usando listObjectsV2
    try {
        $listParams = [
            'Bucket' => $config['bucket'],
            'Prefix' => $newDir,
            'MaxKeys' => 1,
        ];

        $result = $s3->listObjectsV2($listParams);
        $exists = $result['KeyCount'] > 0;

        if ($exists) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Ya existe una carpeta con ese nombre en el directorio seleccionado.'
            ], 409);
        }
    } catch (\Exception $e) {
        Log::error("Error al verificar la existencia de la carpeta: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'mensaje' => 'Error al verificar la existencia de la carpeta.'
        ], 500);
    }

    try {
        // Obtener TODOS los objetos bajo el prefijo (archivos + subcarpetas implícitas)
        $archivos = Storage::disk('s3')->allFiles($oldDir);

        foreach ($archivos as $archivo) {
            $nuevoArchivo = str_replace($oldDir, $newDir, $archivo);
            Storage::disk('s3')->move($archivo, $nuevoArchivo);
        }

        // Eliminar directorio antiguo (opcional)
        Storage::disk('s3')->deleteDirectory($oldDir);

        return response()->json([
            'success' => true,
            'mensaje' => 'Carpeta y subcarpetas renombradas exitosamente.'
        ]);

    } catch (\Exception $e) {
        Log::error("Error renombrando carpeta: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'mensaje' => 'Error al mover recursivamente.'
        ], 500);
    }
}













public function getFileUrl(Request $request)
{
    // Validar la solicitud
    $request->validate([
        'path' => 'required|string',
    ]);

    $filePath = urldecode($request->input('path'));

    // Verificar si el archivo existe en S3
    if (!Storage::disk('s3')->exists($filePath)) {
        return response()->json([
            'success' => false,
            'mensaje' => 'El archivo no existe.'
        ], 404);
    }

    try {
        // Generar una URL pre-firmada que expira en 5 minutos
        $url = Storage::disk('s3')->temporaryUrl(
            $filePath,
            now()->addMinutes(5)
        );

        return response()->json([
            'success' => true,
            'url' => $url
        ], 200);
    } catch (\Exception $e) {
        Log::error("Error al generar la URL del archivo: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'mensaje' => 'Error al generar la URL del archivo.'
        ], 500);
    }
}










public function descargarElementos(Request $request)
{
    // Validar la solicitud
    $request->validate([
        'elementos' => 'required|array',
        'elementos.*' => 'required|string',
        'currentPath' => 'required|string',
    ]);

    $elementos = $request->input('elementos');
    $currentPath = rtrim(urldecode($request->input('currentPath')), '/');

    // Crear un archivo ZIP temporal
    $zipFileName = 'descarga_' . time() . '.zip';
    $zipPath = storage_path('app/temp/' . $zipFileName);

    // Asegurarse de que el directorio temporal existe
    if (!file_exists(storage_path('app/temp'))) {
        mkdir(storage_path('app/temp'), 0755, true);
    }

    $zip = new ZipArchive();

    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        Log::error("No se pudo crear el archivo ZIP en '{$zipPath}'.");
        return response()->json([
            'success' => false,
            'mensaje' => 'No se pudo crear el archivo ZIP.'
        ], 500);
    }

    foreach ($elementos as $elemento) {
        $elementoPath = $currentPath . '/' . $elemento;

        try {
            // Verificar si el elemento es una carpeta o un archivo
            $files = Storage::disk('s3')->allFiles($elementoPath);

            if (count($files) > 0) {
                // Es una carpeta, agregar todos los archivos bajo esta carpeta
                foreach ($files as $file) {
                    try {
                        $fileContent = Storage::disk('s3')->get($file);
                        $relativePath = Str::after($file, $currentPath . '/');
                        $zip->addFromString($relativePath, $fileContent);
                    } catch (\Exception $e) {
                        Log::error("Error al obtener el archivo '{$file}': " . $e->getMessage());
                        continue;
                    }
                }
            } else {
                // No hay archivos bajo el path, verificar si es un archivo
                if (Storage::disk('s3')->exists($elementoPath)) {
                    try {
                        $fileContent = Storage::disk('s3')->get($elementoPath);
                        $relativePath = Str::after($elementoPath, $currentPath . '/');
                        $zip->addFromString($relativePath, $fileContent);
                    } catch (\Exception $e) {
                        Log::error("Error al obtener el archivo '{$elementoPath}': " . $e->getMessage());
                        continue;
                    }
                } else {
                    // El elemento no existe, registrar y continuar
                    Log::warning("El elemento '{$elementoPath}' no existe y será omitido.");
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al procesar el elemento '{$elementoPath}': " . $e->getMessage());
            continue;
        }
    }

    $zip->close();

    // Verificar si el ZIP contiene al menos un archivo
    if (filesize($zipPath) === 0) {
        unlink($zipPath);
        return response()->json([
            'success' => false,
            'mensaje' => 'No se encontraron archivos para descargar.'
        ], 404);
    }

    // Devolver el archivo ZIP al usuario y eliminarlo después de enviar
    return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
}








}
