@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Embarque
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-8 mx-auto">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">Crear Embarque para Traficos <strong>{{$trafico_ids}}</strong> </span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('embarquesFromTrafico.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            <div class="row padding-1 p-1">

                                <div class="row mb-3 mt-2 justify-content-center ">
                                    <div class="col-md-3">
                                        <div class="form-group mb-3 mb20">
                                            <label for="num_embarque" class="form-label">{{ __('#Embarque') }}</label>
                                            <input type="text" name="numEmbarque" class="form-control" value="E" id="num_embarque" required>
                                        </div>
                                    </div> 
                                    <div class="col-md-3">
                                        <div class="form-group mb-3 mb20">
                                            <label for="num_economico" class="form-label">{{ __('#Economico') }}</label>
                                            <input type="text" name="numEconomico" class="form-control @error('numEconomico') is-invalid @enderror" value="{{ old('numEconomico', $embarque?->numEconomico) }}" id="num_economico" placeholder="Numeconomico">
                                            {!! $errors->first('numEconomico', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                        </div>
                                    </div>     

                                    <div class="col-md-3">
                                        <div class="form-group mb mb20">
                                            <label for="fecha_embarque" class="form-label">{{ __('Fecha Embarque') }}</label>
                                            <input type="datetime-local" name="fechaEmbarque" class="form-control @error('fechaEmbarque') is-invalid @enderror" value="{{$fechaDeHoy}}" id="fecha_embarque" placeholder="Fechaembarque">
                                            {!! $errors->first('fechaEmbarque', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                        </div>
                                    </div>        
                                </div>   
                                
<!-- SEGUNDA ROW -->           <div class="row mb-3 justify-content-center">
                                    <div class="col-md-3">
                                        <div class="form-group mb-3 mb20">
                                            <label for="transporte" class="form-label">{{ __('Transporte') }}</label>
                                            <input type="text" name="Transporte" class="form-control" id="transporte" >
                                        </div>
                                    </div>   
                                    <div class="col-md-6">
                                        <div class="form-group mb-3 mb20">
                                            <label for="tipo_transporte" class="form-label">{{ __('Tipo de Transporte') }}</label>
                                            <input type="text" name="TipoDeTransporte" class="form-control" id="tipo_transporte">
                                        </div>
                                    </div>        
                                </div>    

<!-- tercera row -->
                                
                                     

                                    
<!-- CUARTA row -->
                                <div class="row  justify-content-center">                                    
                                    <div class="col-md-3">
                                        <div class="form-group mb-3 mb20">
                                            <label for="chofer" class="form-label">{{ __('Chofer') }}</label>
                                            <input type="text" name="chofer" class="form-control" id="chofer">
                                        </div>
                                    </div>   
                                    <div class="col-md-4">
                                        <div class="form-group mb-3 mb20">
                                            <label for="placas" class="form-label">{{ __('Placas') }}</label>
                                            <input type="text" name="Placas" class="form-control" id="placas"  maxlength="6" pattern="[A-Za-z0-9]{1,6}">
                                        </div>
                                    </div>  
                                    <div class="col-md-2">
                                        <div class="form-group mb-3 mb20">
                                            <label for="caat" class="form-label">{{ __('CAAT') }}</label>
                                            <input type="text" name="CaaT" class="form-control" id="caat" maxlength="4" pattern="[A-Za-z0-9]{1,4}">
                                        </div>
                                    </div>  
                                </div>        
<!-- QUINTA ROW -->
                        
                                
<!-- SEXTA ROW -->                                
                                <div class="row">
                 
                                    <input type="hidden" id="trafico_ids_input" name="trafico_ids" value="{{$trafico_ids}}">   

                                </div>
      
                                  
 
                                <div class="col-md-10 mt20 mt-3 text-end">
                                    <button type="submit" class="btn btn-primary">{{ __('Asignar Embarque') }}</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                    


            </div>
        </div>
    </section>
@endsection
