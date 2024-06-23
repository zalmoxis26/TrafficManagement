<div class="row padding-1 p-1 justify-content-center">
    <div class="col-md-3">
        <div class="form-group mb-2 mb20">
            <label for="clave" class="form-label">{{ __('CLAVE DE LA EMPRESA') }}</label>
            <input type="text" name="clave" class="form-control @error('clave') is-invalid @enderror" value="{{ old('clave', $empresa?->clave) }}" id="clave" placeholder="Clave">
            {!! $errors->first('clave', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
    </div>
    <div class="col-6">
        <div class="form-group mb-2 mb20">
            <label for="descripcion" class="form-label">{{ __('NOMBRE DE LA EMPRESA') }}</label>
            <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $empresa?->descripcion) }}" id="descripcion" placeholder="Nombre de la Empresa" required>
            {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>   
    </div>
</div>


<div class="row padding-1 p-1 mt-3 justify-content-center">
    <div class="col-6">
        <div class="form-group mb-2 mb20">
            <label for="empresaMatriz" class="form-label">{{ __('NOMBRE EMPRESA MATRIZ') }}</label>
            <input type="text" name="empresaMatriz" class="form-control @error('empresaMatriz') is-invalid @enderror" value="{{ $empresa->empresaMatriz ? $empresa->empresaMatriz : 'MISMA' }}" id="empresaMatriz" placeholder="Nombre de la Empresa Matriz" required>
            {!! $errors->first('empresaMatriz', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>   
    </div>
    
    <div class="col-md-3">
        <div class="form-group mb-2 mb20">
            <label for="rfc" class="form-label">{{ __('RFC') }}</label>
            <input type="text" name="rfc" class="form-control @error('rfc') is-invalid @enderror" value="{{ old('rfc', $empresa?->rfc) }}" id="rfc" placeholder="Rfc">
            {!! $errors->first('rfc', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
    </div>    
</div>

<div class="row padding-1 p-1">
    <div class="col-md-12 mt20 mt-2 text-end">
        <button type="submit" class="btn btn-primary">{{ __('Actualizar') }}</button>
    </div>
</div>