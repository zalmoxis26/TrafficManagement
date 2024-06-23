@extends('layouts.app')

@section('template_title')
    {{ $usersEmpresa->name ?? __('Show') . " " . __('Users Empresa') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Users Empresa</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('users-empresas.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>User Id:</strong>
                                    {{ $usersEmpresa->user_id }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Empresa Id:</strong>
                                    {{ $usersEmpresa->empresa_id }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
