@component('mail::message')
# 🛡️ Hola {{ $name }}

Gracias por registrarte en **Tracking SAI**.  
Antes de continuar, por favor verifica tu dirección de correo electrónico haciendo clic en el siguiente botón:

@component('mail::button', ['url' => $verifyUrl, 'color' => 'success'])
✅ Verificar correo electrónico
@endcomponent

---

**¿No solicitaste esta verificación?**  
Si no iniciaste este proceso, puedes ignorar este correo de forma segura.

> 💡 Consejo: revisa también tu carpeta de **Spam o Correo no deseado** si no ves el mensaje en tu bandeja principal.

Gracias,<br>
**El equipo de Tracking SAI**
@endcomponent
