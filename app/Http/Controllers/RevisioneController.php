<?php

namespace App\Http\Controllers;

use App\Models\Revisione;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\RevisioneRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Historial;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Events\RevisionUpdated;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class RevisioneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {

        // Obtener el usuario autenticado
    $user = auth()->user();

    // Obtener los IDs de las empresas asignadas al usuario
    $empresasAsignadasIds = $user->empresas->pluck('empresa_id');


    // Filtrar las revisiones para que solo se incluyan aquellas que pertenezcan a los traficos de las empresas asignadas al usuario
    $revisiones = Revisione::whereHas('traficos', function ($query) use ($empresasAsignadasIds) {
        $query->whereIn('empresa_id', $empresasAsignadasIds);
            })->orderBy('updated_at', 'DESC')
            ->get();

    return view('revisione.index', compact('revisiones'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $revisione = new Revisione();

        return view('revisione.create', compact('revisione'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Revisione::create($request->all());



        return Redirect::route('revisiones.index')
            ->with('success', 'Revisione created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $revisione = Revisione::find($id);

        return view('revisione.show', compact('revisione'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {


        $revisione = Revisione::find($id);
        // Obtener los usuarios que tienen el rol "revisor"
        $revisores = User::role('revisor')->get();


        return view('revisione.edit', compact('revisione','revisores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Revisione $revisione)
    {
    
       
        // Obtener el primer tráfico asociado a la revisión
        $trafico = $revisione->traficos()->first();

        // Validar el request, incluyendo el archivo adjunto
        $request->validate([
            'adjuntoRevision' => 'file|mimes:png,jpg,pdf,doc,docx,xls,xlsx|max:2048', // Ajusta los tipos y tamaño según tus necesidades
        ]);
    
        if ($request->input('Revision') === "EN PROCESO" && is_null($revisione->inicioRevision)) {

            $request['inicioRevision'] = Carbon::now('America/Los_Angeles')->format('Y-m-d\TH:i');
        } 

    
        if ($request->input('Revision') === "LIBERADA" ) {
            // Establecer la fecha de fin de la revisión
            $finRevisionRequest = $request->input('finRevision');

            // Si el valor del request es nulo, se usa Carbon::now(), de lo contrario se usa el valor del request
            $revisione->finRevision = is_null($finRevisionRequest)
                ? Carbon::now('America/Los_Angeles')->format('Y-m-d\TH:i')
                : Carbon::parse($finRevisionRequest)->format('Y-m-d\TH:i');
            
            // Convertir las fechas de inicio y fin a instancias de Carbon
            $inicio = Carbon::parse($revisione->inicioRevision);
            $fin = Carbon::parse($revisione->finRevision);
    
            // Calcular la diferencia en días, horas y minutos
            $diff = $inicio->diff($fin);
    
            $days = $diff->d;
            $hours = $diff->h;
            $minutes = $diff->i;
    
            // Construir la cadena legible
            $tiempoRevision = "{$days} dias {$hours} hrs {$minutes} mins";
    
            // Almacenar el tiempo de revisión como una cadena legible
            $revisione->tiempoRevision = $tiempoRevision;
    
            // Guardar los cambios
            $revisione->save();
    
        }

        // Actualizar el modelo Revisione con los datos del request
        $revisione->update($request->except('adjuntoRevision')); // Excluir el archivo de los datos actualizados directamente
    

        // Procesar el archivo adjunto
        if ($request->hasFile('adjuntoRevision')) {


            // Verificar si ya hay un archivo de revisión adjunto
            if ($revisione->adjuntoRevision) {
                $nombreOriginal = pathinfo($revisione->adjuntoRevision, PATHINFO_FILENAME);
                $extension = pathinfo($revisione->adjuntoRevision, PATHINFO_EXTENSION);
                $nuevoNombreRevision = $nombreOriginal . '_' . Str::uuid() . '.' . $extension;

                $oldFilePath = storage_path('app/public/' . $revisione->adjuntoRevision);
                $historialPath = 'Historial/RevisionSustituidaTrafico_' . $revisione->traficos()->first()->id . '/' . $nuevoNombreRevision;

                // Mover el archivo actual a Historial/RevisionSustituida
                if (File::exists($oldFilePath)) {
                    Storage::disk('public')->move($revisione->adjuntoRevision, $historialPath);

                    // Crear un historial para la sustitución de la revisión
                    Historial::create([
                        'trafico_id' => $trafico->id,
                        'nombre' => 'Sustitucion Revision (Anterior)',
                        'descripcion' => 'Se ha sustituido el archivo de revision anterior.',
                        'hora' => Carbon::now('America/Los_Angeles'),
                        'adjunto' => $historialPath,
                    ]);
                }
            }

            $file = $request->file('adjuntoRevision');
            $fileName =  $file->getClientOriginalName();
            $filePath = $file->storeAs('Revisiones/RevisionTrafico_' . $revisione->traficos()->first()->id , $fileName, 'public');
    
            // Guardar el nombre del archivo en la base de datos
            $revisione->adjuntoRevision = 'Revisiones/RevisionTrafico_' . $revisione->traficos()->first()->id . '/' . $fileName;
            $revisione->save();

   
            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Recepcion Nuevo Archivo Revision',
                'descripcion' => 'Se han adjuntado archivos de revision.',
                'hora' => Carbon::now('America/Los_Angeles'),
                'adjunto' =>  $revisione->adjuntoRevision,
            ]);
        }

        if($revisione->correccionFactura === 'SI'){
               
            $revisione->facturaCorrecta = $revisione->finRevision;
            $revisione->save();
        
        }
    
      
        if ($trafico) {

            $trafico->Revision = $request->input('Revision');
            $trafico->save();

            // Disparar el evento
            event(new RevisionUpdated($trafico));

        }

        
        if($request->input('Revision') === "LIBERADA" )  {
            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Actualizacion Status Revision (Liberada)',
                'descripcion' => 'La Revision se encuentra Liberada.',
                'hora' => Carbon::now('America/Los_Angeles'),
            ]);
        } else if($request->input('Revision') === "EN ESPERA DE CORRECCIONES") {
            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Actualizacion Status Revision (Solicitud de Correciones)',
                'descripcion' => 'La Revision se encuentra en Espera de Correciones.',
                'hora' => Carbon::now('America/Los_Angeles'),
            ]);
        }else {
            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Actualizacion de Revision',
                'descripcion' => 'La Revision ha sido Actualizada.',
                'hora' => Carbon::now('America/Los_Angeles'),
            ]);
        }

        
    
        // Redirigir a la ruta 'revisiones.index' con un mensaje de éxito
        return Redirect::route('revisiones.index')
            ->with('success', 'Revisione updated successfully');
    }
    

    public function destroy($id): RedirectResponse
    {
        Revisione::find($id)->delete();

        return Redirect::route('revisiones.index')
            ->with('success', 'Revisione deleted successfully');
    }
}
