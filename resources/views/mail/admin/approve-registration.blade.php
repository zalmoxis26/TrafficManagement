@component('mail::message')
# Nueva solicitud de registro

Tienes una nueva solicitud para crear una cuenta en el sistema.

@component('mail::panel')
**Nombre:** {{ $name }}  
**Correo:** {{ $email }}
@endcomponent

@component('mail::subcopy')
Estos enlaces caducan en **{{ $expires }}** por seguridad.
@endcomponent

@component('mail::button', ['url' => $approveUrl, 'color' => 'success'])
✅ Aprobar usuario
@endcomponent

@component('mail::button', ['url' => $rejectUrl, 'color' => 'error'])
❌ Rechazar solicitud
@endcomponent

---

Si los botones no funcionan, copia y pega estas URL en tu navegador:

**Aprobar:**  
{{ $approveUrl }}

**Rechazar:**  
{{ $rejectUrl }}

Gracias,  
{{ config('app.name') }}
@endcomponent
