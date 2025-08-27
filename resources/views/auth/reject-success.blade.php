@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow border-0 rounded-3 text-center p-4">
        <div class="card-body">
          <div class="mb-4">
            <span style="font-size:3rem;">❌</span>
          </div>
          <h3 class="fw-bold text-danger">Solicitud rechazada</h3>
          <p class="mt-3 text-muted">
            La solicitud de registro fue rechazada correctamente.<br>
            El usuario no ha sido creado en el sistema.
          </p>
          <div class="mt-4">
            <a href="{{ route('inicio') }}" class="btn btn-outline-secondary px-4">
              Ir a Inicio.
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
