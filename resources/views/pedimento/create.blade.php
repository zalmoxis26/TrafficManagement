@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Pedimento
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Crear') }} Pedimento para Trafico: <strong>{{$trafico->id}}</strong></span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('pedimentos.store') }}"  role="form" enctype="multipart/form-data">
                            @csrf

                            @include('pedimento.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
