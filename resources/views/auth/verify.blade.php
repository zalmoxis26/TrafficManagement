@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-dark text-white fw-bold d-flex align-items-center gap-2">
                    🛡️✅ 
                    <span>Verifica tu dirección de correo electrónico</span>
                </div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                            📧 <span>Se ha enviado un nuevo enlace de verificación a tu correo electrónico.</span>
                        </div>
                    @endif

                    <p class="mb-3">
                        Antes de continuar, por favor revisa tu bandeja de entrada y haz clic en el enlace de verificación.
                    </p>

                    <p class="mb-3 text-muted">
                        Si no has recibido el correo:
                    </p>

                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            🔄 Reenviar enlace de verificación
                        </button>
                    </form>

                    <hr class="my-4">

                    <p class="small text-muted mb-0">
                        💡 Consejo: revisa también tu carpeta de <strong>Spam/Correo no deseado</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
