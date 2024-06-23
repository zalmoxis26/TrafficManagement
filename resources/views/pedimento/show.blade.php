@extends('layouts.app')

@section('template_title')
    {{ $pedimento->name ?? __('Show') . " " . __('Pedimento') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Pedimento</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('pedimentos.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Numpedimento:</strong>
                                    {{ $pedimento->numPedimento }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Aduana:</strong>
                                    {{ $pedimento->aduana }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Patente:</strong>
                                    {{ $pedimento->patente }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Claveped:</strong>
                                    {{ $pedimento->clavePed }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fechaped:</strong>
                                    {{ $pedimento->fechaPed }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Adjunto:</strong>
                                    {{ $pedimento->adjunto }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
