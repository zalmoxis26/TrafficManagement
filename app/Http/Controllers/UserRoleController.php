<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{

    public function index()

    {
        $users = User::whereHas('roles')->with('roles')->orderBy('created_at', 'DESC')->get();

    

        return view('roles.index', compact('users'));
    }


    public function create()
    {
        $users = User::doesntHave('roles')->get();

        $roles = Role::all();
        return view('roles.asignarRol', compact('users', 'roles'));
    }

    public function update(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->assignRole($request->role_id);
        
        return redirect()->route('roles.index')->with('success', 'Role assigned successfully');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('roles.edit', compact('user', 'roles'));
    }

        public function updateRoles(Request $request, User $user)
    {
        // Eliminar todos los roles actuales del usuario
        DB::table('model_has_roles')->where('model_id', $user->id)->delete();

        // Asignar el nuevo rol seleccionado
        $user->assignRole($request->roles);

        return redirect()->route('roles.index')->with('success', 'Roles actualizados correctamente.');
    }

    public function deleteRole( $user_id)
    {
        
        // Eliminar el rol específico del usuario
        DB::table('model_has_roles')
            ->where('model_id', $user_id)
            ->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }


    

}