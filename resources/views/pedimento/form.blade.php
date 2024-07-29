<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="num_pedimento" class="form-label">{{ __('Numpedimento') }}</label>
            <input type="text" name="numPedimento" class="form-control @error('numPedimento') is-invalid @enderror" value="{{ old('numPedimento', $pedimento?->numPedimento) }}" id="num_pedimento" placeholder="Numpedimento">
            {!! $errors->first('numPedimento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="aduana" class="form-label">{{ __('Aduana') }}</label>
            <input type="text" name="aduana" class="form-control @error('aduana') is-invalid @enderror" value="{{ old('aduana', $pedimento?->aduana) }}" id="aduana" placeholder="Aduana">
            {!! $errors->first('aduana', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="patente" class="form-label">{{ __('Patente') }}</label>
            <input type="text" name="patente" class="form-control @error('patente') is-invalid @enderror" value="{{ old('patente', $pedimento?->patente) }}" id="patente" placeholder="Patente">
            {!! $errors->first('patente', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="clave_ped" class="form-label">{{ __('Claveped') }}</label>
            <input type="text" name="clavePed" class="form-control @error('clavePed') is-invalid @enderror" value="{{ old('clavePed', $pedimento?->clavePed) }}" id="clave_ped" placeholder="Clave Pedimento">
            {!! $errors->first('clavePed', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fecha_ped" class="form-label">{{ __('Fechaped') }}</label>
            <input type="text" name="fechaPed" class="form-control @error('fechaPed') is-invalid @enderror" value="{{ old('fechaPed', $pedimento?->fechaPed) }}" id="fecha_ped" placeholder="Fechaped">
            {!! $errors->first('fechaPed', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="adjunto" class="form-label">{{ __('Adjunto') }}</label>
            <input type="text" name="adjunto" class="form-control @error('adjunto') is-invalid @enderror" value="{{ old('adjunto', $pedimento?->adjunto) }}" id="adjunto" placeholder="Adjunto">
            {!! $errors->first('adjunto', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>