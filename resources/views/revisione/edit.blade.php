@extends('layouts.app')

@section('template_title')
    {{ __('Actualizar') }} Revision
@endsection

@section('content')
    <section class="content container-fluid">
        
            <div class="col-md-9 mx-auto">

                <div class="card card-default">
                    <div class="card-header">
                         <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span class="card-title">{{ __('Actualizar') }} Revision</span>

                            <div class="float-right">
                                    <a href="{{ route('traficos.index') }}" class="btn btn-secondary btn-sm float-right"  data-placement="left">
                                    {{ __('Ir a Traficos') }}
                                    </a>
                            </div> 
                        </div>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('revisiones.update', $revisione->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            <div class="row padding-1 p-2 mb-3 mt-3">
                                <div class="col-md-4">
                                    <div class="form-group mb-2 mb20">
                                        <label for="nombre_revisor" class="form-label">{{ __('Nombre del Revisor') }}</label>
                                        <input list="revisores" name="nombreRevisor" class="form-control @error('nombreRevisor') is-invalid @enderror" value="{{ old('nombreRevisor', $revisione?->nombreRevisor) }}" id="nombre_revisor" placeholder="Nombrerevisor">
                                        <datalist id="revisores">
                                            @foreach ($revisores as $revisor)
                                                <option value="{{ $revisor->name }}"></option>
                                            @endforeach
                                        </datalist>
                                        {!! $errors->first('nombreRevisor', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                    </div>                                    
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group mb-2 mb20" d>
                                        <label for="inicio_revision" class="form-label">{{ __('Inicio Revision') }}</label>
                                        <input disabled type="datetime-local" name="inicioRevision" class="form-control @error('inicioRevision') is-invalid @enderror" value="{{ old('inicioRevision', $revisione?->inicioRevision) }}" id="inicio_revision" placeholder="Iniciorevision">
                                        {!! $errors->first('inicioRevision', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-2 mb20">
                                        <label for="fin_revision" class="form-label">{{ __('Fin Revision') }}</label>
                                        <input disabled type="datetime-local" name="finRevision" class="form-control @error('finRevision') is-invalid @enderror" value="{{ old('finRevision', $revisione?->finRevision) }}" id="fin_revision" placeholder="Finrevision">
                                        {!! $errors->first('finRevision', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-2 mb20">
                                        <label for="tiempo_revision" class="form-label">{{ __('Tiempo de Revision') }}</label>
                                        <input type="text" disabled class="form-control" value="{{ old('tiempoRevision', $revisione?->tiempoRevision) }}" id="tiempo_revision" placeholder="Tiemporevision">
                                    </div>
                                </div>    
                            </div> 
                        </div>    
                    </div>        
                    <div class="card mt-5">
                        <div class="card-header">Adjuntos y Status</div>
                        <div class="card-body">    
                            <div class="row justify-content-center">
                                <div class="col-3 pt-1"> 
                                    <label for="ubicacionRevision" class="form-label">Ubicacion de Revision</label>
                                    <input type="text"  class="form-control" name="ubicacionRevision" id="ubicacionRevision" value="{{$revisione->ubicacionRevision}}" placeholder="Ubicacion de Revision" @role('revisor')  disabled @endrole />
                                </div>                               
                                
                            @php
                                // Obtener el valor de la revisión
                                $revisionStatus = $revisione->traficos->Revision ?? 'EN PROCESO';
                            @endphp                

                                <div class="col-md-4 pt-1">
                                    <div class="mb-3">
                                        <label for="" class="form-label">Status Revision</label>
                                        <select class="form-select form-select-md" name="Revision" id="statusRevision">
                                            <option value="EN PROCESO" @selected($revisionStatus == 'EN PROCESO')>EN PROCESO</option>
                                            <option value="CORRECCIONES" @selected($revisionStatus == 'CORRECCIONES')>CORRECCIONES</option>
                                            <option value="FINALIZADO" @selected($revisionStatus == 'FINALIZADO')>FINALIZADO</option>
                                        </select>
                                        <div id="updateMessage" style="margin-top: 10px; color: green;"></div>
                                    </div>                                      
                                </div>   
                            </div>   
                            

                            <div class="row justify-content-center pt-1">
                                <div class="col-md-7 d-flex align-items-center">
                                    <div class="form-group mb-2 mb20 flex-grow-1">
                                        <label for="adjuntoRevision" class="form-label">{{ __('Adjuntar Revision') }}</label>
                                        <input type="file" class="form-control fs-6 @error('adjuntoRevision') is-invalid @enderror" id="adjuntoRevision" name="adjuntoRevision">
                                        @error('adjuntoRevision')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <a class="btn btn-md btn-dark ms-2 mt-4 pt-2 pb-1" data-bs-toggle="modal" data-bs-target="#documentModal" onclick="loadDocument('{{ $revisione->adjuntoRevision }}')">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div> 
                            </div>   
                              
                                   
                            </div> 
                        <div class="card-footer bg-light">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12  p-1 text-end"> <!-- Agregué la clase text-end para alinear a la derecha -->
                                        <button type="submit" class="btn btn-primary">{{ __('ACTUALIZAR INFORMACION') }}</button>
                                    </div>  
                                </div>
                            </div>
                        </div>
                    </div>
                                   
                                    
                                    
                            
                            

                        </form>
                    
                
            </div>
        </div>
    </section>
@endsection


<!-- Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="documentModalLabel">Vista Previa del Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="documentIframe" frameborder="0" style="width: 100%; height: 500px; display: none;"></iframe>
                <a id="downloadLink" href="#" class="btn btn-primary" style="display: none;" download>Descargar Archivo</a>
            </div>
        </div>
    </div>
</div>





<script>
    function loadDocument(fileName) {
        let fileUrl = `{{ url('storage/') }}/${fileName}`;
        let extension = fileName.split('.').pop().toLowerCase();
        
        let documentIframe = document.getElementById('documentIframe');
        let downloadLink = document.getElementById('downloadLink');
        
        if (extension === 'xlsx' || extension === 'xls' || extension === 'doc' || extension === 'docx') {
            // If the file is an Excel file or a Word document, hide the iframe and show the download link
            documentIframe.style.display = 'none';
            downloadLink.href = fileUrl;
            downloadLink.style.display = 'block';
        } else {
            // For images and other file types, show the iframe and hide the download link
            documentIframe.src = fileUrl;
            documentIframe.style.display = 'block';
            downloadLink.style.display = 'none';
        }
    }
</script>
