@component('mail::message')
# 🎉 Hola {{ $name }}

Tu solicitud para acceder a **Tracking SAI** ha sido **aprobada** ✅

Ya puedes ingresar al sistema y comenzar a gestionar tus tráficos, remisiones y registros.

@component('mail::button', ['url' => $url, 'color' => 'success'])
🚀 Ir al panel de Tráficos
@endcomponent

Gracias por formar parte de nuestra plataforma.<br>
**El equipo de Tracking SAI**
@endcomponent
