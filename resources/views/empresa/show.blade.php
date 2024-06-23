@extends('layouts.app')

@section('template_title')
    {{ $empresa->name ?? __('Show') . " " . __('Empresa') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Empresa</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('empresas.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Clave:</strong>
                                    {{ $empresa->clave }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Descripcion:</strong>
                                    {{ $empresa->descripcion }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Rfc:</strong>
                                    {{ $empresa->rfc }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
