@extends('layouts.app')

@section('template_title')
    Traficos
@endsection

@section('content')

    <style>
        .directory-row {
            background-color: royalblue;
            font-weight: bold;
            color: white; /* Cambiar el color del texto para que sea legible en el fondo azul */
        }

        body {
            background-color: white;
        }

        .content-box {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 8px;
            background-color: #ffffff;
        }

        .directory-path {
            font-size: 1.2em;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .dataTables_wrapper .dataTables_filter input {
            margin-left: 0.5em;
        }

        .dataTables_wrapper .dataTables_length select {
            margin-left: 0.5em;
        }
    </style>

    <div class="container mt-5 content-box">
        <h1 class="mb-4">Documentos FTP</h1>

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

            
            @if (!empty($errorMsg))
    <div class="alert alert-danger">{{ $errorMsg }}</div>
@endif



        <div class="directory-path">
            Carpeta actual: {{ $directory }}
            @if ($directory != '/')
                <a id="btnVolver" href="{{ route('documents.index', ['directory' => urlencode(dirname($directory))]) }}" class="btn btn-info mb-3 float-end text-white">Volver</a>
            @endif
        </div>

        <table id="documentsTable" class="table table-bordered table-hover">
            <thead class="thead-dark table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <!-- Mostrar directorios -->
                @foreach ($directories as $directory)
                    <tr class="directory-row text-center">
                        <td class="bg-secondary-subtle">{{ basename($directory['path']) }}</td>
                        <td class="bg-secondary-subtle">Carpeta</td>
                        <td class="text-center bg-secondary-subtle">
                            <a href="{{ route('documents.index', ['directory' => urlencode($directory['path'])]) }}" class="btn btn-secondary">Abrir Carpeta</a>
                        </td>
                    </tr>
                @endforeach

                <!-- Mostrar archivos -->
                @foreach ($files as $file)
                    <tr class="text-center">
                        <td>{{ basename($file['path']) }}</td>
                        <td class="text-center">Archivo</td>
                        <td class="text-center">
                            <a href="{{ route('documents.download', urlencode($file['path'])) }}" class="btn btn-primary">Descargar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- jQuery, Bootstrap, DataTables JavaScript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#documentsTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json" // Ajusta el idioma si es necesario
                },
                "rowCallback": function(row, data, index) {
                    if ($(row).find('td:nth-child(2)').text() === 'Carpeta') {
                        $(row).addClass('directory-row');
                    }
                }
            });
        });
    </script>
@endsection
