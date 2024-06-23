<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el usuario administrador existente
        $admin = User::findOrFail(3);

        // Asignar el rol "admin" al usuario administrador (si no lo tiene)
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Crear y asignar roles a otros usuarios
        $revisor = User::create([
            'name' => 'Revisor',
            'email' => 'revisor@example.com',
            'password' => Hash::make('revisor123'), // Contraseña específica para el revisor
        ]);
        $revisor->assignRole('revisor');

        $documentador = User::create([
            'name' => 'Documentador',
            'email' => 'documentador@example.com',
            'password' => Hash::make('documentador123'), // Contraseña específica para el documentador
        ]);
        $documentador->assignRole('documentador');

        $cliente = User::create([
            'name' => 'Cliente',
            'email' => 'cliente@example.com',
            'password' => Hash::make('cliente123'), // Contraseña específica para el cliente
        ]);
        $cliente->assignRole('cliente');
    
    }
}
