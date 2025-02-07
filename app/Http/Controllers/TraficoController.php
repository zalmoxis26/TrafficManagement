<?php

namespace App\Http\Controllers;

use App\Models\Trafico;
use App\Events\TraficoCreated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Requests\TraficoRequest;
use App\Events\FacturaUpdated;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\UsersEmpresa;
use App\Models\Revisione;
use Carbon\Carbon;
use App\Models\Anexo;
use App\Models\Historial;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Exports\TraficoExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth; // Importar Auth

class TraficoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener el usuario autenticado
        $user = auth()->user();
    
        // =========================================================================================
        // CÓDIGO ORIGINAL (comentado):
        // $userEmpresas = $user->empresas; 
        //
        // NUEVO: obtener todas las empresas
        // =========================================================================================

        
        $userEmpresas = Empresa::all();

    
        // =================================================================================================
        // CÓDIGO ORIGINAL (comentado):
        // $empresasAsignadasIds = $user->empresas->pluck('empresa_id');
        // =================================================================================================

    
        // Definir las fechas por defecto
        $defaultFechaFin = Carbon::now()->addDay()->format('Y-m-d');
        $defaultFechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
      
        // Usar las fechas proporcionadas en el request si existen, de lo contrario usar las fechas por defecto
        $fechaInicio = $request->input('fechaInicio', $defaultFechaInicio);
        $fechaFin = $request->input('fechaFin', $defaultFechaFin);
    
        // Validar que la diferencia entre las fechas no sea mayor a 3 meses
        $fechaInicioCarbon = Carbon::parse($fechaInicio)->startOfDay();
        $fechaFinCarbon = Carbon::parse($fechaFin)->endOfDay();
    
        if ($fechaInicioCarbon->diffInMonths($fechaFinCarbon) > 3) {
            return redirect()->back()->withErrors(['Las Seleccion de fechas no pueden tener una diferencia mayor a 3 meses.']);
        }
    
        // =================================================================================================
        // CÓDIGO ORIGINAL (comentado):
        //
        // Filtrar los tráficos por las empresas asignadas al usuario autenticado:
        // $query = Trafico::whereIn('empresa_id', $empresasAsignadasIds)
        //                ->where('statusTrafico', 'ABIERTO')
        //                ->whereBetween('fechaReg', [$fechaInicio, $fechaFin]);
        //
        // NUEVO: Quitar filtro por empresa, para mostrar todos los tráficos:
        // =================================================================================================
    
        $query = Trafico::where('statusTrafico', 'ABIERTO')
                        ->whereBetween('fechaReg', [$fechaInicio, $fechaFin]);
    
        // Filtrar por empresa seleccionada
        if ($request->has('empresaSelect') && $request->empresaSelect != '00') {
            $query->whereHas('empresa', function($q) use ($request) {
                $q->where('id', $request->empresaSelect);
            });
        }
    
        // Obtener los tráficos filtrados
        $traficos = $query->orderBy('fechaReg', 'DESC')->get();
    
        return view('trafico.index', compact('traficos', 'userEmpresas','fechaInicio','fechaFin', 'request'));
    }
    
    public function indexTraficosCerrados(Request $request)
    {
        // Obtener el usuario autenticado
        $user = auth()->user();
    
        // =========================================================================================
        // CÓDIGO ORIGINAL (comentado):
        // $userEmpresas = $user->empresas; 
        //
        // NUEVO: obtener todas las empresas
        // =========================================================================================

        
        $userEmpresas = Empresa::all();
    
        // =================================================================================================
        // CÓDIGO ORIGINAL (comentado):
        // $empresasAsignadasIds = $userEmpresas->pluck('empresa.id');
        // =================================================================================================
    
        // Definir las fechas por defecto
        $defaultFechaFin = Carbon::now()->format('Y-m-d');
        $defaultFechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
    
        // Usar las fechas proporcionadas en el request si existen, de lo contrario usar las fechas por defecto
        $fechaInicio = $request->input('fechaInicio', $defaultFechaInicio);
        $fechaFin = $request->input('fechaFin', $defaultFechaFin);
    
        // Validar que la diferencia entre las fechas no sea mayor a 3 meses
        $fechaInicioCarbon = Carbon::parse($fechaInicio);
        $fechaFinCarbon = Carbon::parse($fechaFin);
    
        if ($fechaInicioCarbon->diffInMonths($fechaFinCarbon) > 3) {
            return redirect()->back()->withErrors(['Las Seleccion de fechas no pueden tener una diferencia mayor a 3 meses.']);
        }
    
        // =================================================================================================
        // CÓDIGO ORIGINAL (comentado):
        //
        // $query = Trafico::whereIn('empresa_id', $empresasAsignadasIds)
        //                 ->where('statusTrafico', 'CERRADO')
        //                 ->whereBetween('fechaReg', [$fechaInicio, $fechaFin]);
        //
        // NUEVO: Quitar filtro por empresa, para mostrar todos los tráficos cerrados:
        // =================================================================================================
    
        $query = Trafico::where('statusTrafico', 'CERRADO')
                        ->whereBetween('fechaReg', [$fechaInicio, $fechaFin]);
    
        // Filtrar por empresa seleccionada
        if ($request->has('empresaSelect') && $request->empresaSelect != '00') {
            $query->whereHas('empresa', function($q) use ($request) {
                $q->where('id', $request->empresaSelect);
            });
        }
    
        // Obtener los tráficos filtrados
        $traficos = $query->orderBy('fechaReg', 'DESC')->get();
    
        return view('trafico.indexCerrados', compact('traficos','userEmpresas','fechaInicio','fechaFin', 'request'));
    }
    



    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $trafico = new Trafico();

        return view('trafico.create', compact('trafico'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TraficoRequest $request): RedirectResponse
    {
        Trafico::create($request->validated());

        return Redirect::route('traficos.index')
            ->with('success', 'Trafico created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $trafico = Trafico::find($id);

        return view('trafico.show', compact('trafico'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $trafico = Trafico::find($id);

         // Obtener el usuario autenticado
         $usuario = Auth::user();
         // Obtener las empresas asociadas con el usuario
         $empresas = $usuario->empresas; // Usando la relación definida en el modelo User

        // Determinar si lleva revisión basado en si existe una revisión asociada
        $llevaRevision = $trafico->revision ? 'si' : 'no';
        $ubicacionRevision = $trafico->revision ? $trafico->revision->ubicacionRevision : '';

        return view('trafico.edit', compact('trafico', 'empresas', 'llevaRevision', 'ubicacionRevision'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Trafico $trafico)
    {

          // Definir reglas de validación para los campos del formulario
            $validatedData = $request->validate([
                'factura' => 'required|string|max:255',
                'empresa_id' => 'required', // Asegúrate de que la empresa exista
                'fechaReg' => 'required|date', // Validar la fecha
                'aduana' => 'required',
                'patente' => 'required',
                'Toperacion' => 'required',
                'lleva_revision' => 'required|in:si,no',
                'ubicacionRevision' => 'nullable|string|max:255'
            ]);

        // Actualizar los campos del tráfico con los nuevos datos
        $trafico->factura = $validatedData['factura'];
        $trafico->empresa_id = $validatedData['empresa_id'];
        $trafico->fechaReg = $validatedData['fechaReg'];
        $trafico->aduana = $validatedData['aduana'];
        $trafico->patente = $validatedData['patente'];
        $trafico->Toperacion = $validatedData['Toperacion'];
        

        // Verificar si se seleccionó "lleva_revision" como "sí"
        if ($validatedData['lleva_revision'] == 'si') {
            if ($trafico->revision) {
                // Actualizar la revisión existente
                $revision = $trafico->revision;
                $revision->ubicacionRevision = $validatedData['ubicacionRevision'];
                $revision->save();
            } else {
                // Crear una nueva revisión
                $revision = new Revisione();
                $revision->nombreRevisor = 'sinAsignar';
                $revision->facturaCorrecta = $trafico->fechaReg;
                $revision->status = 'PENDIENTE';
                $revision->ubicacionRevision = $validatedData['ubicacionRevision'];
                $revision->correccionFactura = 'NO';
                $revision->save();

                // Asociar la nueva revisión al tráfico
                $trafico->revision()->associate($revision);
                $trafico->revision_id = $revision->id;
            }
            $trafico->Revision = 'PENDIENTE';
        } else {
            // Si "lleva_revision" es "no", eliminar la revisión existente si la hay
            if ($trafico->revision) {
                $trafico->revision->delete();
                $trafico->Revision = 'N/A';
                $trafico->revision()->dissociate();
                $trafico->revision_id = null;
            }

            $trafico->Revision = 'N/A';
        }

        // Guardar los cambios en el tráfico
        $trafico->save();

        // Redirigir con un mensaje de éxito
        return redirect()->route('traficos.index')->with('success', 'Tráfico actualizado correctamente.');

    }

    public function destroy($id): RedirectResponse
    {
        $trafico = Trafico::find($id);

        // Eliminar las revisiones, pedimentos y anexos asociados
        $trafico->revision()->delete();
        $trafico->pedimento()->delete();
        $trafico->anexos()->delete();
        $trafico->comments()->delete();
        $trafico->historials()->delete();


        // Eliminar la carpeta de facturas asociada al tráfico
        $folders = [
            'Facturas/FacturaTrafico_' . $trafico->id,
            'Revisiones/RevisionTrafico_' . $trafico->id,
            'Anexos/AnexoTrafico_' . $trafico->id,
        ];

        foreach ($folders as $folder) {
            if (Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->deleteDirectory($folder);
            }
        }

        // Finalmente, eliminar el tráfico
        $trafico->delete();


        return Redirect::route('traficos.index')
            ->with('success', 'Trafico deleted successfully');
    }

    public function createFromFactura(): View
    {
        $fechaDeHoy = Carbon::now('America/Los_Angeles')->format('Y-m-d\TH:i');

        // Obtener el usuario autenticado
        $usuario = Auth::user();

        // Obtener las empresas asociadas con el usuario
        $empresas = $usuario->empresas; // Usando la relación definida en el modelo User

        // Pasar fecha de hoy y empresas a la vista
        return view('trafico.TraficoDesdeFactura', compact('fechaDeHoy', 'empresas'));
    }

    public function storeFromFactura(Request $request)
    {
    
        // Definir reglas de validación para los campos del formulario
        $validatedData = $request->validate([
            'factura' => 'required|string|max:255',
            'empresa_id' => 'required', // Asegúrate de que la empresa exista
            'fechaReg' => 'required|date', // Validar la fecha
            'adjuntoFactura' => 'required|file|mimes:pdf,jpg,png|max:3000',
            'aduana' => 'required',
            'patente' => 'required' ,
            'Toperacion' => 'required',
             ]);


            // Obtener el valor del radio button "Lleva Revisión"
            $llevaRevision = $request->input('lleva_revision'); // Esto será 'si' o 'no'


            // Crear un nuevo registro de tráfico con los datos validados
            $trafico = Trafico::create($validatedData);
            $trafico->MxDocs = "PENDIENTE";
            $trafico->statusTrafico = "ABIERTO";

        if ($request->hasFile('adjuntoFactura')) {
            // El archivo desde la solicitud
            $archivo = $request->file('adjuntoFactura');
        
            // Obtener el nombre original del archivo antes de guardarlo
            $nombreOriginal = $archivo->getClientOriginalName();
        
            // Guardar el archivo con el nombre original en 'storage/app/public/facturas'
            $rutaArchivo = $archivo->storeAs('Facturas/FacturaTrafico_' . $trafico->id   , $nombreOriginal, 'public');
        
            // Agregar la ruta completa al conjunto de datos validados
            $trafico['adjuntoFactura'] = '/' .  $rutaArchivo;

    
        }
         // Establecer el valor de "revision" basado en el valor de "lleva_revision"
         if ($llevaRevision == 'si') {

                
            $revision = new Revisione();
            $revision->nombreRevisor = 'sinAsignar';
           // $revision->inicioRevision = Carbon::now('America/Los_Angeles'); // Fecha y hora actuales
            $revision->facturaCorrecta =  $trafico->fechaReg;
            $revision->status = 'PENDIENTE';
            $revision->ubicacionRevision = $request->input('ubicacionRevision');
            $revision->correccionFactura = 'NO';
        
            // Guardar la revisión en la base de datos
            $revision->save();
        
            // Asociar la revisión al tráfico
            $trafico->revision()->associate($revision);
            $trafico['Revision'] = 'PENDIENTE'; // Asignar el valor adecuado para "sí"
            $trafico['revision_id']= $revision->id;

            $trafico->save();

        } else {
            $trafico['Revision'] = 'N/A'; // Asignar el valor adecuado para "no"
            $trafico->save();
        }

         // Emitir el evento
        event(new TraficoCreated($trafico));

         // Registrar el evento en el historial
         Historial::create([
            'trafico_id' => $trafico->id,
            'nombre' => 'Recepcion de Factura',
            'descripcion' => 'Recepcion de Factura se inicia nuevo Proceso de Trafico.',
            'hora' => Carbon::now('America/Los_Angeles'),
            'adjunto' => $trafico->adjuntoFactura,
        ]);


        // Redireccionar con un mensaje de éxito
        return Redirect::route('traficos.index') // Asegúrate de tener esta ruta definida
            ->with('success', 'Tráfico creado con éxito.');
    }

    public function exportTraficos(Request $request)
    {
        $exportType = $request->input('exportType'); // Get the export type, default to 'TODOS'
        $estatus = $request->input('status');
        $fechaInicio = $request->input('fechaInicio');
        $fechaFin = $request->input('fechaFin');

       

        $name = "Reporte de Todos los Traficos del " . $fechaInicio .".xlsx";      
        if($exportType === "importacion"){
            $name = "REPORTE DE IMPORTACION.xlsx";
        }elseif($exportType === "exportacion"){
            $name = "REPORTE DE EXPORTACION.xlsx";
        }


        return Excel::download(new TraficoExport($exportType,$estatus,$fechaInicio, $fechaFin), $name);


    }


    public function streamFactura($id)
    {

        
        // Buscar el pedimento por su ID
        $trafico = Trafico::findOrFail($id);

        // Obtener el nombre del archivo adjunto
        $fileName = $trafico->adjuntoFactura;
    
        // Construir la ruta completa del archivo
        $filePath = storage_path('app/public/' . $fileName);
        
        

        // Verificar si el archivo existe
        if (file_exists($filePath)) {
            // Transmitir el archivo al navegador
            return response()->file($filePath);
        } else {
            // Si el archivo no existe, devolver una respuesta 404
            abort(404);
        }
    }


    public function sustituirFactura(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:3000',
            'trafico_id' => 'required|exists:traficos,id'
        ]);


        // Obtener el tráfico
        $trafico = Trafico::with('revision')->findOrFail($request->trafico_id);


         // Obtener la ruta completa de la factura anterior
        $rutaFacturaAnterior = 'public/' . $trafico->adjuntoFactura;

        // Si existe una factura anterior
    if ($trafico->adjuntoFactura && Storage::exists($rutaFacturaAnterior)) {
        // Generar un nombre único para el archivo
        $nombreOriginal = pathinfo($trafico->adjuntoFactura, PATHINFO_FILENAME);
        $extension = pathinfo($trafico->adjuntoFactura, PATHINFO_EXTENSION);
        $nuevoNombre = $nombreOriginal . '_' . Str::uuid() . '.' . $extension;

        // Guardar el archivo en la carpeta de anexos con el nuevo nombre
        $nuevaRuta = 'public/Historial/FacturaSustituidaTrafico_' . $trafico->id . '/' . $nuevoNombre;
        Storage::move($rutaFacturaAnterior, $nuevaRuta);

        // Eliminar 'public/' de la ruta antes de guardar en la base de datos
        $rutaArchivo = str_replace('public/', '', $nuevaRuta);


     // Registrar el evento en el historial
     Historial::create([
        'trafico_id' => $trafico->id,
        'nombre' => 'Sustitucion de Factura(Anterior-No Valida)',
        'descripcion' => 'Factura No valida, Sustituida por cambio o errores en la misma.',
        'hora' => Carbon::now('America/Los_Angeles'),
        'adjunto' => $rutaArchivo,
    ]);

    }
        // Guardar el archivo en storage/public/adjuntosFacturas
        
            $archivo = $request->file('archivo');
            $filename =  $archivo->getClientOriginalName();
            $path = $archivo->storeAs('public/Facturas/Factura' .'Trafico_' . $trafico->id, $filename);

            // Actualizar el tráfico con la nueva información del archivo
            $trafico->adjuntoFactura = '/Facturas/Factura'  .'Trafico_' . $trafico->id . '/' . $filename;


            if($trafico->Revision != 'N/A'  &&  $trafico->revision->correccionFactura === 'NO' ){
                // campo correcion de revision no confundir
                $trafico->revision->correccionFactura = "SI";
                //campo revision de trafico
                $trafico->Revision = "EN ESPERA DE CORRECCIONES";       
                $trafico->revision->save();
            }
           
            $trafico->save();

            event(new FacturaUpdated($trafico));

             // Registrar el evento en el historial
     Historial::create([
        'trafico_id' => $trafico->id,
        'nombre' => 'Recepcion de Factura(Nueva)',
        'descripcion' => 'Recepcion de Factura Correcta por cambio o error en Factura anterior.',
        'hora' => Carbon::now('America/Los_Angeles'),
        'adjunto' => $trafico->adjuntoFactura,
    ]);
            
        

        // Redirigir con un mensaje de éxito
        return redirect()->back()->with('success', 'Factura sustituida exitosamente.');
    }

}
