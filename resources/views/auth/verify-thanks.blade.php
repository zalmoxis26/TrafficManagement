@extends('layouts.app')

@section('title', 'Tracking SAI | Verificación recibida')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow border-0 rounded-3">
        <div class="card-header bg-dark text-white fw-bold d-flex align-items-center gap-2">
          🛡️✅
          <span>Verificación de correo recibida</span>
        </div>

        <div class="card-body">
          <p class="mb-3">
            ¡Gracias! Hemos recibido la verificación de tu correo. Un administrador de <strong>Tracking SAI</strong> revisará tu solicitud y, si procede, aprobará tu cuenta.
          </p>

          <div class="alert alert-info d-flex align-items-center gap-2" role="alert">
            ⏳ <span>Esto puede tomar un poco de tiempo. Te avisaremos por correo cuando haya una respuesta.</span>
          </div>

          <hr class="my-4">

          <div class="d-flex gap-2">
            <a href="{{ route('login') }}" class="btn btn-primary">
              Volver al inicio de sesión
            </a>
          </div>

          <p class="small text-muted mt-3 mb-0">
            💡 Consejo: revisa tu carpeta de <strong>Spam/Correo no deseado</strong> por si nuestra respuesta cae ahí.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
