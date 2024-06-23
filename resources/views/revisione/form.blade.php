<div class="row padding-1 p-1">
    <div class="col-md-9">
        
        <div class="form-group mb-2 mb20">
            <label for="nombre_revisor" class="form-label">{{ __('Nombrerevisor') }}</label>
            <input type="text" name="nombreRevisor" class="form-control @error('nombreRevisor') is-invalid @enderror" value="{{ old('nombreRevisor', $revisione?->nombreRevisor) }}" id="nombre_revisor" placeholder="Nombrerevisor">
            {!! $errors->first('nombreRevisor', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="inicio_revision" class="form-label">{{ __('Iniciorevision') }}</label>
            <input type="text" name="inicioRevision" class="form-control @error('inicioRevision') is-invalid @enderror" value="{{ old('inicioRevision', $revisione?->inicioRevision) }}" id="inicio_revision" placeholder="Iniciorevision">
            {!! $errors->first('inicioRevision', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="fin_revision" class="form-label">{{ __('Finrevision') }}</label>
            <input type="text" name="finRevision" class="form-control @error('finRevision') is-invalid @enderror" value="{{ old('finRevision', $revisione?->finRevision) }}" id="fin_revision" placeholder="Finrevision">
            {!! $errors->first('finRevision', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tiempo_revision" class="form-label">{{ __('Tiemporevision') }}</label>
            <input type="text" name="tiempoRevision" class="form-control @error('tiempoRevision') is-invalid @enderror" value="{{ old('tiempoRevision', $revisione?->tiempoRevision) }}" id="tiempo_revision" placeholder="Tiemporevision">
            {!! $errors->first('tiempoRevision', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>