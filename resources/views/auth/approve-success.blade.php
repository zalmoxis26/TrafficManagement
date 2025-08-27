@extends('layouts.app')

@section('content')
<div class="container py-5">
  {{-- Toast flotante arriba/derecha --}}
  <div class="position-fixed top-0 end-0 p-3" style="z-index:1080">
    @if(session('status') || isset($status))
      <div id="roleToast" class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            {{ session('status') ?? $status }}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    @endif
  </div>

  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow border-0 rounded-3">
        <div class="card-body text-center">
          <div class="mb-3"><span style="font-size:3rem;">🎉</span></div>
          <h3 class="fw-bold text-success">Usuario aprobado con éxito</h3>
          <p class="mt-2 text-muted">
            El usuario ha sido creado correctamente en el sistema.<br>
            Se envió un correo de <strong>verificación</strong> al email para completar su acceso.
          </p>

          <hr class="my-4">

          {{-- Formulario para editar rol (usa el link firmado que recibes en $setRoleUrl) --}}
          <form action="{{ $setRoleUrl }}" method="POST" class="text-start">
            @csrf
            <div class="mb-3">
              <label for="role" class="form-label fw-bold">Asignar rol</label>
              <select name="role" id="role" class="form-select">
                @foreach($roles as $role)
                  <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                    {{ ucfirst($role->name) }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="d-flex justify-content-between">
              <a href="{{ route('login') }}" class="btn btn-outline-secondary">Volver al login</a>
              <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Auto-cierre del toast (4s) si Bootstrap JS está cargado --}}
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('roleToast');
    if (el && window.bootstrap && bootstrap.Toast) {
      var t = new bootstrap.Toast(el, { delay: 4000 });
      t.show();
    }
  });
</script>
@endsection
