@extends('layouts.app')

@section('template_title')
    {{ $revisione->name ?? __('Show') . " " . __('Revisione') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Revisione</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('revisiones.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Nombrerevisor:</strong>
                                    {{ $revisione->nombreRevisor }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Iniciorevision:</strong>
                                    {{ $revisione->inicioRevision }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Finrevision:</strong>
                                    {{ $revisione->finRevision }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Tiemporevision:</strong>
                                    {{ $revisione->tiempoRevision }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
