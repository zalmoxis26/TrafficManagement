@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-7">
      <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
        
        {{-- Header --}}
        <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
          ⏳ <span class="fw-semibold">Cuenta pendiente de aprobación</span>
        </div>

        {{-- Body --}}
        <div class="card-body p-4 p-lg-5">

          <div class="d-flex align-items-start gap-3 mb-4">
            <div class="display-6 text-danger">🚫</div>
            <div>
              <h5 class="fw-bold mb-1">Aún no puedes acceder</h5>
              <p class="mb-0 text-muted">
                Tu cuenta está en revisión por un administrador.  
                Te notificaremos por correo cuando sea aprobada ✅
              </p>
            </div>
          </div>

          <div class="bg-light border rounded-3 p-4 mb-4">
            <h6 class="fw-semibold mb-3">📋 Detalles de tu cuenta</h6>
            <ul class="list-unstyled mb-0">
              <li class="mb-2">
                <span class="fw-semibold">👤 Usuario:</span> {{ auth()->user()->name }}
              </li>
              <li class="mb-2">
                <span class="fw-semibold">📧 Correo:</span> 
                <span class="badge text-bg-light fs-6">{{ auth()->user()->email }}</span>
              </li>
              <li>
                <span class="fw-semibold">⚠️ Estado:</span> 
                <span class="badge rounded-pill text-bg-warning px-3 py-1 fs-6">Pendiente de aprobación</span>
              </li>
            </ul>
          </div>

          <div class="text-center">
            <a href="{{ route('logout') }}"
               class="btn btn-outline-secondary px-4"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              🚪 Cerrar sesión
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
          </div>

        </div>

        {{-- Footer --}}
        <div class="card-footer bg-light text-center small text-muted">
          Si crees que esto es un error, por favor <strong>contacta al administrador</strong>.
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
