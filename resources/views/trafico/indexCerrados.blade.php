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


    <div class="container-fluid" style="margin-top: -23px;">
        <div class="row">    
        <!-- NAVBAR-->
        <div class="mb-3 text-white" >
            <div class="d-flex justify-content-between align-items-center bg-dark pt-3 pb-3 rounded-3" >
                <div class="left-container  mx-4 d-flex align-items-center">
                    <label for="empresaSelect" class="form-label fs-5 me-1">Empresa:</label>
                    <select class="form-select  me-3" name="empresaSelect" id="empresaSelect" aria-label="Default select example">
                        <option selected value="00">TODAS LAS EMPRESAS</option>
                            @foreach($userEmpresas as $userEmpresa)
                                <option value="{{ $userEmpresa->empresa->id }}" {{ old('empresaSelect', $request->empresaSelect) == $userEmpresa->empresa->id ? 'selected' : '' }}>
                                    {{ $userEmpresa->empresa->descripcion }}
                                </option>
                            @endforeach 
                    </select>
                    <button id="btnBuscar" class="btn btn-md btn-primary" title="Buscar"><i class="bi bi-search"></i></button>
                </div>
                
                <div class="right-container d-flex align-items-center">
                    <div id="date-container" class="d-flex align-items-center me-4">
                        <div class="me-3">
                            <label id="lblFechaInicial" for="txtFechaInicio"  class="fs-5 form-label">Desde:</label>
                        </div>
                        <div class="me-3">
                            <input id="txtFechaInicio" name="fechaInicio" value="{{ old('fechaInicio', $request->fechaInicio ?? $fechaInicio) }}" class="form-control" type="date">
                        </div>
                        <div class="me-3">
                            <label id="fechaFin" name="fechaFin" for="txtFechaFin" class="fs-5 form-label">Hasta:</label>
                        </div>
                        <div class="me-3">
                            <input id="txtFechaFin" name="fechaFin" value="{{ old('fechaFin', $request->fechaFin ?? $fechaFin) }}" class="form-control" type="date">
                        </div>
                        <button id="btnBuscarPorFecha" class="btn btn-md btn-primary" title="Buscar por fecha"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Traficos Cerrados') }}
                            </span>

                    <!--BOTON BORRAR FILTRO -->

                        <button id="clear-filters" class="btn btn-sm btn-secondary">Borrar Filtros</button>
           
                      
                        <!-- Botón para abrir el dropdown -->
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
                                    <a href="{{route('traficoDesdeFactura')}}" class="btn dropdown-item mt-2 mb-2 text-white fs-5 text-center rounded-btn hover-btn">Subir Factura</a>
                                    <a href="{{route('traficos.index')}}" class="btn dropdown-item mt-2 mb-2 text-white fs-5 text-center rounded-btn hover-btn">Traficos Abiertos</a>
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

`               <!--
                    <div class="row justify-content-end mt-4 mb-2"> 
                        <div class="col-1">
                            <label for="fechaInicio" class="form-label"><strong>Fecha de Inicio:</strong></label>
                            <input type="date" id="fechaInicio" name="fechaInicio" class="form-control">
                        </div>
                        <div class="col-1 me-3">
                            <label for="fechaFin" class="form-label"><strong>Fecha Fin:</strong></label>
                            <input type="date" id="fechaFin" name="fechaFin" class="form-control">
                        </div>
                    </div>
                -->

                @if ($errors->any())
                    <div class="alert alert-danger">
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
                                            <td class="text-center">
                                                {{ $trafico->id }}
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
                                            
                                            <td>{{ \Carbon\Carbon::parse($trafico->fechaReg)->format('d-m-Y H:i') }}</td>
                                            <td class="text-center" >{{ optional($trafico->pedimento)->operacion }}</td>
                                            <td>{{ $trafico->empresa->descripcion }}</td>
                                            @if ($trafico->adjuntoFactura)
                                            <td  ><a class="d-block mb-1" href="{{ route('facturas.stream',  ['id' => $trafico->id]) }}?v={{ time() }}" target="_blank">{{ $trafico->factura }} </a>
                                            
                                            </td>
                                            @else

                                            <td >{{ $trafico->factura }} 
                                            </td>
                                            @endif
                                            

                                        <td class="text-center">
                                           
                                            @if(isset($trafico->pedimento) && $trafico->pedimento !== null)
                                            
                                                {{ $trafico->pedimento->numPedimento }}
                                              
                                            
                                               
                                            @endif
                                            

                                        </td>
                                        
                                        
                                        <td class="text-center">{{optional($trafico->pedimento)->remesa}}
										<td >{{ $trafico->clavePed }}</td>
                                       

                                        <td id="trafico-{{ $trafico->id }}-mx-docs">
                                            @if ($trafico->MxDocs === "9")
                                                DESADUANAMIENTO LIBRE(VERDE)
                                            @elseif ($trafico->MxDocs === "11")
                                                RECONOCIMIENTO CONCLUIDO
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
                                                    <a class="btn btn-sm btn-primary fs-6 me-1 mt-1" href="{{ route('traficos.show', $trafico->id) }}"><i class="bi bi-eye"></i>Ver</a>
                                                   <!-- <a class="btn btn-sm btn-success fs-6 mt-1" href="{{ route('traficos.edit', $trafico->id) }}"><i class="bi bi-pencil-square"></i>Edit</a> -->
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" hidden class="btn btn-danger btn-sm fs-5" onclick="event.preventDefault(); confirm('Are you sure to delete?') ? this.closest('form').submit() : false;"><i class="bi bi-trash"></i></button>
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
                    <div class="mt-1">
                        <label for="exportType" class="form-label"><strong> Exportacion Excel:</strong> </label>
                        <select id="exportType" name="exportType" class="form-select" aria-label="Tipo de Exportación">
                            <option value="TODOS" selected>Todos</option>
                            <option value="importacion">Import</option>
                            <option value="exportacion">Exports</option>
                        </select>
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








    





<!--SCRIPTS DE DATATABLE -->
<script>
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
                { "type": "date", targets: [2] } // Ajusta el índice según tu estructura.
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


        //SCRIPT PARA LOS FILTROS EMPRESA Y FECHA 

        document.getElementById('btnBuscar').addEventListener('click', function() {
            const empresaSelect = document.getElementById('empresaSelect').value;
            const url = new URL(window.location.href);
            url.searchParams.set('empresaSelect', empresaSelect);
            window.location.href = url;
        });

        document.getElementById('btnBuscarPorFecha').addEventListener('click', function() {
            const fechaInicio = document.getElementById('txtFechaInicio').value;
            const fechaFin = document.getElementById('txtFechaFin').value;
            const url = new URL(window.location.href);
            url.searchParams.set('fechaInicio', fechaInicio);
            url.searchParams.set('fechaFin', fechaFin);
            window.location.href = url;
        });










    });
</script>




@endsection




