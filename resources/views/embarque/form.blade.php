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
                     <div class="invalid-feedback" id="numEmbarqueError"></div>
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
                <select name="TipoDeTransporte" class="form-control" id="tipo_transporte">
                    <option value="">Seleccione un tipo de transporte</option>
                    <option value="1" {{ $embarque->TipoDeTransporte == '1' ? 'selected' : '' }}>CONTENEDOR ESTÁNDAR 20' (STANDARD CONTAINER 20')</option>
                    <option value="2" {{ $embarque->TipoDeTransporte == '2' ? 'selected' : '' }}>CONTENEDOR ESTÁNDAR 40' (STANDARD CONTAINER 40')</option>
                    <option value="3" {{ $embarque->TipoDeTransporte == '3' ? 'selected' : '' }}>CONTENEDOR ESTÁNDAR DE CUBO ALTO 40' (HIGH CUBE STANDARD CONTAINER 40')</option>
                    <option value="4" {{ $embarque->TipoDeTransporte == '4' ? 'selected' : '' }}>CONTENEDOR TAPA DURA 20' (HARDTOP CONTAINER 20')</option>
                    <option value="5" {{ $embarque->TipoDeTransporte == '5' ? 'selected' : '' }}>CONTENEDOR TAPA DURA 40' (HARDTOP CONTAINER 40')</option>
                    <option value="6" {{ $embarque->TipoDeTransporte == '6' ? 'selected' : '' }}>CONTENEDOR TAPA ABIERTA 20' (OPEN TOP CONTAINER 20')</option>
                    <option value="7" {{ $embarque->TipoDeTransporte == '7' ? 'selected' : '' }}>CONTENEDOR TAPA ABIERTA 40' (OPEN TOP CONTAINER 40')</option>
                    <option value="8" {{ $embarque->TipoDeTransporte == '8' ? 'selected' : '' }}>FLAT 20' (FLAT 20')</option>
                    <option value="9" {{ $embarque->TipoDeTransporte == '9' ? 'selected' : '' }}>FLAT 40' (FLAT 40')</option>
                    <option value="10" {{ $embarque->TipoDeTransporte == '10' ? 'selected' : '' }}>PLATAFORMA 20' (PLATFORM 20')</option>
                    <option value="11" {{ $embarque->TipoDeTransporte == '11' ? 'selected' : '' }}>PLATAFORMA 40' (PLATFORM 40')</option>
                    <option value="12" {{ $embarque->TipoDeTransporte == '12' ? 'selected' : '' }}>CONTENEDOR VENTILADO 20' (VENTILATED CONTAINER 20')</option>
                    <option value="13" {{ $embarque->TipoDeTransporte == '13' ? 'selected' : '' }}>CONTENEDOR TERMICO 20' (INSULATED CONTAINER 20')</option>
                    <option value="14" {{ $embarque->TipoDeTransporte == '14' ? 'selected' : '' }}>CONTENEDOR TERMICO 40' (INSULATED CONTAINER 40')</option>
                    <option value="15" {{ $embarque->TipoDeTransporte == '15' ? 'selected' : '' }}>CONTENEDOR REFRIGERANTE 20' (REFRIGERATED CONTAINER 20')</option>
                    <option value="16" {{ $embarque->TipoDeTransporte == '16' ? 'selected' : '' }}>CONTENEDOR REFRIGERANTE 40' (REFRIGERATED CONTAINER 40')</option>
                    <option value="17" {{ $embarque->TipoDeTransporte == '17' ? 'selected' : '' }}>CONTENEDOR REFRIGERANTE CUBO ALTO 40' (HIGH CUBE REFRIGERATED CONTAINER 40')</option>
                    <option value="18" {{ $embarque->TipoDeTransporte == '18' ? 'selected' : '' }}>CONTENEDOR CARGA A GRANEL 20' (BULK CONTAINER 20')</option>
                    <option value="19" {{ $embarque->TipoDeTransporte == '19' ? 'selected' : '' }}>CONTENEDOR TIPO TANQUE 20' (TANK CONTAINER 20')</option>
                    <option value="20" {{ $embarque->TipoDeTransporte == '20' ? 'selected' : '' }}>CONTENEDOR ESTANDAR 45' (STANDARD CONTAINER 45')</option>
                    <option value="21" {{ $embarque->TipoDeTransporte == '21' ? 'selected' : '' }}>CONTENEDOR ESTÁNDAR 48' (STANDARD CONTAINER 48')</option>
                    <option value="22" {{ $embarque->TipoDeTransporte == '22' ? 'selected' : '' }}>CONTENEDOR ESTÁNDAR 53' (STANDARD CONTAINER 53')</option>
                    <option value="23" {{ $embarque->TipoDeTransporte == '23' ? 'selected' : '' }}>CONTENEDOR ESTÁNDAR 8' (STANDARD CONTAINER 8')</option>
                    <option value="24" {{ $embarque->TipoDeTransporte == '24' ? 'selected' : '' }}>CONTENEDOR ESTÁNDAR 10' (STANDARD CONTAINER 10')</option>
                    <option value="25" {{ $embarque->TipoDeTransporte == '25' ? 'selected' : '' }}>CONTENEDOR ESTÁNDAR DE CUBO ALTO 45' (HIGH CUBE STANDARD CONTAINER 45')</option>
                    <option value="26" {{ $embarque->TipoDeTransporte == '26' ? 'selected' : '' }}>SEMIRREMOLQUE CON RACKS PARA ENVASES DE BEBIDAS</option>
                    <option value="27" {{ $embarque->TipoDeTransporte == '27' ? 'selected' : '' }}>SEMIRREMOLQUE CUELLO DE GANZO</option>
                    <option value="28" {{ $embarque->TipoDeTransporte == '28' ? 'selected' : '' }}>SEMIRREMOLQUE TOLVA CUBIERTO</option>
                    <option value="29" {{ $embarque->TipoDeTransporte == '29' ? 'selected' : '' }}>SEMIRREMOLQUE TOLVA (ABIERTO)</option>
                    <option value="30" {{ $embarque->TipoDeTransporte == '30' ? 'selected' : '' }}>AUTO-TOLVA CUBIERTO/DESCARGA NEUMÁTICA</option>
                    <option value="31" {{ $embarque->TipoDeTransporte == '31' ? 'selected' : '' }}>SEMIRREMOLQUE CHASIS</option>
                    <option value="32" {{ $embarque->TipoDeTransporte == '32' ? 'selected' : '' }}>SEMIRREMOLQUE AUTOCARGABLE (CON SISTEMA DE ELEVACIÓN)</option>
                    <option value="33" {{ $embarque->TipoDeTransporte == '33' ? 'selected' : '' }}>SEMIRREMOLQUE CON TEMPERATURA CONTROLADA</option>
                    <option value="34" {{ $embarque->TipoDeTransporte == '34' ? 'selected' : '' }}>SEMIRREMOLQUE CORTO TRASERO</option>
                    <option value="35" {{ $embarque->TipoDeTransporte == '35' ? 'selected' : '' }}>SEMIRREMOLQUE DE CAMA BAJA</option>
                    <option value="36" {{ $embarque->TipoDeTransporte == '36' ? 'selected' : '' }}>PLATAFORMA DE 28'</option>
                    <option value="37" {{ $embarque->TipoDeTransporte == '37' ? 'selected' : '' }}>PLATAFORMA DE 45'</option>
                    <option value="38" {{ $embarque->TipoDeTransporte == '38' ? 'selected' : '' }}>PLATAFORMA DE 48'</option>
                    <option value="39" {{ $embarque->TipoDeTransporte == '39' ? 'selected' : '' }}>SEMIRREMOLQUE PARA TRANSPORTE DE CABALLOS</option>
                    <option value="40" {{ $embarque->TipoDeTransporte == '40' ? 'selected' : '' }}>SEMIRREMOLQUE PARA TRANSPORTE DE GANADO</option>
                    <option value="41" {{ $embarque->TipoDeTransporte == '41' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (LÍQUIDOS)/SIN CALEFACCIÓN/SIN AISLAR</option>
                    <option value="42" {{ $embarque->TipoDeTransporte == '42' ? 'selected' : '' }}>SEMIRREOLQUE TANQUE (LÍQUIDOS)/CON CALEFACCIÓN/SIN AISLAR</option>
                    <option value="43" {{ $embarque->TipoDeTransporte == '43' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (LÍQUIDOS)/SIN CALEFACCIÓN/AISLADO</option>
                    <option value="44" {{ $embarque->TipoDeTransporte == '44' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (LÍQUIDOS)/CON CALEFACCIÓN/AISLADO</option>
                    <option value="45" {{ $embarque->TipoDeTransporte == '45' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (GAS)/SIN CALEFACCIÓN/SIN AISLAR</option>
                    <option value="46" {{ $embarque->TipoDeTransporte == '46' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (GAS)/CON CALEFACCIÓN/SIN AISLAR</option>
                    <option value="47" {{ $embarque->TipoDeTransporte == '47' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (GAS)/SIN CALEFACCIÓN/AISLADO</option>
                    <option value="48" {{ $embarque->TipoDeTransporte == '48' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (GAS)/CON CALEFACCIÓN/AISLADO</option>
                    <option value="49" {{ $embarque->TipoDeTransporte == '49' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (QUÍMICOS)/SIN CALEFACCIÓN/SIN AISLAR</option>
                    <option value="50" {{ $embarque->TipoDeTransporte == '50' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (QUÍMICOS)/CON CALEFACCIÓN/SIN AISLAR</option>
                    <option value="51" {{ $embarque->TipoDeTransporte == '51' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (QUÍMICOS)/SIN CALEFACCIÓN/AISLADO</option>
                    <option value="52" {{ $embarque->TipoDeTransporte == '52' ? 'selected' : '' }}>SEMIRREMOLQUE TANQUE (QUÍMICOS)/CON CALEFACCIÓN/AISLADO</option>
                    <option value="53" {{ $embarque->TipoDeTransporte == '53' ? 'selected' : '' }}>SEMIRREMOLQUE GÓNDOLA-CERRADA</option>
                    <option value="54" {{ $embarque->TipoDeTransporte == '54' ? 'selected' : '' }}>SEMIRREMOLQUE GÓNDOLA-ABIERTA</option>
                    <option value="55" {{ $embarque->TipoDeTransporte == '55' ? 'selected' : '' }}>SEMIRREMOLQUE TIPO CAJA CERRADA 48'</option>
                    <option value="56" {{ $embarque->TipoDeTransporte == '56' ? 'selected' : '' }}>SEMIRREMOLQUE TIPO CAJA CERRADA 53'</option>
                    <option value="57" {{ $embarque->TipoDeTransporte == '57' ? 'selected' : '' }}>SEMIRREMOLQUE TIPO CAJA REFRIGERADA 48'</option>
                    <option value="58" {{ $embarque->TipoDeTransporte == '58' ? 'selected' : '' }}>SEMIRREMOLQUE TIPO CAJA REFRIGERADA 53'</option>
                    <option value="59" {{ $embarque->TipoDeTransporte == '59' ? 'selected' : '' }}>DOBLE SEMIRREMOLQUE</option>
                    <option value="60" {{ $embarque->TipoDeTransporte == '60' ? 'selected' : '' }}>OTROS</option>
                    <option value="61" {{ $embarque->TipoDeTransporte == '61' ? 'selected' : '' }}>TANQUE 20'</option>
                    <option value="62" {{ $embarque->TipoDeTransporte == '62' ? 'selected' : '' }}>TANQUE 40'</option>
                    <option value="63" {{ $embarque->TipoDeTransporte == '63' ? 'selected' : '' }}>CARRO DE FERROCARRIL</option>
                    <option value="64" {{ $embarque->TipoDeTransporte == '64' ? 'selected' : '' }}>HIGH CUBE 20'</option>
                    <option value="65" {{ $embarque->TipoDeTransporte == '65' ? 'selected' : '' }}>AUTOMÓVIL</option>
                    <option value="66" {{ $embarque->TipoDeTransporte == '66' ? 'selected' : '' }}>CAMIÓN UNITARIO DE DOS EJES</option>
                    <option value="67" {{ $embarque->TipoDeTransporte == '67' ? 'selected' : '' }}>CAMIÓN UNITARIO DE TRES EJES</option>
                    <option value="68" {{ $embarque->TipoDeTransporte == '68' ? 'selected' : '' }}>VEHÍCULOS CON CAPACIDAD DE CARGA DE HASTA 3.5. TONELADAS</option>
                    <option value="69" {{ $embarque->TipoDeTransporte == '69' ? 'selected' : '' }}>TRACTOCAMIÓN</option>
                </select>
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
                <input type="text" name="Placas" class="form-control" id="placas"  maxlength="7" pattern="[A-Za-z0-9]{1,7}" value="{{$embarque->Placas}}">
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