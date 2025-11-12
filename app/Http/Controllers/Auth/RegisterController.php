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
use App\Notifications\NewUserRegisteredAdmin; // notificación al admin
use App\Notifications\UserApprovedNotification; // notificación al usuario aprobado

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


    // 1) Guarda en CACHE y envía enlace de verificación al usuario (no se toca la BD)

    public function store(Request $request)
    {
        
        // 🚫 1 intento por email por hora
        $email = strtolower($request->email);
        if (Cache::has("lock:register:$email")) {
            return back()->withErrors([
                'email' => 'Ya se ha enviado una solicitud para este correo. Intenta nuevamente en 1 hora.',
            ])->withInput();
        }

        // 🔐 Bloquear por 1 hora
        Cache::put("lock:register:$email", true, now()->addHour());


        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:users,email'],
            'password' => [
                'required','string','min:8','confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
        ], [
            'password.regex' => 'La contraseña debe incluir al menos una letra mayúscula, un número y un carácter especial.',
        ]);

    


        $token = Str::random(64);

        Cache::put("reg:$token", [
            'name'          => $request->name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'created_at'    => now(),
        ], now()->addHours(8)); // TTL 8h

        // Link de verificación (propio) válido 8h
        $verifyUrl = URL::temporarySignedRoute('register.verify', now()->addHours(8), ['token' => $token]);

        // ENVÍA al usuario (no al admin todavía)
        Notification::route('mail', $request->email)
            ->notify(new \App\Notifications\VerifyRegistrationEmail($verifyUrl, $request->name));

        return back()->with('status', 'Te enviamos un correo para confirmar tu email. Revisa tu bandeja de entrada.');
    }



    // 2) El usuario verifica su correo → ahora sí se notifica al admin


    public function verify(Request $request, string $token)
    {
        $data = Cache::get("reg:$token");
        if (!$data) {
            return redirect()->route('login')->withErrors('El enlace expiró o es inválido.');
        }

        // Marca verificado (flag en cache) y evita duplicados
        Cache::put("reg:$token:verified", true, now()->addHours(8));

        if (!Cache::get("reg:$token:adminNotified")) {
            $approveUrl = URL::temporarySignedRoute('admin.approve', now()->addHours(8), ['token' => $token]);
            $rejectUrl  = URL::temporarySignedRoute('admin.reject',  now()->addHours(8), ['token' => $token]);

            Notification::route('mail', 'francisco@cesoftware.com.mx')
                ->notify(new \App\Notifications\NewUserRegisteredAdmin(
                    $approveUrl,
                    $rejectUrl,
                    $data['name'],
                    $data['email']
                ));

            Cache::put("reg:$token:adminNotified", true, now()->addHours(8));
        }

        return view('auth.verify-thanks'); // o redirect con flash
    }



    /**
     * El admin aprueba desde el enlace (firmado). Se crea el usuario, se marca aprobado y se envía verificación de correo.
     */
    
      
    public function approve(Request $request, string $token)
    {
        if (!Cache::get("reg:$token:verified")) {
            return redirect()->route('login')->withErrors('Primero el usuario debe confirmar su correo.');
        }

        $data = Cache::pull("reg:$token"); // consume el payload
        if (!$data) {
            return redirect()->route('login')->withErrors('El enlace expiró o ya fue utilizado.');
        }

        // Limpieza de flags
        Cache::forget("reg:$token:verified");
        Cache::forget("reg:$token:adminNotified");

        // Crear usuario real y marcarlo como YA verificado (porque pasó por tu verify)
        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => $data['password_hash'],
            'approved_at'       => now(),
            'email_verified_at' => now(), // ya verificó vía tu enlace
        ]);


        // URL del botón (ruta index de traficos)
        $traficosUrl = route('traficos.index');

        // Notificar al usuario que fue aprobado
        $user->notify(new UserApprovedNotification($user->name, $traficosUrl));



        // Rol por defecto
        if ($role = Role::where('name','guest')->first()) {
            $user->assignRole($role);
        }

        $roles = Role::all();
        $setRoleUrl = URL::temporarySignedRoute('admin.registration.setRole', now()->addMinutes(60), ['user' => $user->id]);

        return view('auth.approve-success', compact('user', 'roles', 'setRoleUrl'));
    }

    // El admin RECHAZA → se borra todo de cache (nunca se creó usuario)
    

    public function reject(Request $request, string $token)
    {
        Cache::forget("reg:$token");
        Cache::forget("reg:$token:verified");
        Cache::forget("reg:$token:adminNotified");

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
