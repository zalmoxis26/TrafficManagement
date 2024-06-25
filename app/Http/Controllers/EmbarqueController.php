<?php

namespace App\Http\Controllers;

use App\Models\Embarque;
use App\Models\Trafico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\EmbarqueRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Exports\EmbarquesExport;
use App\Models\Historial;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class EmbarqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $embarques = Embarque::paginate();

        return view('embarque.index', compact('embarques'))
            ->with('i', ($request->input('page', 1) - 1) * $embarques->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $embarque = new Embarque();

        return view('embarque.create', compact('embarque'));
    }

    public function createFromTrafico(Request $request)
{
    // Obtener los IDs de trafico seleccionados desde la solicitud
    $trafico_ids = $request->input('trafico_ids');
    $fechaDeHoy = Carbon::now('America/Los_Angeles')->format('Y-m-d\TH:i');

    // Validar si no se han seleccionado traficos
    if (empty($trafico_ids)) {
        return redirect()->back()->with('error', 'SELECCIONA ALMENOS UN TRAFICO A ASIGNAR');
    }

    // Convertir la cadena de IDs a un array
    $trafico_ids_array = explode(',', $trafico_ids);

    // Buscar los traficos que ya tienen embarque asignado
    $traficosConEmbarque = Trafico::whereIn('id', $trafico_ids_array)->whereNotNull('embarque')->get();

    if ($traficosConEmbarque->isNotEmpty()) {
        return redirect()->back()->with('error', 'UNO O MÁS TRAFICOS YA TIENEN EMBARQUE ASIGNADO');
    }

    $embarque = new Embarque();
    
    return view('embarque.createFromTrafico', compact('embarque','trafico_ids','fechaDeHoy'));
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Embarque::create($request->validated());

        return Redirect::route('embarques.index')
            ->with('success', 'Embarque created successfully.');
    }

    public function storeFromTrafico(Request $request){

        $embarque = Embarque::create($request->all());

        // Obtener los IDs de tráfico de la solicitud
        $trafico_ids = explode(',', $request->trafico_ids);


        // Asignar los tráficos al embarque en la tabla pivot
        $embarque->traficos()->sync($trafico_ids);

         // Asignar el valor de $embarque->numEmbarque a la columna embarque de los tráficos correspondientes
        Trafico::whereIn('id', $trafico_ids)->update(['embarque' => $embarque->numEmbarque]);

        // Crear un Historial para cada tráfico
        foreach ($trafico_ids as $trafico_id) {
            Historial::create([
                'trafico_id' => $trafico_id,
                'nombre' => 'Nuevo Embarque Asignado',
                'descripcion' => 'Se ha asignado el embarque ' . $embarque->numEmbarque . ' al tráfico.',
                'hora' => Carbon::now('America/Los_Angeles'),
            ]);
        }

        return Redirect::route('traficos.index')
            ->with('success', 'Embarque creado y relacionado successfully.');
    }

    public function desasignarFromTrafico(Request $request)
    {
           
        // Obtener los IDs de tráfico de la solicitud
        $trafico_ids = explode(',', $request->trafico_des);
        // Buscar todos los tráficos
        $traficos = Trafico::whereIn('id', $trafico_ids)->get();

        // Desasociar los tráficos de todos los embarques asociados
        foreach ($traficos as $trafico) {
            // Obtener los embarques asociados a este tráfico
            $numEmbarque = $trafico->embarques->first()->numEmbarque;

            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Embarque Desasignado',
                'descripcion' => 'Se ha Desasignado el embarque ' . $numEmbarque . ' al tráfico.',
                'hora' => Carbon::now('America/Los_Angeles'),
            ]);
            
            // Desasociar el tráfico de todos los embarques
            $trafico->embarques()->detach();
            $trafico->update(['embarque' => null]);

        }
   

        return Redirect::route('traficos.index')
            ->with('success', 'Embarque desasignado successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $embarque = Embarque::find($id);

        return view('embarque.show', compact('embarque'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $embarque = Embarque::find($id);

        return view('embarque.edit', compact('embarque'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmbarqueRequest $request, Embarque $embarque): RedirectResponse
    {


           // Obtén todos los datos del request
    $data = $request->all();

    // Verifica y actualiza el campo 'entregaDocs' si el checkbox está seleccionado y el campo en la BD es null
    if ($request->has('entregaDocs') && $embarque->entregaDocs === null) {
        $data['entregaDocs'] = Carbon::now('America/Los_Angeles');

        // Actualizar MxDocs a "Entregado" en todos los traficos relacionados
        foreach ($embarque->traficos as $trafico) {
            $trafico->MxDocs = 'ENTREGADO';
            $trafico->save();

            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Documentos Entregados Embarque',
                'descripcion' => 'Se han entregado los documentos para el embarque.',
                'hora' => $data['entregaDocs'] , 
            ]);
        }

           // Acceder al pedimento relacionado y asignar la misma hora y fecha a fechaDodaPita
           if ($trafico->pedimento && $trafico->pedimento->fechaDodaPita === null) {
                
                $trafico->pedimento->fechaDodaPita = $data['entregaDocs'];
                $trafico->pedimento->save();
            }
        
    } else {
        unset($data['entregaDocs']);
    }

    if ($request->has('rojoAduana') && $embarque->rojoAduana === null) {
        $data['rojoAduana'] = Carbon::now('America/Los_Angeles');

        foreach ($embarque->traficos as $trafico) {
            $trafico->MxDocs = 'RECONOCIMIENTO ADUANERO';

            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Estatus Embarque(Rojo)',
                'descripcion' => 'El Embarque: ' .  $embarque->numEmbarque . ' entro en Reconocimiento Aduanero (ROJO)',
                'hora' =>  $data['rojoAduana'] , 
            ]);

            $trafico->save();
        }

    } else {
        unset($data['rojoAduana']);
    }

    if ($request->has('modulado') && $embarque->modulado === null) {
        $data['modulado'] = Carbon::now('America/Los_Angeles');

        foreach ($embarque->traficos as $trafico) {
            $trafico->MxDocs = 'FINALIZADO';
            $trafico->statusTrafico = 'CERRADO';
            $trafico->save();

            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Estatus Embarque(Modulado)',
                'descripcion' => 'El Embarque: ' .  $embarque->numEmbarque . ' ha sido Modulado',
                'hora' =>   $data['modulado'], 
            ]);
            
        }

    } else {
        unset($data['modulado']);
    }

    
    // Actualiza el embarque con los datos filtrados
    $embarque->update($data);

    if ($request->has('rojoAduana') && $embarque->rojoAduana === null  || $request->has('modulado') && $embarque->modulado === null || $request->has('entregaDocs') && $embarque->entregaDocs === null ) {

    }else{

        foreach ($embarque->traficos as $trafico) {
            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Embarque Actualizado',
                'descripcion' => 'El Embarque: ' .  $embarque->numEmbarque . ' ha sido Actualizado',
                'hora' => Carbon::now('America/Los_Angeles'),
            ]);
        }
    }    

   


        return Redirect::back()
            ->with('success', 'Embarque updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Embarque::find($id)->delete();

        return Redirect::route('embarques.index')
            ->with('success', 'Embarque deleted successfully');
    }

    public function export(Request $request)
    {
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');
        $modulado = $request->input('modulado', 'TODOS');

        return Excel::download(new EmbarquesExport($fechaInicio, $fechaFin, $modulado), 'embarques.xlsx');
    }
}
