@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Pedimento
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-9 mx-auto">

                <div class="card card-default">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float">
                            <span class="card-title">{{ __('Crear') }} Pedimento para Trafico: <strong>{{$trafico->id}}</strong></span>
                        </div>            
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('traficos.index') }}"> {{ __('Volver') }}</a>
                        </div>
                    </div>
                    <div class="card-body bg-white">
                        <form action="{{ route('pedimentos.storeFromTrafico', $trafico->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row padding-1 p-1 mb-3">
                                <!-- Campo oculto para enviar el ID del tráfico -->
                                <input type="hidden" name="trafico_id" value="{{ $trafico->id }}"> 
                                
                               <div  class="col-md-4" >
                                   <div class="form-group mb-2 mb20">
                                       <label for="aduana" class="form-label">{{ __('Aduana') }}</label>
                                       <input type="text" name="aduana" class="form-control @error('aduana') is-invalid @enderror" value="{{$trafico->aduana }}" id="aduana" placeholder="Aduana">
                                       {!! $errors->first('aduana', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                   </div>
                                   
                               </div>

                               <div class="col-md-4 ">
                                   <div class="form-group mb-2 mb20">
                                       <label for="patente" class="form-label">{{ __('Patente') }}</label>
                                       <input type="text" name="patente" class="form-control @error('patente') is-invalid @enderror" value="{{$trafico->patente }}" id="patente" placeholder="Patente">
                                       {!! $errors->first('patente', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                   </div>
                                   
                                  
                               </div>

                               <div class="col-md-4 ">
                                   <div class="form-group mb-2 mb20">
                                       <label for="fecha_ped" class="form-label">{{ __('Fecha Pedimento') }}</label>
                                       <input type="date" name="fechaPed" class="form-control @error('fechaPed') is-invalid @enderror" value="{{$fechaDeHoy}}" id="fecha_ped" placeholder="Fechaped">
                                       {!! $errors->first('fechaPed', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                   </div>
                               </div>
                           </div>  
                           
                           <div class="row  mb-3">
                               <div class="col-md-3">
                                   <div class="form-group mb-2 mb20">
                                       <label for="num_pedimento" class="form-label">{{ __('#Pedimento') }}</label>
                                       <input type="text" name="numPedimento" class="form-control @error('numPedimento') is-invalid @enderror" value="{{ old('numPedimento', $pedimento?->numPedimento) }}" id="num_pedimento" placeholder="Numpedimento">
                                       {!! $errors->first('numPedimento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                   </div>
                               </div>

                               <div class="col-md-3">
                                   <div class="form-group mb-2 mb20">
                                       <label for="clave_ped" class="form-label">{{ __('Clave Pedimento') }}</label>
                                       <input type="text" name="clavePed" class="form-control @error('clavePed') is-invalid @enderror" value="{{ old('clavePed', $pedimento?->clavePed) }}" id="clave_ped" placeholder="Claveped">
                                       {!! $errors->first('clavePed', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                   </div>
                               </div>
                               
                               <div class="col-md-3">
                                    <div class="form-group mb-2 mb20">
                                        <label for="operacion" class="form-label">{{ __('Tipo Operación') }}</label>
                                        <select name="operacion" id="operacion" class="form-control" required disabled>
                                            <option value="Importacion" {{ old('operacion', $trafico?->Toperacion) == 'Importacion' ? 'selected' : '' }}>
                                                {{ __('Importación') }}
                                            </option>
                                            <option value="Exportacion" {{ old('operacion', $trafico?->Toperacion) == 'Exportacion' ? 'selected' : '' }}>
                                                {{ __('Exportación') }}
                                            </option>
                                        </select>
                                    </div>  
                                </div>


                               <div class="col-md-3">
                                   <div class="form-group mb-2"  id="remesa_field" style="display:none;">
                                       <label for="num_remesa" class="form-label">{{ __('#Remesa') }}</label>
                                       <input type="number" name="remesa" class="form-control"  id="num_remesa" placeholder="Numero de remesa" value="{{$pedimento->remesa}}">
                                   </div>
                               </div>
                           </div>   
                       </div>
                   </div>

                           <div class="card mt-5">
                               <div class="card-header">Adjuntos y Status</div>
                               <div class="card-body">       
                                   <div class="row p-1">
                                       <div class="col-md-8">
                                           <div class="form-group mb-2 mb20">
                                               <label for="adjunto" class="form-label">{{ __('Adjuntar Pedimento') }}</label>
                                               <input type="file" class="form-control @error('adjunto') is-invalid @enderror" id="adjunto" name="adjunto">
                                               {!! $errors->first('adjunto', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                           </div>       
                                       </div>    
                                       <div class="col-md-4">
                                           <div class="mb-3">
                                                   <label for="" class="form-label">Status Pedimento</label>
                                                   <select class="form-select form-select-md" name="MxDocs" id="statusPedimento">
                                                    <option value="EN PROCESO">EN PROCESO</option>
                                                    <option value="VALIDADO" >VALIDADO</option>
                                                    <option value="PAGADO">PAGADO</option>
                                                    <option value="7" >LISTOS (DODA PITA EN TRAFICO)</option>
                                                    <option value="ENTREGADO">ENTREGADO</option>
                                                    <option value="9" >DESADUANAMIENTO LIBRE(VERDE)</option>
                                                    <option value="RECONOCIMIENTO ADUANERO" >RECONOCIMIENTO ADUANERO(ROJO)</option>
                                                    <option value="11" >RECONOCIMIENTO CONCLUIDO</option>
                                                   </select>
                                               </div>                                    
                                           </div>                                           
                                       </div>   
                                   </div> 
                               <div class="card-footer bg-light">
                                   <div class="container">
                                       <div class="row">
                                           <div class="col-md-12  p-1 text-end"> <!-- Agregué la clase text-end para alinear a la derecha -->
                                               <button type="submit" class="btn btn-success">{{ __('ENVIAR INFORMACION') }}</button>
                                           </div>  
                                       </div>
                                   </div>
                               </div>
                           </div>
                               
                                


                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('clave_ped').addEventListener('input', function() {
            var clave_ped = this.value.toUpperCase();
            var remesa_field = document.getElementById('remesa_field');
            
            if (clave_ped === 'IN' || clave_ped === 'AF' || clave_ped === 'A1' || clave_ped === 'RT') {
                remesa_field.style.display = 'block';
            } else {
                remesa_field.style.display = 'none';
            }
        });
    });
</script>