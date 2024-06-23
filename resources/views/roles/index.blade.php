@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Lista de Usuarios y Roles</h1>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Usuarios</h5>
                <a href="{{ route('users.assign-roles') }}" class="btn btn-primary">Asignar Roles</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="table-roles">
                    <thead class="thead table-dark">
                        <tr>
                            <th>Nombre</th>
                            <th>Correo Electrónico</th>
                            <th  class="text-center">Rol Asignado</th>
                            <th  class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center">
                                @foreach($user->roles as $role)
                                    @if($role->name == 'revisor')
                                        <span class="badge bg-success fs-6">{{ $role->name }}</span>
                                    @elseif($role->name == 'documentador')
                                        <span class="badge bg-primary fs-6">{{ $role->name }}</span>
                                    @elseif($role->name == 'guest')
                                        <span class="badge bg-warning fs-6">{{ $role->name }}</span>
                                    @elseif($role->name == 'cliente')
                                        <span class="badge bg-dark fs-6">{{ $role->name }}</span>
                                    @elseif($role->name == 'admin')
                                        <span class="badge bg-danger fs-6">{{ $role->name }}</span>
                                    @endif
                                @endforeach
                            </td>
                            <td  class="text-center">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-success btn-sm"><i class="bi bi-pencil"></i> Editar</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    <!--SCRIPTS DE DATATABLE -->
    <script>
        $(document).ready(function() {
            // Inicializa el DataTable con stateSave habilitado
            var tabla = $('#table-roles').DataTable({
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
                "columnDefs": [
                    { "type": "date", targets: [2] } // Ajusta el índice según tu estructura.
                ]
        });
    });

</script>


@endsection
