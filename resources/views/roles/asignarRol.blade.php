@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Asignar Rol a Usuario</div>

                <div class="card-body">
                    <form action="{{ route('users.update-roles') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Lista de Usuarios:</label>
                            <select name="user_id" id="user_id" class="form-select">
                                <option value="">---SELECCIONE USUARIO A ASIGNAR---</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Lista de Roles:</label>
                            <select name="role_id" id="role_id" class="form-select">
                                <option value="">---SELECCIONE UN ROL A ASIGNAR---</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="float-end">
                        <button type="submit" class="btn btn-dark">ASIGNAR</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
