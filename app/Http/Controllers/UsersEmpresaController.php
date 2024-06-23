<?php

namespace App\Http\Controllers;

use App\Models\UsersEmpresa;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\UsersEmpresaRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Importar Auth

class UsersEmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $usersEmpresas = UsersEmpresa::orderBy('user_id','DESC')->paginate();
        $currentUser = Auth::user();

        return view('users-empresa.index', compact('usersEmpresas','currentUser'))
            ->with('i', ($request->input('page', 1) - 1) * $usersEmpresas->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        
        $usersEmpresa = new UsersEmpresa();
            // Obtener el usuario autenticado
        $users = User::all();
        $empresas = Empresa::all();
        $empresasAgrupadasPorMatriz = DB::table('empresas')
        ->select('empresaMatriz')
        ->groupBy('empresaMatriz')
        ->get();        

        return view('users-empresa.create', compact('usersEmpresa','empresas','users','empresasAgrupadasPorMatriz'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    
        // Validar los datos recibidos
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'option' => 'required|in:unica,matriz',
        'empresa_id' => 'required_if:option,unica',
        'matriz' => 'required_if:option,matriz|exists:empresas,empresaMatriz',
    ]);



    $empresaId = $request->input('empresa_id');

  

    if ($request->input('option') == 'unica') {
      // SI EN UNICA SELECCIONA TODOS SE HACE ASIGNACION MASIVA
        if ($empresaId === 'TODOS') {
            // Obtener todos los IDs de las empresas
            $IdsEmpresas = Empresa::pluck('id');
    
            // Asignar todas las empresas al usuario
            foreach ($IdsEmpresas as $id) {
                UsersEmpresa::firstOrCreate([
                    'user_id' => $request->input('user_id'),
                    'empresa_id' => $id,
                ]);
            }
        }else{
              // Opción 'unica': Crear un registro directo
        
            $empresa_id = $request->input('empresa_id');
            UsersEmpresa::firstOrCreate([
                'user_id' => $request->input('user_id'),
                'empresa_id' => $empresa_id,
            ]);
        }
       
    } elseif ($request->input('option') == 'matriz') {
        // Opción 'matriz': Asignación masiva

        // 1. Buscar la empresa matriz
        $matriz = $request->input('matriz');
        $empresasConMatriz = Empresa::where('empresaMatriz', $matriz)->get();

        // 2. Obtener todos los ids de las empresas que tienen esa empresaMatriz
        $empresaIds = $empresasConMatriz->pluck('id')->toArray();

        // 5. Asignar todas las empresas de esos usuarios al nuevo usuario
            foreach ($empresaIds as $empresaId) {
                UsersEmpresa::firstOrCreate([
                    'user_id' => $request->input('user_id'),
                    'empresa_id' => $empresaId,
                ]);
            }
    }

    // Redirigir y mostrar mensaje de éxito
    return Redirect::route('users-empresas.index')
        ->with('success', 'UsersEmpresa created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $usersEmpresa = UsersEmpresa::find($id);

        return view('users-empresa.show', compact('usersEmpresa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $usersEmpresa = UsersEmpresa::find($id);
        $currentUser = Auth::user();
        $empresas = Empresa::all();
        $users = User::all();

        return view('users-empresa.edit', compact('usersEmpresa','currentUser','empresas','users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UsersEmpresaRequest $request, UsersEmpresa $usersEmpresa): RedirectResponse
    {
        $usersEmpresa->update($request->validated());

        return Redirect::route('users-empresas.index')
            ->with('success', 'UsersEmpresa updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        UsersEmpresa::find($id)->delete();

        return Redirect::route('users-empresas.index')
            ->with('success', 'UsersEmpresa deleted successfully');
    }
}
