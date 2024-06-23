<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="cliente_id" class="form-label">{{ __('Cliente Id') }}</label>
            <input type="text" name="cliente_id" class="form-control @error('cliente_id') is-invalid @enderror" value="{{ old('cliente_id', $trafico?->cliente_id) }}" id="cliente_id" placeholder="Cliente Id">
            {!! $errors->first('cliente_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="operacion" class="form-label">{{ __('Operacion') }}</label>
            <input type="text" name="operacion" class="form-control @error('operacion') is-invalid @enderror" value="{{ old('operacion', $trafico?->operacion) }}" id="operacion" placeholder="Operacion">
            {!! $errors->first('operacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="folio_transporte" class="form-label">{{ __('Foliotransporte') }}</label>
            <input type="text" name="folioTransporte" class="form-control @error('folioTransporte') is-invalid @enderror" value="{{ old('folioTransporte', $trafico?->folioTransporte) }}" id="folio_transporte" placeholder="Foliotransporte">
            {!! $errors->first('folioTransporte', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_reg" class="form-label">{{ __('Fechareg') }}</label>
            <input type="text" name="fechaReg" class="form-control @error('fechaReg') is-invalid @enderror" value="{{ old('fechaReg', $trafico?->fechaReg) }}" id="fecha_reg" placeholder="Fechareg">
            {!! $errors->first('fechaReg', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="toperacion" class="form-label">{{ __('Toperacion') }}</label>
            <input type="text" name="Toperacion" class="form-control @error('Toperacion') is-invalid @enderror" value="{{ old('Toperacion', $trafico?->Toperacion) }}" id="toperacion" placeholder="Toperacion">
            {!! $errors->first('Toperacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="factura" class="form-label">{{ __('Factura') }}</label>
            <input type="text" name="factura" class="form-control @error('factura') is-invalid @enderror" value="{{ old('factura', $trafico?->factura) }}" id="factura" placeholder="Factura">
            {!! $errors->first('factura', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="clave_ped" class="form-label">{{ __('Claveped') }}</label>
            <input type="text" name="clavePed" class="form-control @error('clavePed') is-invalid @enderror" value="{{ old('clavePed', $trafico?->clavePed) }}" id="clave_ped" placeholder="Claveped">
            {!! $errors->first('clavePed', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="us_docs" class="form-label">{{ __('Usdocs') }}</label>
            <input type="text" name="usDocs" class="form-control @error('usDocs') is-invalid @enderror" value="{{ old('usDocs', $trafico?->usDocs) }}" id="us_docs" placeholder="Usdocs">
            {!! $errors->first('usDocs', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="revision" class="form-label">{{ __('Revision') }}</label>
            <input type="text" name="Revision" class="form-control @error('Revision') is-invalid @enderror" value="{{ old('Revision', $trafico?->Revision) }}" id="revision" placeholder="Revision">
            {!! $errors->first('Revision', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="transporte" class="form-label">{{ __('Transporte') }}</label>
            <input type="text" name="Transporte" class="form-control @error('Transporte') is-invalid @enderror" value="{{ old('Transporte', $trafico?->Transporte) }}" id="transporte" placeholder="Transporte">
            {!! $errors->first('Transporte', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="clasificacion" class="form-label">{{ __('Clasificacion') }}</label>
            <input type="text" name="Clasificacion" class="form-control @error('Clasificacion') is-invalid @enderror" value="{{ old('Clasificacion', $trafico?->Clasificacion) }}" id="clasificacion" placeholder="Clasificacion">
            {!! $errors->first('Clasificacion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="odt" class="form-label">{{ __('Odt') }}</label>
            <input type="text" name="Odt" class="form-control @error('Odt') is-invalid @enderror" value="{{ old('Odt', $trafico?->Odt) }}" id="odt" placeholder="Odt">
            {!! $errors->first('Odt', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>