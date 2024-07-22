<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessFtpFiles;


class DocumentFtpController extends Controller
{
    public function index($directory = '/')
    {
        $directory = urldecode($directory);
        $ftpDisk = Storage::disk('ftp');

        // Obtener lista de archivos y directorios en el directorio FTP
        try {
            $contents = $ftpDisk->listContents($directory, false);
            $files = [];
            $directories = [];

            foreach ($contents as $content) {
                if ($content['type'] === 'file') {
                    $files[] = $content;
                } elseif ($content['type'] === 'dir') {
                    $directories[] = $content;
                }
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al listar los archivos: ' . $e->getMessage());
        }

        return view('documentosFTP.index', compact('files', 'directories', 'directory'));
    }


    public function download($filename)
    {

        $filename = urldecode($filename);
        $ftpDisk = Storage::disk('ftp');
        $localDisk = Storage::disk('local');

        $filePath = '/' . $filename;

        // Descargar el archivo y guardarlo localmente
        try {
            if ($ftpDisk->exists($filePath)) {
                $contents = $ftpDisk->get($filePath);
                $localDisk->put('invoices/' . $filename, $contents);

                return response()->download(storage_path('app/invoices/' . $filename));
            } else {
                return redirect()->back()->with('error', 'Archivo no encontrado.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al descargar el archivo: ' . $e->getMessage());
        }
    }


    public function dispatchJob()
    {
        ProcessFtpFiles::dispatch();
        return response()->json(['status' => 'Job dispatched']);
    }

    public function uploadToFTP(Request $request)
    {
         // Validar la solicitud para asegurarse de que se están subiendo archivos
         $request->validate([
            'files.*' => 'required|file|mimes:txt,pdf',
        ]);

        $uploadedFiles = $request->file('files');
        $paths = [];
        $errors = [];

        foreach ($uploadedFiles as $file) {
            // Obtener el nombre original del archivo
            $filename = $file->getClientOriginalName();

            // Subir el archivo al servidor FTP
            $path = $file->storeAs('/', $filename, 'ftp');

            // Verificar si el archivo se subió correctamente
            if ($path) {
                $paths[] = $path;
            } else {
                $errors[] = 'Error al subir el archivo: ' . $filename;
            }
        }

        // Verificar si hubo errores
        if (count($errors) > 0) {
            return response()->json(['message' => 'Algunos archivos no se pudieron subir', 'errors' => $errors], 500);
        } else {
            return response()->json(['message' => 'Todos los archivos se subieron exitosamente', 'paths' => $paths], 200);
        }
    }
}
