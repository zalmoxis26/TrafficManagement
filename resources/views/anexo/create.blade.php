@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Anexo
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="card card-default">
                    <div class="card-header">
                        <span class="card-title">{{ __('Create') }} Anexo</span>
                    </div>
                    <div class="card-body bg-white">
                        <!-- Formulario para crear un anexo -->
                        <form action="{{ route('anexo.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <!-- Campo oculto para trafico_id -->
                            <input type="hidden" name="trafico_id" value="{{ $traficoId }}">
                            
                            <!-- Campo de entrada de archivos -->
                            <div class="mb-3">
                                <label for="archivo" class="form-label">Adjuntar Archivo</label>
                                <input type="file" class="form-control" id="archivo" name="archivo" required>
                            </div>
                            
                            <!-- Campo de texto para la descripción del adjunto -->
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                            </div>
                            
                            <!-- Botón para enviar el formulario -->
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
