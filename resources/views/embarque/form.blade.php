<div class="row padding-1 p-1">

    

        <div class="row mb-4">
            @if ($message = Session::get('success'))
                <div class="alert alert-success mb-2">
                    <p>{{ $message }}</p>
                </div>
            @endif
            <div class="col-md-5">
                <div class="form-group mb-3 mb20">
                    <label for="num_embarque" class="form-label">{{ __('#Embarque') }}</label>
                    <input type="text" name="numEmbarque" class="form-control" value="{{$embarque->numEmbarque}}" id="num_embarque" required>
                </div>
            </div>    

            <div class="col-md-4">
                <div class="form-group mb-3 mb20">
                    <label for="num_economico" class="form-label">{{ __('Numero Economico') }}</label>
                    <input type="text" name="numEconomico" class="form-control @error('numEconomico') is-invalid @enderror" value="{{ old('numEconomico', $embarque?->numEconomico) }}" id="num_economico" placeholder="Numeconomico">
                    {!! $errors->first('numEconomico', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
            </div>   

            <div class="col-md-3">
                <div class="form-group mb mb20">
                    <label for="fecha_embarque" class="form-label">{{ __('Fecha Embarque') }}</label>
                    <input type="datetime-local" name="fechaEmbarque" class="form-control @error('fechaEmbarque') is-invalid @enderror" value="{{ old('fechaEmbarque', $embarque?->fechaEmbarque) }}" id="fecha_embarque" placeholder="Fechaembarque">
                    {!! $errors->first('fechaEmbarque', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                </div>
            </div> 
        </div>   
        
<!-- SEGUNDA ROW -->
    <div class="row">    
        <div class="col-md-4">
            <div class="form-group mb-3 mb20">
                <label for="tipo_transporte" class="form-label">{{ __('Tipo de Transporte') }}</label>
                <input type="text" name="TipoDeTransporte" class="form-control" id="tipo_transporte" value="{{$embarque->TipoDeTransporte}}">
            </div>
        </div>   
        
        <div class="col-md-3">
            <div class="form-group mb-3 mb20">
                <label for="transporte" class="form-label">{{ __('Transporte') }}</label>
                <input type="text" name="Transporte" class="form-control" id="transporte" value="{{$embarque->Transporte}}" >
            </div>
        </div>   

        <div class="col-md-3">
            <div class="form-group mb-3 mb20">
                <label for="placas" class="form-label">{{ __('Placas') }}</label>
                <input type="text" name="Placas" class="form-control" id="placas"  maxlength="6" pattern="[A-Za-z0-9]{1,6}" value="{{$embarque->Placas}}">
            </div>
        </div>   

        
        <div class="col-md-2">
            <div class="form-group mb-3 mb20">
                <label for="caat" class="form-label">{{ __('CAAT') }}</label>
                <input type="text" name="Caat" class="form-control" id="caat" maxlength="4" pattern="[A-Za-z0-9]{1,4}" value="{{$embarque->Caat}}">
            </div>
        </div> 
    </div>    

<!-- tercera row -->
<div class="row mt-4 justify-content-center">
    <div class="col-md-2">
        <div class="form-group mb-3 mb20">
            <label for="chofer" class="form-label">{{ __('Chofer') }}</label>
            <input type="text" name="chofer" class="form-control" id="chofer" value="{{$embarque->chofer}}">
        </div>
    </div>   
   
    <div class="col-md-2">
        <div class="form-group mb-3 pt-4 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="documentosEntregados" name="entregaDocs" {{ $embarque->entregaDocs ? 'checked' : '' }}>
            <label class="form-check-label" for="documentosEntregados">{{ __('Documentos Entregados') }}</label>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group mb-3 pt-4 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="reconocimientoAduanero" name="rojoAduana" {{ $embarque->rojoAduana ? 'checked' : '' }}>
            <label class="form-check-label" for="reconocimientoAduanero">{{ __('Reconocimiento Aduanero(Rojo)') }}</label>
        </div>
    </div>
    <div class="col-md-2" id="andenField"  style="display:none;">
        <div class="form-group pt-1">
            <label for="anden" class="form-label">{{ __('Anden') }}</label>
            <input type="text" name="anden" class="form-control" id="anden" value="{{$embarque->anden}}">
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group mb-3 pt-4 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="modulado" name="modulado" {{ $embarque->modulado ? 'checked' : '' }}>
            <label class="form-check-label" for="modulado">{{ __('Modulado') }}</label>
        </div>
    </div>
</div>

            
            
<!-- CUARTA row -->
                                      
            


<!-- QUINTA ROW -->
      
        
<!-- SEXTA ROW -->                                     
    <div class="col-md-12 mt20 mt-2 text-end">
       @role('admin|documentador')
        @if($embarque->traficos->first()->statusTrafico === "ABIERTO")
        <button type="submit" class="btn btn-primary">{{ __('Actualizar') }}</button> 
        @else

        @endif
        @endrole
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const reconocimientoAduaneroSwitch = document.getElementById('reconocimientoAduanero');
        const andenField = document.getElementById('andenField');

        reconocimientoAduaneroSwitch.addEventListener('change', function () {
            if (this.checked) {
                andenField.style.display = '';
            } else {
                andenField.style.display = 'none';
            }
        });

        // Inicializar visibilidad en función del estado actual del interruptor
        if (reconocimientoAduaneroSwitch.checked) {
            andenField.style.display = '';
        } else {
            andenField.style.display = 'none';
        }
    });
</script>