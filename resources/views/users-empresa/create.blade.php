@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Users Empresa
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-8 mx-auto">

                <div class="card card-default">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        
                      

                        <span class="card-title">Asignar Empresas a los Usuarios </span>

                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle fs-4 pt-0 pb-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-list"></i>
                            </button>
                    
                            <!-- Dropdown -->
                            <div class="dropdown-menu dropdown-menu-lg-end p-3 me-3" aria-labelledby="dropdownMenuButton">
                                <!-- Botones dentro del dropdown -->
                                <a href="{{route('empresas.index')}}" class="btn dropdown-item text-white fs-5 text-center rounded-btn hover-btn">Listado de Empresas</a>
                                <a  href="{{ route('empresas.create') }}" class="btn dropdown-item mt-2 text-white fs-5 text-center rounded-btn hover-btn">Agregar Empresas</a>
                                <a href="{{ route('users-empresas.index') }}" class="btn dropdown-item mt-2 text-white fs-5 text-center rounded-btn hover-btn">Listado Empresas Asignadas</a>
                                <a href="{{ route('users-empresas.create') }}" class="btn dropdown-item mt-2 mb-2 text-white fs-5 text-center rounded-btn hover-btn">Asignar Empresas</a>
                            </div>
                        </div>
                        
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('users-empresas.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('users-empresa.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
