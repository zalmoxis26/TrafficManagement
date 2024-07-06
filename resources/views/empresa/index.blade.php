@extends('layouts.app')

@section('template_title')
    Empresas
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-10 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                                {{ __('Listado de Empresas') }}
                            </span>

                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle fs-4 pt-0 pb-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-list"></i>
                                </button>
                        
                                <!-- Dropdown -->
                                <div class="dropdown-menu dropdown-menu-lg-end p-3 me-3" aria-labelledby="dropdownMenuButton">
                                    <!-- Botones dentro del dropdown -->
                                    <a href="{{route('inicio')}}" class="btn dropdown-item text-white fs-5 text-center rounded-btn hover-btn">Inicio</a>
                                    <a href="{{route('empresas.index')}}" class="btn dropdown-item mt-2 text-white fs-5 text-center rounded-btn hover-btn">Listado de Empresas</a>
                                    <a  href="{{ route('empresas.create') }}" class="btn dropdown-item mt-2 text-white fs-5 text-center rounded-btn hover-btn">Agregar Empresas</a>
                                    <a href="{{ route('users-empresas.index') }}" class="btn dropdown-item mt-2 text-white fs-5 text-center rounded-btn hover-btn">Listado Empresas Asignadas</a>
                                    <a href="{{ route('users-empresas.create') }}" class="btn dropdown-item mt-2 mb-2 text-white fs-5 text-center rounded-btn hover-btn">Asignar Empresas</a>
                                </div>
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
                            <table class="table table-striped table-hover text-center p-2" id="table-empresas">
                                <thead class="thead table-dark">
                                    <tr>
                                        <th>No</th>            
                                        <th  >Clave</th>
                                        <th class="text-center">Nombre de la Empresa</th>
                                        <th class="text-center">Empresa Matriz</th>
                                        <th class="text-center">RFC</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empresas as $empresa)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            
										<td >{{ $empresa->clave }}</td>
										<td class="text-center">{{ $empresa->descripcion }}</td>
                                        <td class="text-center">{{ $empresa->empresaMatriz }}</td>
										<td >{{ $empresa->rfc }}</td>

                                            <td>
                                                <form action="{{ route('empresas.destroy', $empresa->id) }}" method="POST">
                                                    <a class="btn btn-sm btn-success" href="{{ route('empresas.edit', $empresa->id) }}"><i class="bi bi-pen"></i> {{ __('Edit') }}</a>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="event.preventDefault(); confirm('Are you sure to delete?') ? this.closest('form').submit() : false;"><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
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

    <script>

document.addEventListener('DOMContentLoaded', function () {
        $(document).ready(function() {
            // Inicializa el DataTable con stateSave habilitado
            var tabla = $('#table-empresas').DataTable({
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
                "pageLength": 5,
                "order": [],
                "stateSave": true, // Habilitar stateSave
            });
        });
    });
    </script>

@endsection
