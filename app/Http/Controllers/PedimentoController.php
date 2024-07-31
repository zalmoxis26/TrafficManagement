<?php

namespace App\Http\Controllers;

use App\Models\Pedimento;
use App\Models\Trafico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\PedimentoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\Historial;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Events\MxDocsStatusUpdated;

class PedimentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $pedimentos = Pedimento::paginate();

        return view('pedimento.index', compact('pedimentos'))
            ->with('i', ($request->input('page', 1) - 1) * $pedimentos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $pedimento = new Pedimento();
       

        return view('pedimento.create', compact('pedimento'));
    }

    public function createFromTrafico($id)
    {
        $fechaDeHoy = Carbon::now('America/Los_Angeles')->format('Y-m-d');

        $pedimento = new Pedimento();
        $trafico = Trafico::find($id);

        
        return view('pedimento.createFromTrafico', compact('pedimento','trafico','fechaDeHoy'));
    }

    public function storeFromTrafico(Request $request,$id)
    {

        // Obtener el tráfico correspondiente al ID
        $trafico = Trafico::findOrFail($id);

         // Validar el adjunto si está presente
        $request->validate([
            'adjunto' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10000', // 10 MB
        ]);

        // Crear un nuevo pedimento con los datos validados
        $pedimento = Pedimento::create($request->all());


        // Asignar el pedimento al tráfico
        $trafico->pedimento()->associate($pedimento);
        $trafico->clavePed = $pedimento->clavePed;
        $trafico->MxDocs= $request->input('MxDocs');
        $trafico->save();


        if ($request->hasFile('adjunto')) {
            // Obtener el nombre original del archivo
            $nombreArchivo = $request->file('adjunto')->getClientOriginalName();
            // Mover el archivo a la carpeta Pedimentos
            $request->file('adjunto')->storeAs('/public/Pedimentos/PedimentoTrafico_' . $pedimento->traficos->first()->id . '/' , $nombreArchivo);
            // Asignar el nombre del archivo al pedimento o al tráfico, según sea necesario
            $pedimento->adjunto = $nombreArchivo;
            $pedimento->save();

             // Registrar el evento en el historial
             Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Recepcion de Pedimento',
                'descripcion' => 'Se adjunta pdf del pedimento Pedimento.',
                'hora' => Carbon::now('America/Los_Angeles'),
                'adjunto' => "/Pedimentos/PedimentoTrafico_" .  $trafico->id  . '/'. $pedimento->adjunto,
            ]);
        
        }

        // Registrar el evento en el historial
        Historial::create([
            'trafico_id' => $trafico->id,
            'nombre' => 'Nuevo Pedimento Asignado',
            'descripcion' => 'Se ha asignado Pedimento al Trafico.',
            'hora' => Carbon::now('America/Los_Angeles'),
        ]);

        return Redirect::route('traficos.index')
            ->with('success', 'Pedimento created successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PedimentoRequest $request): RedirectResponse
    {


        Pedimento::create($request->validated());

        return Redirect::route('pedimentos.index')
            ->with('success', 'Pedimento created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $pedimento = Pedimento::find($id);

        return view('pedimento.show', compact('pedimento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $pedimento = Pedimento::find($id);

        return view('pedimento.edit', compact('pedimento'));
    }

    public function editFromTrafico($id , $pedimentoId)
    {
        $trafico = Trafico::find($id);
        $pedimento = Pedimento::find($pedimentoId);


        if ($trafico->MxDocs === 'RECONOCIMIENTO CONCLUIDO') {
            $mxDocsStatus = '11'; // Cambia esta lógica según sea necesario
        } elseif ($trafico->MxDocs === 'LISTOS (DODA PITA EN TRAFICO) ') {
            $mxDocsStatus = '7';
        } elseif ($trafico->MxDocs === 'DESADUANAMIENTO LIBRE(VERDE)') {
            $mxDocsStatus = '9';
        } else {
            $mxDocsStatus = $trafico->MxDocs; // Mantener el valor original en otros casos
        }
       


        return view('pedimento.editFromTrafico', compact('pedimento', 'trafico', 'mxDocsStatus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pedimento $pedimento)
    {
        $pedimento->update($request->all());

        return Redirect::route('pedimentos.index')
            ->with('success', 'Pedimento updated successfully');
    }

    public function updateFromTrafico(Request $request, $pedimentoId)
    {

        $trafico = Trafico::find($request->trafico_id);
        // Buscar el pedimento por su ID
        $pedimento = Pedimento::find($pedimentoId);
        $pedimento->update($request->all());
     
        if ($request->hasFile('adjunto')) {
        
            if ($pedimento->adjunto) {
             
                Storage::delete('/public/Pedimentos/PedimentoTrafico_' . $pedimento->traficos->first()->id . '/' . $pedimento->adjunto);
            }
        
            // Obtener el nombre original del archivo
            $nombreArchivo = $request->file('adjunto')->getClientOriginalName();
            // Mover el archivo a la carpeta Pedimentos
            $request->file('adjunto')->storeAs('/public/Pedimentos/PedimentoTrafico_' . $pedimento->traficos->first()->id . '/', $nombreArchivo);
            // Asignar el nombre del archivo al pedimento
            $pedimento->adjunto =  $nombreArchivo;
            $pedimento->save();

            // Registrar el evento en el historial
            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Recepcion de Pedimento',
                'descripcion' => 'Se adjunta pdf del pedimento Pedimento.',
                'hora' => Carbon::now('America/Los_Angeles'),
                'adjunto' => "/Pedimentos/PedimentoTrafico_" .  $trafico->id  . '/'. $pedimento->adjunto,
            ]);

        }
        
        $trafico->clavePed = $pedimento->clavePed;

        if($request->input('MxDocs') === "7" ){
            $pedimento->fechaDodaPita =  $pedimento->fechaDodaPita = Carbon::now('America/Los_Angeles');
            $pedimento->save();
            $trafico->MxDocs= "LISTOS (DODA PITA EN TRAFICO)";
           
        }else{
            $trafico->MxDocs=$request->input('MxDocs');     
        }

       /* if($request->input('MxDocs') === "9" || $request->input('MxDocs') === "11" ){
            $trafico->statusTrafico = "CERRADO";
            
         } */

         $trafico->save();

    
         // Emitir el evento después de actualizar el estado de MxDocs
        broadcast(new MxDocsStatusUpdated($trafico));

        
        Historial::create([
            'trafico_id' => $trafico->id,
            'nombre' => 'Actualizacion Pedimento',
            'descripcion' => 'Se ha actualizado la informacion del pedimento.',
            'hora' => Carbon::now('America/Los_Angeles'),
        ]);

        // Redirigir a alguna ruta después de actualizar
        return Redirect::route('traficos.index')
            ->with('success', 'Pedimento Actualizado successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Pedimento::find($id)->delete();

        return Redirect::route('pedimentos.index')
            ->with('success', 'Pedimento deleted successfully');
    }

    public function streamPedimento($id)
{
    // Buscar el pedimento por su ID
    $pedimento = Pedimento::findOrFail($id);

    // Obtener el nombre del archivo adjunto
    $fileName = $pedimento->adjunto;

    // Construir la ruta completa del archivo
    $filePath = storage_path('app/public/Pedimentos/PedimentoTrafico_' . $pedimento->traficos->first()->id . '/' . $fileName);

    // Verificar si el archivo existe
    if (file_exists($filePath)) {
        // Transmitir el archivo al navegador
        return response()->file($filePath);
    } else {
        // Si el archivo no existe, devolver una respuesta 404
        abort(404);
    }
}

    


}
