@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Pedimento
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Update') }} Pedimento</span>
                    </div>
                    <div class="card-body bg-white">
                        <form method="POST" action="{{ route('pedimentos.update', $pedimento->id) }}"  role="form" enctype="multipart/form-data">
                            {{ method_field('PATCH') }}
                            @csrf

                            @include('pedimento.form')

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
