<div class="row padding-1 p-1 bg-light">
    <div class="col-md-6 mx-auto">
        
       <!-- Lista de Usuarios -->
    <div class="form-group mb-2 mb-5">
        <label for="user_id" class="form-label">{{ __('Lista de Usuarios') }}</label>
        <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror">
            <option value="">{{ __('-- Selecciona Usuario --') }}</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('user_id', $usersEmpresa?->user_id) == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
        @error('user_id')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>


    <!-- Lista para Asignar por Empresas -->
    <div class="form-group mb-2 mb-5">
        <label for="empresa_id" class="form-label">
            <input class="form-check-input" type="radio" name="option" value="unica" onchange="toggleSelect(this)"> {{ __('Lista para Asignar por Proveedor') }}
        </label>
        <select id="empresa_id" name="empresa_id" class="form-control @error('empresa_id') is-invalid @enderror" disabled>
            <option value="" disabled selected>-- Selecciona un Proveedor --</option>   
            <option value="TODOS">
                *ASIGNAR TODOS LOS PROVEEDORES*
            </option>
            @foreach($empresas as $empresa)
                
                <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>
                    {{ $empresa->descripcion }}
                </option>

            @endforeach
        </select>
        @error('empresa_id')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>
    
    <!-- Lista para Asignar por Empresa Matriz -->
    <div class="form-group mb-2 mb-4">
        <label for="matriz" class="form-label">
            <input class="form-check-input" type="radio" name="option" value="matriz" onchange="toggleSelect(this)"> {{ __('Lista para Asignar por Cliente') }}
        </label>
        @error('option')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
        <select id="matriz" name="matriz" class="form-control @error('matriz') is-invalid @enderror" disabled>
            <option value="">-- Selecciona un Cliente --</option>
            @foreach($empresasAgrupadasPorMatriz as $empresaMatriz)
                <option value="{{ $empresaMatriz->empresaMatriz }}" {{ old('matriz') == $empresaMatriz->empresaMatriz ? 'selected' : '' }}>
                    {{ $empresaMatriz->empresaMatriz }}
                </option>
            @endforeach
        </select>
        @error('matriz')
            <div class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </div>
        @enderror
    </div>
    </div>
    <div class="col-md-10 mt20 mt-2 text-end">
        <button type="submit" class="btn btn-primary">{{ __('Asignar') }}</button>
    </div>
</div>

<script>
    function toggleSelect(radio) {
        // Disable all selects
        document.getElementById('empresa_id').disabled = true;
        document.getElementById('matriz').disabled = true;
    
        // Enable the selected one
        if (radio.value == "unica") {
            document.getElementById('empresa_id').disabled = false;
        } else if (radio.value == "matriz") {
            document.getElementById('matriz').disabled = false;
        }
    }
    </script>