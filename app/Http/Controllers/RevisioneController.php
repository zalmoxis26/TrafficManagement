<?php

namespace App\Http\Controllers;

use App\Models\Revisione;
use App\Models\Trafico; // Asegúrate de tenerlo importado
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
use Illuminate\Support\Facades\Mail; // IMPORTANTE
use App\Mail\RevisionStatusMail;      // IMPORTANTE

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
    /*$revisiones = Revisione::whereHas('traficos', function ($query) use ($empresasAsignadasIds) {
        $query->whereIn('empresa_id', $empresasAsignadasIds);
            })->orderBy('updated_at', 'DESC')
            ->get();*/

            $revisiones = Revisione::whereHas('traficos')
            ->orderBy('updated_at', 'DESC')
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
        $trafico = $revisione->traficos()->first();

        $request->validate([
            'adjuntoRevision' => 'file|mimes:png,jpg,pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        if ($request->input('Revision') === "EN PROCESO" && is_null($revisione->inicioRevision)) {
            $request['inicioRevision'] = Carbon::now('America/Los_Angeles')->format('Y-m-d\TH:i');
        } 

        if ($request->input('Revision') === "LIBERADA" ) {
            $finRevisionRequest = $request->input('finRevision');
            $revisione->finRevision = is_null($finRevisionRequest)
                ? Carbon::now('America/Los_Angeles')->format('Y-m-d\TH:i')
                : Carbon::parse($finRevisionRequest)->format('Y-m-d\TH:i');
            
            $inicio = Carbon::parse($revisione->inicioRevision);
            $fin = Carbon::parse($revisione->finRevision);
            $diff = $inicio->diff($fin);
            $revisione->tiempoRevision = "{$diff->d} dias {$diff->h} hrs {$diff->i} mins";
            $revisione->save();
        }

        $revisione->update($request->except('adjuntoRevision'));

        if ($request->hasFile('adjuntoRevision')) {
            if ($revisione->adjuntoRevision) {
                $oldFilePath = storage_path('app/public/' . $revisione->adjuntoRevision);
                if (File::exists($oldFilePath)) {
                    $historialPath = 'Historial/RevisionSustituidaTrafico_' . $trafico->id . '/' . pathinfo($revisione->adjuntoRevision, PATHINFO_FILENAME) . '_' . Str::uuid() . '.' . pathinfo($revisione->adjuntoRevision, PATHINFO_EXTENSION);
                    Storage::disk('public')->move($revisione->adjuntoRevision, $historialPath);

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
            $fileName = $file->getClientOriginalName();
            $file->storeAs('Revisiones/RevisionTrafico_' . $trafico->id , $fileName, 'public');
            $revisione->adjuntoRevision = 'Revisiones/RevisionTrafico_' . $trafico->id . '/' . $fileName;
            $revisione->save();

            Historial::create([
                'trafico_id' => $trafico->id,
                'nombre' => 'Recepcion Nuevo Archivo Revision',
                'descripcion' => 'Se han adjuntado archivos de revision.',
                'hora' => Carbon::now('America/Los_Angeles'),
                'adjunto' => $revisione->adjuntoRevision,
            ]);
        }

        if($revisione->correccionFactura === 'SI'){
            $revisione->facturaCorrecta = $revisione->finRevision;
            $revisione->save();
        }

        if ($trafico) {
            $nuevoStatus = $request->input('Revision');
            $trafico->Revision = $nuevoStatus;
            $trafico->save();

            event(new RevisionUpdated($trafico));

            // COMENTA EL BUENO Y USA EL DE PRUEBA:
            // $this->enviarNotificacionRevision($trafico, $nuevoStatus);
            $this->enviarNotificacionRevisionPrueba($trafico, $nuevoStatus);

        }

        // Registro de Historial
        $nombreHist = $nuevoStatus === "LIBERADA" ? 'Actualizacion Status Revision (Liberada)' : ($nuevoStatus === "EN ESPERA DE CORRECCIONES" ? 'Actualizacion Status Revision (Solicitud de Correciones)' : 'Actualizacion de Revision');
        Historial::create([
            'trafico_id' => $trafico->id,
            'nombre' => $nombreHist,
            'descripcion' => "La Revision se encuentra en estado: $nuevoStatus.",
            'hora' => Carbon::now('America/Los_Angeles'),
        ]);

        return Redirect::route('revisiones.index')->with('success', 'Revisione updated successfully');
    }
        

        public function destroy($id): RedirectResponse
        {
            Revisione::find($id)->delete();

            return Redirect::route('revisiones.index')
                ->with('success', 'Revisione deleted successfully');
        }
    


    private function enviarNotificacionRevision(Trafico $trafico, string $status): void
{
    try {
        $emailExcluido = 'francisco@cesoftware.com.mx';
        $idExcluido    = 3;

        // 1) Empresa
        $emailsEmpresa = ($trafico->empresa && !empty($trafico->empresa->emailNotify))
            ? collect(explode(',', $trafico->empresa->emailNotify))
                ->map(fn($e) => trim($e))
                ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL) && $e !== $emailExcluido)
                ->toArray()
            : [];

        // 2) Documentadores y 3) Admins
        $emailsDocumentadores = User::role('documentador')->where('id', '!=', $idExcluido)->pluck('email')
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL) && $e !== $emailExcluido)->toArray();

        $emailsAdmins = User::role('admin')->where('id', '!=', $idExcluido)->pluck('email')
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL) && $e !== $emailExcluido)->toArray();

        // 4) Destinatarios unificados
        $to = array_values(array_unique(array_merge($emailsEmpresa, $emailsAdmins)));
        $cc = array_values(array_unique(array_merge($emailsDocumentadores, ['revisiones@agenciasai.com'])));

        if (!empty($to)) {
            // Usa el Mailable RevisionStatusMail que creamos antes
            Mail::to($to)->cc($cc)->send(new \App\Mail\RevisionStatusMail($trafico, $status));
        }

    } catch (\Throwable $e) {
        \Log::error("Error enviando correo de revisión: " . $e->getMessage());
    }
}



private function enviarNotificacionRevisionPrueba(Trafico $trafico, string $status): void
{
    try {
        // Enviamos a tu correo de prueba, pero usando la lógica de estatus
        Mail::to('osvaldo@rentasgmp.com')
            ->send(new \App\Mail\RevisionStatusMail($trafico, $status));


    } catch (\Throwable $e) {
        \Log::error('Error al enviar correo de PRUEBA REVISIÓN', [
            'error'      => $e->getMessage(),
            'trafico_id' => $trafico->id ?? null,
            'status'     => $status
        ]);
    }
}


}

