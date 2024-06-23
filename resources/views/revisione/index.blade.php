@extends('layouts.app')

@section('template_title')
    Revisiones
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-9 mx-auto text-center">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Revisiones') }}
                            </span>

                            <div class="float-right">
                                    <a href="{{ route('traficos.index') }}" class="btn btn-secondary btn-sm float-right"  data-placement="left">
                                    {{ __('Ir a Traficos') }}
                                    </a>
                            </div> 
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p>{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body bg-white">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-3 mt-3" id="table-revisiones">
                                <thead class="thead table-dark">
                                    <tr>
                                    <th>#Trafico</th>
                                    <th class="text-center" style="width:15%">Empresa</th>
									<th >Nombre Revisor</th>
									<th >Inicio Revision</th>
									<th >Fin Revision</th>
									<th >Tiempo Revision</th>
                                    <th >Ubicacion</th>
                                    <th >Status</th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($revisiones as $revisione)
                                        <tr>
                                            <td class="text-center">{{ optional($revisione->traficos)->id }}</td>   
                                            <td>{{ optional($revisione->traficos)->empresa->descripcion }}</td>                              
                                            <td class="text-center" >{{ $revisione->nombreRevisor }}</td>
                                            <td>
                                                @if ($revisione->inicioRevision)
                                                {{ \Carbon\Carbon::parse($revisione->inicioRevision)->format('d-M-Y H:i')  }}
                                            @else
                                                
                                            @endif
                                            </td>       
                                            <td>
                                                @if ($revisione->finRevision)
                                                    {{ \Carbon\Carbon::parse($revisione->finRevision)->format('d-M-Y H:i') }}
                                                @else
                                                    
                                                @endif
                                            </td>
                                            
                                            <td >{{ $revisione->tiempoRevision }}</td>
                                            <td>{{$revisione->ubicacionRevision}}</td>

  <!--BTN STATUS REVISION -->           <td>
                                            @php
                                                $trafico = $revisione->traficos;
                                                $revision = $trafico ? $trafico->Revision : null;
                                                $buttonClass = '';
                                                $badgeClass = '';
                                        
                                                switch ($revision) {
                                                    case 'PENDIENTE':
                                                        $buttonClass = 'btn btn-danger rounded-5 p-0';
                                                        $badgeClass = 'badge bg-danger';
                                                        break;
                                                    case 'EN PROCESO':
                                                        $buttonClass = 'btn btn-primary rounded-5 p-0';
                                                        $badgeClass = 'badge bg-primary';
                                                        break;
                                                    case 'CORRECCIONES':
                                                        $buttonClass = 'btn btn-warning rounded-5 p-0';
                                                        $badgeClass = 'badge bg-warning';
                                                        break;
                                                    case 'FINALIZADO':
                                                        $buttonClass = 'btn btn-success rounded-5 p-0';
                                                        $badgeClass = 'badge bg-success';
                                                        break;
                                                    default:
                                                        $buttonClass = 'btn btn-secondary rounded-5 p-0';
                                                        $badgeClass = 'badge bg-secondary';
                                                        break;
                                                }
                                            @endphp
                                        
                                                <button class="{{ $buttonClass }}">
                                                    <span class="{{ $badgeClass }}">{{ $revision }}</span>
                                                </button>   
                                        </td>
                                        

                                        
                                        

                                            <td>
                                                <form action="{{ route('revisiones.destroy', $revisione->id) }}" method="POST">
                                            <!--    <a class="btn btn-sm btn-primary " href="{{ route('revisiones.show', $revisione->id) }}"><i class="fa fa-fw fa-eye"></i> {{ __('Show') }}</a> -->
                                                    <a class="btn btn-sm btn-success" href="{{ route('revisiones.edit', $revisione->id) }}"><i class="bi bi-pen"></i>Editar</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="event.preventDefault(); confirm('Are you sure to delete?') ? this.closest('form').submit() : false;"><i class="bi bi-trash"></i> {{ __('Borrar') }}</button>
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

    <!--SCRIPTS DE DATATABLE -->
<script>
    $(document).ready(function() {
        // Inicializa el DataTable con stateSave habilitado
        var tabla = $('#table-revisiones').DataTable({
            autoWidth: false,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            layout: {
                topStart: 'pageLength',
                topEnd: {
                            search: {
                                placeholder: 'Buscar...'
                            }
                        },
                },
            "lengthMenu": [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
            "pageLength": 10,
            "order": [],
            "stateSave": true, // Habilitar stateSave
            "columnDefs": [
                { "type": "date", targets: [3,4] } // Ajusta el índice según tu estructura.
            ]
        });
    });
</script>

@endsection

