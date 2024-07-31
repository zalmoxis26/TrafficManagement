@extends('layouts.app')

@section('template_title')
    Traficos
@endsection

@section('content')


<style>
    thead input {
            border-radius: 5px;
        }
  
        .table-responsive {
            overflow-x: auto;
        }

    </style>



    
</div>


    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Traficos') }}
                            </span>

                    <!--BOTON BORRAR FILTRO -->

                        <button id="clear-filters" class="btn btn-sm btn-secondary">Borrar Filtros</button>
           
                       
                        <div class="d-flex justify-content-end align-items-center">

                             <!-- Button trigger modal -->
                            <button type="button" class="btn btn-success me-2 btn-sm" data-bs-toggle="modal" data-bs-target="#exportEmbarquesModal">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Excel Embarques
                            </button>   
                            <button type="button" class="btn btn-success me-2 btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Excel Traficos
                            </button>   

                           
                            <div class="me-2">
                                <a href="{{ route('traficos.create') }}" hidden class="btn btn-primary btn-sm p-2" data-placement="left">
                                    {{ __('CREAR') }}
                                </a>
                            </div>

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
                    </div>
                    
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4" id="success-alert">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger m-4" id="error-alert">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger m-4" id="error-alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <div class="card-body text-center bg-light pt-0">
                        <div class="table-responsive">
                            <table id="table-traficos" class="table table-striped table-hover align-middle" >
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>#Trafico</th>
                                        <th>Anexos</th>
                                        <th>Embarque</th>
                                        <th style="width:7%;">Fecha</th>
                                        <th>Operacion</th>
                                        <th style="width:10%;">Cliente</th>
                                        <th>Factura</th>
                                        <th>Pedimento</th>
                                        <th>Remesa</th>
                                        <th>Clave</th>
                                        <th>Mxdocs</th>
                                        <th>Revision</th>
                                        <th>Aduana</th>
                                        <th>Patente</th>
                                     <!--  
                                        <th>Transporte</th>
                                        <th>Clasificacion</th>
                                        <th>Odt</th>-->
                                        <th></th>
                                    </tr>
                                </thead>
                                <thead class="table-dark">
                                    <tr>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar #Trafico"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Anexos"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Embarque"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Fecha"></th>                             
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Operacion"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Cliente"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Factura"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Pedimento"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Remesa"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Clave"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Mxdocs"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Revision"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Aduana"></th>
                                        <th><input style="width:100%;" type="text" placeholder="Buscar Patente"></th>
                                        <th></th>
                                    </tr>
                                </thead>                            
                                <tbody class="text-center">
                                    @foreach ($traficos as $trafico)
                                        <tr id="trafico-{{ $trafico->id }}">
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"  id="trafico_ids" value="{{ $trafico->id }}">
                                                        <label class="form-check-label" for="trafico_{{ $trafico->id }}">
                                                            {{ $trafico->id }}
                                                        </label>
                                                </div>
                                            </td>

                                            <td>  <button type="button" class="btn btn-dark btn-sm fs-5" data-bs-toggle="modal" data-bs-target="#createAnexoModal" data-trafico-id="{{$trafico->id}}">
                                                <i class="bi bi-paperclip"></i> </button>
                                            </td>
                                            <td>

                                                @if ($trafico->embarques->isNotEmpty())
                                                    <a href="{{ route('embarques.edit', $trafico->embarques->first()->id) }}" >
                                                        {{ $trafico->embarque }}
                                                    </a>
                                                @else
                                                    {{ $trafico->embarque}}        
                                                @endif
                                            </td>
                                          
                                            <td>{{ \Carbon\Carbon::parse($trafico->fechaReg)->format('d-M-Y H:i') }}</td>
                                            <td class="text-center">
                                                {{ optional($trafico->pedimento)->operacion ?: $trafico->Toperacion }}
                                            </td>                                            
                                            <td>{{ $trafico->empresa->descripcion }}</td>
                                            @if ($trafico->adjuntoFactura)
                                            <td>
                                                <a class="d-block mb-1" href="{{ route('facturas.stream',  ['id' => $trafico->id]) }}?v={{ time() }}" target="_blank">{{ $trafico->factura }} </a>
                                                <button type="button" class="btn btn-secondary btn-sm fs-6" data-bs-toggle="modal" title="Sustitur factura" data-bs-target="#sustituirFacturaModal" data-trafico-id="{{$trafico->id}}">
                                                    <i class="bi bi-recycle"></i>
                                                </button>
                                                <a style="text-decoration: none;" href="{{ route('traficos.edit', $trafico->id) }}" class="text-secondary fs-4" title="Editar">
                                                    <i class="bi bi-pencil-fill" style="text-decoration: none;"></i>
                                                </a>                                                
                                            </td>
                                            @else

                                            <td >{{ $trafico->factura }} 
                                                <button type="button" class="btn btn-secondary btn-sm fs-6" data-bs-toggle="modal" title="Sustitur factura" data-bs-target="#sustituirFacturaModal" data-trafico-id="{{$trafico->id}}">
                                                    <i class="bi bi-recycle"></i>
                                                </button>
                                            </td>
                                            @endif
                                            
                                        <td >
                                           
                                            @if(isset($trafico->pedimento) && $trafico->pedimento !== null)
                                            <div class="d-flex p-1">
                                                {{ $trafico->pedimento->numPedimento }}
                                                <a style="text-decoration: none;"  href="{{ route('pedimentoEditFromTrafico', ['id' => $trafico->id, 'pedimentoId' => $trafico->pedimento_id]) }}"   class="text-dark fs-4" title="Editar">
                                                    <i  class="bi bi-pencil-fill" style="text-decoration: none;"  ></i>
                                                </a>
                                               
                                            @else
                                            
                                
                                                <a style="text-decoration: none;" href="{{ route('pedimentoCreateFromTrafico', $trafico->id) }}"   class="text-dark fs-4" title="Editar{{$trafico->id}}">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                               
                                            @endif



                                            @if(isset($trafico->pedimento) &&  $trafico->pedimento->adjunto)

                                            <a href="{{ route('pedimentos.stream',  $trafico->pedimento->id) }}" target="_blank" class="text-success fs-4" title="Ver documento">
                                                <i class="bi bi-file-earmark-ppt-fill"></i>
                                            </a>
                                            
                                            
                                            

                                            @else
                                            <a href="" class="text-danger fs-4" title="SIN PEDIMENTO ADJUNTO">
                                                <i class="bi bi-file-earmark-ppt"></i>
                                            </a>
                                            @endif
                                            
                                            </div>

                                        </td>
                                        
                                        
                                        <td class="text-center">{{optional($trafico->pedimento)->remesa}}
										<td >{{ $trafico->clavePed }}</td>
                                       

                                        <td id="trafico-{{ $trafico->id }}-mx-docs">
                                            @if ($trafico->MxDocs === "9")
                                                DESADUANAMIENTO LIBRE(VERDE)
                                            @elseif ($trafico->MxDocs === "11")
                                                RECONOCIMIENTO CONCLUIDO
                                                @elseif ($trafico->MxDocs === "7")
                                                LISTOS (DODA PITA EN TRAFICO)
                                            @else
                                                {{ $trafico->MxDocs }}
                                            @endif
                                        </td>
                                       

                                            <td id="trafico-{{ $trafico->id }}-revision">
                                                @if ($trafico->revision_id)
                                                    <a href="{{ route('revisiones.edit', $trafico->revision_id) }}">{{ $trafico->Revision }}</a>
                                                @else
                                                    {{ $trafico->Revision }}
                                                @endif
                                            </td>


                                            <td >{{ $trafico->aduana }}</td>
                                            <td class="text-center">{{ $trafico->patente}}</td>
                                          
                                            <td>
                                                <form action="{{ route('traficos.destroy', $trafico->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-primary fs-6 me-1 mt-1" href="{{ route('traficos.show', $trafico->id) }}">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                    @csrf
                                                    @method('DELETE')
                                                    @role('admin')
                                                        <button type="submit" class="btn btn-sm btn-danger  me-1 mt-1" onclick="event.preventDefault(); confirm('Are you sure to delete?') ? this.closest('form').submit() : false;">
                                                            <i class="bi bi-trash"></i>Borrar
                                                        </button>
                                                    @endrole    
                                                </form>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        </div> 
                    </div> 
                
            </div> 
            </div>        
            </div>              
            </div>
        </div>
    </div>


   <!-- Modal EXPORTAR EXCEL -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success  text-white" style="font-weight: bold;">
                <h5 class="modal-title" id="exportModalLabel">Exportar Datos a Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-secondary-subtle">
                <form action="{{ route('trafico.export') }}" method="GET">
                    <!-- Select for IMPORT, EXPORTS, TODOS -->
                    <div class="mt-1 col-10 mx-auto">
                        <label for="exportType" class="form-label"><strong> Exportacion Excel:</strong> </label>
                        <select id="exportType" name="exportType" class="form-select" aria-label="Tipo de Exportación">
                            <option value="TODOS" selected>Todos</option>
                            <option value="importacion">Import</option>
                            <option value="exportacion">Exports</option>
                        </select>
                    </div>

                    <div class="row justify-content-center mt-4 mb-2"> 
                        <div class="col-5">
                            <label for="fechaInicio" class="form-label"><strong>Fecha de Inicio:</strong></label>
                            <input type="date" id="fechaInicio" name="fechaInicio" class="form-control">
                        </div>
                        <div class="col-5">
                            <label for="fechaFin" class="form-label"><strong>Fecha Fin:</strong></label>
                            <input type="date" id="fechaFin" name="fechaFin" class="form-control">
                        </div>
                    </div>

                    <!-- Radio buttons for ABIERTO, CERRADO, TODOS -->
                    <div class="mt-4 text-center">
                        <label class="form-label"><strong>Estado:</strong></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusTodos" value="TODOS" checked>
                            <label class="form-check-label" for="statusTodos">Todos</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusAbierto" value="ABIERTO">
                            <label class="form-check-label" for="statusAbierto">Abierto</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusCerrado" value="CERRADO">
                            <label class="form-check-label" for="statusCerrado">Cerrado</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer  bg-secondary-subtle">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Exportar</button>
                </div>
            </form>    
        </div>
    </div>
</div>

<!-- Modal EXPORTAR EXCEL EMBARQUES -->
<div class="modal fade" id="exportEmbarquesModal" tabindex="-1" aria-labelledby="exportEmbarquesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white" style="font-weight: bold;">
                <h5 class="modal-title" id="exportEmbarquesModalLabel">Exportar Datos a Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-secondary-subtle">
                <form action="{{ route('exportar-embarques') }}" method="GET">
                    <!-- Select for IMPORT, EXPORTS, TODOS -->
                    <!-- Radio buttons for ABIERTO, CERRADO, TODOS -->

                    <!-- Radio buttons for MODULADO, NO MODULADO -->
                    <div class="col-10 mt-3 mx-auto">
                        <label for="modulado" class="form-label"><strong>Embarques:</strong></label>
                        <select id="modulado" name="modulado" class="form-select" aria-label="Modulado">
                            <option value="TODOS" selected>Todos</option>
                            <option value="SI">Modulado</option>
                            <option value="NO">No Modulado</option>
                        </select>
                    </div>

                    <!-- Date pickers for Fecha de Inicio and Fecha Fin -->
                    <div class="row justify-content-center mt-4 mb-2"> 
                        <div class="col-5">
                            <label for="fechaInicio" class="form-label"><strong>Fecha de Inicio:</strong></label>
                            <input type="date" id="fechaInicio" name="fechaInicio" class="form-control">
                        </div>
                        <div class="col-5">
                            <label for="fechaFin" class="form-label"><strong>Fecha Fin:</strong></label>
                            <input type="date" id="fechaFin" name="fechaFin" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-secondary-subtle">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Exportar</button>
                </div>
            </form>    
        </div>
    </div>
</div>


<!--MODAL DE ANEXOS -->


        <div class="modal fade" id="createAnexoModal" tabindex="-1" aria-labelledby="createAnexoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title" id="createAnexoModalLabel">Agregar Anexo a Trafico #</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('anexo.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="trafico_id" id="traficoId" value="">
                            <div class="mb-3">
                                <label for="asunto" class="form-label">Asunto</label>
                                <input type="text" class="form-control" id="asunto" name="asunto" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Comentarios u observaciones</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" ></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="archivo" class="form-label">Archivo</label>
                                <input type="file" class="form-control @error('archivo') is-invalid @enderror" id="archivo" name="archivo" required>
                                @error('archivo')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>                            
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>



<!--SCRIPT ANEXOS-->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var createAnexoModal = document.getElementById('createAnexoModal');
        createAnexoModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var traficoId = button.getAttribute('data-trafico-id');
            var inputTraficoId = createAnexoModal.querySelector('#traficoId');
            inputTraficoId.value = traficoId;

            // Modificar el título del modal
            var modalTitle = createAnexoModal.querySelector('.modal-title');
            modalTitle.innerHTML = "Agregar Anexo a Trafico #" + traficoId;
        });
    });
</script>

    

<!--MODAL DE SUSTITUIR FACTURA -->

<div class="modal fade" id="sustituirFacturaModal" tabindex="-1" aria-labelledby="sustituirFacturaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="sustituirFacturaForm" method="POST" enctype="multipart/form-data" action="{{ route('trafico.sustituirFactura') }}">
                @csrf
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="sustituirFacturaModalLabel">Sustituir Factura</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Hidden field to store trafico id -->
                    <input type="hidden" name="trafico_id" id="traficoId" value="">
                    <!-- Custom file input -->
                    <div class="mb-3">
                        <label for="archivo" class="form-label">Adjuntar Factura Correcta:</label>
                        <input type="file" class="form-control custom-file-input" id="customFile" name="archivo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sript para pasar trafico id al modal -->

    <script>
        // Event listener for the modal show event
        var modal = document.getElementById('sustituirFacturaModal');
        modal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var traficoId = button.getAttribute('data-trafico-id'); // Extract info from data-trafico-id attribute
            var input = modal.querySelector('#traficoId'); // Find the hidden input field
            input.value = traficoId; // Set the value of the hidden input field
            var modalTitle = sustituirFacturaModal.querySelector('.modal-title');
            modalTitle.innerHTML = "Sustituir Factura para Trafico #" + traficoId;
        });
    </script>



<!--//FORMS Y SCRIPTS PARA NAVBAR DE EMBARQUE, REVISION  -->
    <!--//SCRIPT Y FORM PARA ASIGNAR EMBARQUE----->
      <form id="asignar_embarque_form" action="{{ route('embarque.createFromTrafico') }}" method="POST">
        @csrf
            <input type="hidden" id="trafico_ids_input" name="trafico_ids" value="">
            <!-- Aquí van tus checkboxes -->
      </form>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener todos los checkboxes
            var checkboxes = document.querySelectorAll('input[id="trafico_ids"]');
            
            // Agregar un evento de cambio a cada checkbox
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    // Obtener los IDs de tráfico seleccionados
                    var trafico_ids = [];
                    checkboxes.forEach(function(checkbox) {
                        if (checkbox.checked) {
                            trafico_ids.push(checkbox.value);
                        }
                    });
    
                    // Actualizar el valor del campo oculto con los IDs de tráfico seleccionados
                    document.getElementById('trafico_ids_input').value = trafico_ids.join(',');
                });
            });
    
            // Mostrar el formulario y enviarlo cuando se hace clic en el enlace
            document.getElementById('asignar_embarque_btn').addEventListener('click', function(e) {
                e.preventDefault();
               
                document.getElementById('asignar_embarque_form').submit();
            });
        });
    </script>

    <!--//SCRIPT Y FORM PARA DESASIGNAR EMBARQUE----->
    
    <form id="desasignar_embarque_form" action="{{ route('embarque.desasignarFromTrafico') }}" method="POST">
        @csrf
            <input type="hidden" id="trafico_des_input" name="trafico_des" value="">
            <!-- Aquí van tus checkboxes -->
      </form>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener todos los checkboxes
            var checkboxes = document.querySelectorAll('input[id="trafico_ids"]');
            
            // Agregar un evento de cambio a cada checkbox
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    // Obtener los IDs de tráfico seleccionados
                    var trafico_ids = [];
                    checkboxes.forEach(function(checkbox) {
                        if (checkbox.checked) {
                            trafico_ids.push(checkbox.value);
                        }
                    });
    
                    // Actualizar el valor del campo oculto con los IDs de tráfico seleccionados
                    document.getElementById('trafico_des_input').value = trafico_ids.join(',');
                });
            });
    
            // Mostrar el formulario y enviarlo cuando se hace clic en el enlace
            document.getElementById('desasignar_embarque_btn').addEventListener('click', function(e) {
                e.preventDefault();
               
                document.getElementById('desasignar_embarque_form').submit();
            });
        });
    </script>


<!--SCRIPTS DE DATATABLE -->
<script>

document.addEventListener('DOMContentLoaded', function () {
    $(document).ready(function() {
        // Inicializa el DataTable con stateSave habilitado
        var tabla = $('#table-traficos').DataTable({
            autoWidth: false,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            layout: {
                top:null,
                topStart: null,
                topEnd: null,
                bottomStart: 'pageLength',
                bottomEnd: {
                            search: {
                                placeholder: 'Buscar...'
                            }
                        },
                bottom2Start: 'info',
                bottom2End: 'paging'
                },
            "lengthMenu": [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
            "pageLength": 5,
            "order": [],
            "stateSave": true, // Habilitar stateSave
            "columnDefs": [
                { "type": "date", targets: [3] } // Ajusta el índice según tu estructura.
            ]
        });

        // Restaurar valores de filtros de columna desde el estado guardado
        var state = tabla.state.loaded();
        if (state) {
            tabla.columns().every(function(index) {
                var colSearch = state.columns[index].search.search;
                if (colSearch) {
                    $('thead:eq(1) th:eq(' + index + ') input').val(colSearch); // Asegúrate de usar el segundo thead para inputs
                }
            });
        }

        // Configurar filtros de columna
        $('#table-traficos thead:eq(1) th').each(function() { // Usar el segundo thead para configurar los filtros
            var title = $(this).text();
            $(this).find('input').on('keyup change', function() {
                if (tabla.column($(this).parent().index() + ':visible').search() !== this.value) {
                    tabla
                        .column($(this).parent().index() + ':visible')
                        .search(this.value)
                        .draw();
                }
            });
        });

         // Clear filters button functionality
        // Clear filters button functionality
        $('#clear-filters').on('click', function() {
            // Clear each input field in the second thead
            $('#table-traficos thead:eq(1) th input').each(function() {
                $(this).val('');
            });

            // Clear DataTable column search
            tabla.columns().search('').draw();

            // Clear the global search input
            $('.dataTables_filter input').val('');
            tabla.search('').draw();
        });
    });
});
</script>




@endsection




