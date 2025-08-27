@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow border-0 rounded-3">
        <div class="card-header fw-bold d-flex align-items-center gap-2">
          📝 Solicitud de registro
        </div>

        <div class="card-body">
          {{-- Flash de estado (éxito al enviar solicitud) --}}
          @if (session('status'))
            <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
              ✅ <span>{{ session('status') }}</span>
            </div>
          @endif

          {{-- Errores globales (por si llega alguno que no sea de campo) --}}
          @if ($errors->any())
            <div class="alert alert-danger">
              <strong>Revisa los campos:</strong>
              <ul class="mb-0">
                @foreach ($errors->all() as $e)
                  <li>{{ $e }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form id="register-form" method="POST" action="{{ route('register.request') }}">
            @csrf

            <div class="row mb-3">
              <label for="name" class="col-md-4 col-form-label text-md-end">Nombre</label>
              <div class="col-md-6">
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                       name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                @error('name') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="email" class="col-md-4 col-form-label text-md-end">Correo</label>
              <div class="col-md-6">
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                       name="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email') <span class="invalid-feedback"><strong>{{ $message }}</strong></span> @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="password" class="col-md-4 col-form-label text-md-end">Contraseña</label>
              <div class="col-md-6">
                <div class="input-group">
                  <input id="password" type="password"
                         class="form-control @error('password') is-invalid @enderror"
                         name="password" required autocomplete="new-password" aria-describedby="togglePwd">
                  <button class="btn btn-outline-secondary" type="button" id="togglePwd" tabindex="-1">👁️</button>
                </div>
                @error('password') <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span> @enderror

                <div class="form-text mt-2">
                  Debe cumplir:
                  <ul class="mb-1" id="pw-reqs">
                    <li id="req-len">Mínimo 8 caracteres</li>
                    <li id="req-upper">Al menos 1 mayúscula (A-Z)</li>
                    <li id="req-num">Al menos 1 número (0-9)</li>
                    <li id="req-special">Al menos 1 caracter especial (p. ej. !@#$%)</li>
                  </ul>
                  <div class="progress" style="height: 6px;">
                    <div id="pw-strength" class="progress-bar" role="progressbar" style="width: 0%"
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                  <small class="text-muted">Ejemplo válido: <code>Empresa2024!</code></small>
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <label for="password-confirm" class="col-md-4 col-form-label text-md-end">Confirmar contraseña</label>
              <div class="col-md-6">
                <input id="password-confirm" type="password" class="form-control"
                       name="password_confirmation" required autocomplete="new-password">
                <div id="pw-match-hint" class="form-text"></div>
              </div>
            </div>

            <div class="row mb-0">
              <div class="col-md-6 offset-md-4">
               <button id="submit-btn" type="submit" class="btn btn-primary" disabled>
                <span id="btn-text">Enviar solicitud al administrador</span>
                <span id="btn-spinner" class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                </button>

                <div class="form-text mt-2">
                  Te avisaremos por correo cuando un administrador apruebe tu cuenta.
                </div>
              </div>
            </div>
          </form>
        </div>



        

      </div>
    </div>
  </div>
</div>

{{-- UX: checklist, fuerza, mostrar/ocultar, habilitar botón --}}
<script>

(function(){
  const $pw  = document.getElementById('password');
  const $pc  = document.getElementById('password-confirm');
  const $btn = document.getElementById('submit-btn');
  const $bar = document.getElementById('pw-strength');
  const $hint= document.getElementById('pw-match-hint');
  const $toggle = document.getElementById('togglePwd');

  const req = {
    len: document.getElementById('req-len'),
    upper: document.getElementById('req-upper'),
    num: document.getElementById('req-num'),
    special: document.getElementById('req-special'),
  };
  const ok  = el => { el.classList.add('text-success'); el.classList.remove('text-danger'); };
  const bad = el => { el.classList.add('text-danger'); el.classList.remove('text-success'); };

  function checkPw(v){
    const r = {
      len: v.length >= 8,
      upper: /[A-Z]/.test(v),
      num: /\d/.test(v),
      special: /[\W_]/.test(v),
    };
    r.len ? ok(req.len) : bad(req.len);
    r.upper ? ok(req.upper) : bad(req.upper);
    r.num ? ok(req.num) : bad(req.num);
    r.special ? ok(req.special) : bad(req.special);

    // fuerza simple: 0..4 -> 0..100
    const score = Object.values(r).filter(Boolean).length;
    const pct = (score/4)*100;
    $bar.style.width = pct + '%';
    $bar.classList.remove('bg-danger','bg-warning','bg-success');
    if (pct < 50) $bar.classList.add('bg-danger');
    else if (pct < 100) $bar.classList.add('bg-warning');
    else $bar.classList.add('bg-success');

    return r;
  }

  function checkMatch(){
    const match = $pw.value && $pc.value && $pw.value === $pc.value;
    $hint.textContent = match ? 'Las contraseñas coinciden.' : ($pc.value ? 'Las contraseñas no coinciden.' : '');
    $hint.classList.toggle('text-success', !!match);
    $hint.classList.toggle('text-danger', !match && !!$pc.value);
    return match;
  }

  function canSubmit(){
    const r = checkPw($pw.value);
    const allReq = r.len && r.upper && r.num && r.special;
    const match = checkMatch();
    $btn.disabled = !(allReq && match);
  }

  $pw.addEventListener('input', canSubmit);
  $pc.addEventListener('input', canSubmit);

  $toggle.addEventListener('click', function(){
    const type = $pw.getAttribute('type') === 'password' ? 'text' : 'password';
    $pw.setAttribute('type', type);
    $toggle.textContent = (type === 'password') ? '👁️' : '🙈';
  });

  // init
  canSubmit();
})();

const $form = document.getElementById('register-form');
$form.addEventListener('submit', function() {
  $btn.disabled = true;
  document.getElementById('btn-text').textContent = 'Enviando...';
  document.getElementById('btn-spinner').classList.remove('d-none');
});




</script>
@endsection
