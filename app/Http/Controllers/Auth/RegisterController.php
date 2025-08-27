<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewUserRegisteredAdmin; // 👈 la creamos abajo

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                // Al menos 1 mayúscula, 1 número y 1 carácter especial
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
        ], [
            'password.regex' => 'La contraseña debe incluir al menos una letra mayúscula, un número y un carácter especial.',
            'password.min'   => 'La contraseña debe tener al menos 8 caracteres.',
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:users'],
            'password' => [
                'required','string','min:8','confirmed',
                // Al menos 1 mayúscula, 1 número y 1 carácter especial:
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
        ], [
            'password.regex' => 'La contraseña debe incluir al menos una letra mayúscula, un número y un carácter especial.',
        ]);

        // Token y payload (NUNCA guardes el password en claro)
        $token = Str::random(64);
        Cache::put("reg:$token", [
            'name'          => $request->name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
        ], now()->addMinutes(60)); // TTL 60 min (ajústalo)

        // Enlaces firmados y temporales
        $approveUrl = \URL::temporarySignedRoute('admin.approve', now()->addMinutes(60), ['token' => $token]);
        $rejectUrl  = \URL::temporarySignedRoute('admin.reject',  now()->addMinutes(60), ['token' => $token]);

        // Notifica al admin
        Notification::route('mail', 'francisco@cesoftware.com.mx')
           ->notify(new NewUserRegisteredAdmin($approveUrl, $rejectUrl, $request->name, $request->email));


        return back()->with('status', 'Solicitud enviada. Un administrador revisará tu registro.');
    }




    /**
     * El admin aprueba desde el enlace (firmado). Se crea el usuario, se marca aprobado y se envía verificación de correo.
     */
    public function approve(Request $request, string $token)
    {
        $data = Cache::pull("reg:$token"); // pull = get + forget (idempotente)
        if (!$data) {
            return redirect()->route('login')->withErrors('El enlace expiró o ya fue utilizado.');
        }

        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => $data['password_hash'],
            'approved_at' => now(),
        ]);

         //(Opcional) asignar rol por defecto guest
         if ($role = Role::where('name','guest')->first()) {
             $user->assignRole($role);
         }

        // Verificación de correo (el usuario aún debe verificar para entrar)
        $user->sendEmailVerificationNotification();

        // Alternativa: forzar definir nueva contraseña por email (en vez de usar la enviada):
        // \Password::sendResetLink(['email' => $user->email]);

         // Traemos todos los roles disponibles para poder editarlos
        $roles = Role::all();


        // URL firmada válida por 60 min
        $setRoleUrl = URL::temporarySignedRoute(
            'admin.registration.setRole',
            now()->addMinutes(60),
            ['user' => $user->id]
        );

        return view('auth.approve-success', compact('user', 'roles', 'setRoleUrl'));
    }

    /**
     * El admin rechaza la solicitud: eliminamos el token de cache.
     */


    public function reject(Request $request, string $token)
    {
        Cache::forget("reg:$token");
        return view('auth.reject-success');
    }


    public function setRole(Request $request, User $user)
    {
        // 1) Validar el rol recibido
        $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        // 2) Asignar rol (sobrescribe los anteriores)
        $user->syncRoles([$request->role]);

        // 3) Cargar todos los roles
        $roles = Role::all();

        // 4) Generar un nuevo link firmado válido por 60 min
        $setRoleUrl = URL::temporarySignedRoute(
            'admin.registration.setRole',
            now()->addMinutes(60),
            ['user' => $user->id]
        );

        // 5) Retornar la vista con el nuevo link
        return view('auth.approve-success', [
            'user'       => $user,
            'roles'      => $roles,
            'setRoleUrl' => $setRoleUrl, // 👈 ahora siempre es un link fresco
            'status'     => "✅ Rol actualizado a {$request->role}",
        ]);
    }



}
