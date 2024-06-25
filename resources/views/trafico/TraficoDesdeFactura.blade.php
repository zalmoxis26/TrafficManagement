@extends('layouts.app')

@section('template_title')
    {{ __('Transmitir Factura') }}
@endsection




@section('content')
    <!-- Contenedor principal -->
    <div class="container mt-5">
        <!-- Card para la factura -->
        <div class="card">
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>Detalles de la Factura</h3>
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle fs-4 pt-0 pb-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-list"></i>
                        </button>
                
                        <!-- Dropdown -->
                        <div class="dropdown-menu dropdown-menu-lg-end p-3 me-3" aria-labelledby="dropdownMenuButton">
                            <!-- Botones dentro del dropdown -->
                            <a href="{{route('revisiones.index')}}" class="btn dropdown-item text-white fs-5 text-center rounded-btn hover-btn">Revisión</a>
                            <a href="#" id="asignar_embarque_btn" class="btn dropdown-item mt-2 text-white fs-5 text-center rounded-btn hover-btn">Asignar Embarque</a>
                            <a href="#" id="desasignar_embarque_btn" class="btn dropdown-item mt-2 text-white fs-5 text-center rounded-btn hover-btn">Desasignar Embarque</a>
                            <a href="{{route('traficoDesdeFactura')}}" class="btn dropdown-item mt-2 mb-2 text-white fs-5 text-center rounded-btn hover-btn">Subir Factura</a>
                            <a href="{{route('traficos.cerrados')}}" class="btn dropdown-item mt-2 mb-2 text-white fs-5 text-center rounded-btn hover-btn">Traficos Cerrados</a>
                        </div>
                    </div>
                </div>
            </div>   
            <div class="card-body">
                <!-- Formulario para detalles de la factura -->
                <form action="{{ route('trafico.storeFromFactura') }}" method="POST" enctype="multipart/form-data"> <!-- Ruta para el envío del formulario -->
                    @csrf <!-- Token CSRF para proteger el formulario -->
                    <!-- Número de factura -->

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="aduana" class="form-label">Aduana</label>
                                <input type="text" class="form-control" id="aduana" name="aduana" list="aduanaOptions" placeholder="Ingrese el número de Aduana" required>
                                <datalist id="aduanaOptions">
                                    <option value="400-TIJ"></option>
                                    <option value="390-TEC"></option>
                                    <option value="190-MEX"></option>
                                    <option value="160-MANZ"></option>
                                    <option value="110-ENS"></option>      
                                </datalist>
                            </div>
                            
                        </div>    
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="patente" class="form-label">Patente</label>
                                <input type="text" class="form-control" id="patente" name="patente" value="3875" placeholder="Ingrese patente" required>
                            </div>
                        </div> 
                          <!-- Fecha -->
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="datetime-local" class="form-control" id="fecha" name="fechaReg" value="{{$fechaDeHoy}}" required>

                            </div>
                        </div>   
                    </div> 

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="facturaNumero" class="form-label">Número de Factura</label>
                                <input type="text" class="form-control" id="facturaNumero" name="factura" placeholder="Ingrese el número de factura" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                             <!-- Empresa -->
                            <div class="mb-3">
                                <label for="empresa" class="form-label">Empresa</label>
                                <select id="empresa" class="form-control" name="empresa_id" required>
                                    <option value="" selected disabled>Selecciona una empresa</option>
                                    <!-- Lista de empresas -->
                                    @foreach ($empresas as $empresa)
                                        <option value="{{ $empresa->empresa->id }}">{{ $empresa->empresa->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>       
                        </div>
                    </div>

                    <!-- Adjunto de documento -->
                    <div class="row">
                        <div class="col-md-8">  
                            <div class="mb-3">
                                <label for="adjuntoFactura" class="form-label">Adjuntar Documento</label>
                                <input type="file" class="form-control @error('adjuntoFactura') is-invalid @enderror" id="adjuntoFactura" name="adjuntoFactura" required>
                                @error('adjuntoFactura')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    
                                               
                  
                    
                    
                    <!-- Botones de radio para "Lleva Revisión" -->
                    <div class="mb-3 mt-5 d-flex justify-content-center gap-2 fw-bold" >
                        <div class="bg-light p-2 pt-3 border" style="width:25%;">
                            <label class="form-label"  style="margin-right: 10px; padding-left: 15px; ">LLEVA REVISION</label>
                            <!-- Opción "Sí" -->
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="lleva_revision" id="llevaRevisionSi" value="si" required>
                                <label class="form-check-label" for="llevaRevisionSi">SI</label>
                            </div>

                            <!-- Opción "No" -->
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="lleva_revision" id="llevaRevisionNo" value="no" required>
                                <label class="form-check-label" for="llevaRevisionNo">NO</label>
                            </div>
                            
                        <div class="text-center" id="ubicacionRevisionContainer"  style="display: none">
                            <hr></hr>
                            <label for="ubicacionRevision" class="form-label">Ubicacion de Revision</label>
                            <input
                                type="text"
                                class="form-control"
                                name="ubicacionRevision"
                                id="ubicacionRevision"
                                aria-describedby="helpId"
                                placeholder="Ubicacion de Revision"
                            />
                        </div>
                        </div>

                    </div>  
                   
                          
                    <div class="float-end">
                        <!-- Botón para enviar -->
                    <button type="submit" class="btn btn-primary">Subir Factura</button>
                    </div>        
                    
                </form>
            </div>
        </div>
    </div>

   
    <script>
        $(document).ready(function() {
            $('input[name="lleva_revision"]').change(function() {
                if ($(this).val() === 'si') {
                    $('#ubicacionRevisionContainer').show();  // Mostrar el campo
                    $('#ubicacionRevision').prop('required', true);  // Agregar el atributo required
                } else {
                    $('#ubicacionRevisionContainer').hide();  // Ocultar el campo
                    $('#ubicacionRevision').prop('required', false);  // Quitar el atributo required
                }
            });
        });
        </script>
        
        
@endsection
