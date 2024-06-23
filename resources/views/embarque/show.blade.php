@extends('layouts.app')

@section('template_title')
    {{ $embarque->name ?? __('Show') . " " . __('Embarque') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Embarque</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('embarques.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Numeconomico:</strong>
                                    {{ $embarque->numEconomico }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Entregado:</strong>
                                    {{ $embarque->entregado }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Desaduanado:</strong>
                                    {{ $embarque->Desaduanado }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Clavenombre:</strong>
                                    {{ $embarque->claveNombre }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Tipooper:</strong>
                                    {{ $embarque->tipoOper }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Claveaduana:</strong>
                                    {{ $embarque->claveAduana }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fechaembarque:</strong>
                                    {{ $embarque->fechaEmbarque }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
