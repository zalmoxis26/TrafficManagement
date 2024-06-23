@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Editar Roles de Usuario</div>

                <div class="card-body">
                    <form action="{{ route('roles.update-roles', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre de Usuario:</label>
                            <input type="text" class="form-control" id="name" value="{{ $user->name }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico:</label>
                            <input type="email" class="form-control" id="email" value="{{ $user->email }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="roles" class="form-label">Roles:</label>
                            <select name="roles[]" id="roles" class="form-select">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
