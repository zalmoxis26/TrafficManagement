<div class="row padding-1 p-1 justify-content-center">
    <div class="col-md-3 col-12">
        <div class="form-group pt-2">
            <label for="clave" class="form-label">{{ __('CLAVE CLIENTE') }}</label>
            <input type="text" name="clave" class="form-control @error('clave') is-invalid @enderror" value="{{ old('clave', $empresa?->clave) }}" id="clave" placeholder="Clave">
            {!! $errors->first('clave', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-group mb-2 ">
            <label for="empresaMatriz" class="form-label">{{ __('NOMBRE DEL CLIENTE') }}</label>
            <input type="text" name="empresaMatriz" class="form-control @error('empresaMatriz') is-invalid @enderror" value="{{ $empresa->empresaMatriz }}" id="empresaMatriz" placeholder="Nombre del Cliente" required>
            {!! $errors->first('empresaMatriz', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>   
    </div> 
</div>

<div class="row padding-1 p-1 mt-3 mb-4 justify-content-center">
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label for="clave" class="form-label">{{ __('CLAVE PROVEEDOR') }}</label>
            <input type="text" name="claveProveedor" class="form-control @error('claveProveedor') is-invalid @enderror" value="{{ old('claveProveedor', $empresa?->claveProveedor) }}" id="claveProveedor" placeholder="Clave del Proveedor">
            {!! $errors->first('claveProveedor', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-group mb-2 mt-2">
            <label for="descripcion" class="form-label">{{ __('NOMBRE DEL PROVEEDOR') }}</label>
            <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $empresa?->descripcion) }}" id="descripcion" placeholder="Nombre de la Empresa" required>
            {!! $errors->first('descripcion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>   
    </div>     
</div>

<div class="row padding-1 p-1 mt-1 mb-5 justify-content-center">
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label for="clave" class="form-label">{{ __('PREFIJO FACTURA') }}</label>
            <input type="text" name="prefijoFactura" class="form-control @error('prefijoFactura') is-invalid @enderror" value="{{ old('prefijoFactura', $empresa?->prefijoFactura) }}" id="prefijoFactura" placeholder="prefijoFactura">
            {!! $errors->first('prefijoFactura', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
    </div>
    <div class="col-md-3 col-12">
        <div class="form-group">
            <label for="rfc" class="form-label">{{ __('RFC') }}</label>
            <input type="text" name="rfc" class="form-control @error('rfc') is-invalid @enderror" value="{{ old('rfc', $empresa?->rfc) }}" id="rfc" placeholder="Rfc">
            {!! $errors->first('rfc', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
    </div>    
</div>

<div class="row padding-1 p-1">
    <div class="col-md-12  mt-2 text-end">
        <button type="submit" class="btn btn-primary">{{ __('Actualizar') }}</button>
    </div>
</div>