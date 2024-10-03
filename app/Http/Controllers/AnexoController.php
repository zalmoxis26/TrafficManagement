<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use App\Models\Trafico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\AnexoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AnexoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $anexos = Anexo::paginate();

        return view('anexo.index', compact('anexos'))
            ->with('i', ($request->input('page', 1) - 1) * $anexos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $anexo = new Anexo();

        return view('anexo.create', compact('anexo'));
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'trafico_id' => 'required|exists:traficos,id',
            'descripcion' => 'max:255',
            'archivo' => 'required|file|mimes:pdf,jpg,png,doc,docx,xls,xlsx,txt|max:2048',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Obtener el ID del tráfico y la descripción del formulario
        $traficoId = $request->input('trafico_id');
        $descripcion = $request->input('descripcion');
    
        // Obtener el archivo del formulario
        $archivo = $request->file('archivo');

        // Guardar el archivo en la carpeta de anexos
        $rutaArchivo = $archivo->store('public/Anexos/AnexoTrafico_' . $traficoId);
    
        // Eliminar 'public/' de la ruta antes de guardar en la base de datos
        $rutaArchivo = str_replace('public/', '', $rutaArchivo);
        
        // Crear el registro del anexo en la base de datos
        $anexo = new Anexo();
        $anexo->descripcion = $descripcion;
        $anexo->archivo = $rutaArchivo;
        $anexo->asunto = $request->input('asunto');
        $anexo->save();
    
        // Relacionar el tráfico y el anexo en la tabla pivot
        $trafico = Trafico::find($traficoId);
        $trafico->anexos()->attach($anexo->id);
    
        return redirect()->back()->with('success', 'Anexo creado y asociado al tráfico con éxito.');
    }


    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $anexo = Anexo::find($id);

        return view('anexo.show', compact('anexo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $anexo = Anexo::find($id);

        return view('anexo.edit', compact('anexo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AnexoRequest $request, Anexo $anexo): RedirectResponse
    {
        $anexo->update($request->validated());

        return Redirect::route('anexos.index')
            ->with('success', 'Anexo updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        $anexo = Anexo::find($id);

        if ($anexo) {
            // Eliminar el archivo del almacenamiento
            if (Storage::exists('public/' . $anexo->archivo)) {
                Storage::delete('public/' . $anexo->archivo);
            }

            // Eliminar el registro de la base de datos
            $anexo->delete();

            return redirect()->back()->with('success', 'Anexo eliminado exitosamente.');
        }

        return redirect()->back()->with('error', 'Anexo no encontrado.');
    }
}
