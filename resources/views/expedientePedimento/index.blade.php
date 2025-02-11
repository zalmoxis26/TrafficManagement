<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Archivos - ADP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --danger-color: #dc3545;
            --background-light: #f8f9fa;
        }

        body {
            background-color: var(--background-light);
        }

        .container-main {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .search-container {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-input {
            padding-left: 2.5rem;
            border-radius: 2rem;
            border: 2px solid var(--primary-color);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
        }

        .card-file {
            transition: all 0.2s ease;
            border: 1px solid rgba(0,0,0,0.125);
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            cursor: pointer;
        }

      

        .file-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .selected {
            border-color: var(--primary-color) !important;
            background-color: rgba(13, 110, 253, 0.1);
        }

        .action-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
            font-size: 0.9rem;
        }

        .breadcrumb-item.active {
            color: var(--primary-color);
            font-weight: 500;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .back-button {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .context-menu {
            position: absolute;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
        }

        .context-menu-item {
            padding: 8px 16px;
            cursor: pointer;
            transition: background 0.2s;
        }

      
    </style>
</head>
<body>
    <div class="container-main">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                @if($parentPath)
                <a href="{{ route('pedimento.index', ['path' => $parentPath]) }}" 
                   class="btn btn-outline-secondary back-button">
                    <i class="bi bi-arrow-left"></i>
                </a>
                @endif
                <h1 class="h3 mb-0">
                    <i class="bi bi-archive me-2"></i>Gestor de Archivos
                </h1>
            </div>
            
            <!-- Botones de Acción -->
            <div class="btn-group action-buttons">
                <button id="btnNuevaCarpeta" class="btn btn-primary" title="Nueva Carpeta">
                    <i class="bi bi-folder-plus"></i>
                </button>
                <button id="btnCargarArchivos" class="btn btn-success" title="Subir Archivos">
                    <i class="bi bi-upload"></i>
                </button>
                <button type="button" class="btn btn-secondary dropdown-toggle" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item" id="btnSeleccionarTodo">
                        <i class="bi bi-check2-all me-2"></i>Seleccionar Todo
                    </button></li>
                    <li><button class="dropdown-item" id="btnDeseleccionar" style="display: none;">
                        <i class="bi bi-x-circle me-2" ></i>Deseleccionar
                    </button></li>
                    <li><button class="dropdown-item" id="btnDescargarSeleccionados">
                        <i class="bi bi-download me-2"></i>Descargar
                    </button></li>
                    <li><button class="dropdown-item text-danger" id="btnEliminarSeleccionados">
                        <i class="bi bi-trash me-2"></i>Eliminar
                    </button></li>
                    <li><button class="dropdown-item" id="btnRenombrarSeleccionado" >
                        <i class="bi bi-pencil-square me-2"></i>Renombrar
                    </button></li>
                </ul>
            </div>
        </div>


         <!-- Nav bar -->
         <nav class="mb-4">
            <ol class="breadcrumb">
                @foreach(explode('/', $currentPath) as $index => $part)
                    @php
                        $pathSoFar = implode('/', array_slice(explode('/', $currentPath), 0, $index + 1));
                    @endphp
                    <li class="breadcrumb-item">
                        @if($index === count(explode('/', $currentPath)) - 1)
                            {{ $part }}
                        @else
                            <a href="{{ route('pedimento.index', ['path' => $pathSoFar]) }}">{{ $part }}</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>


  <!-- Contenedor de Buscador y archivos y carpetas -->  
<div class="container my-4">

    <!-- Buscador -->
    <div class="search-container mb-4">
        <i class="bi bi-search search-icon"></i>
        <input type="text" id="searchInput" class="form-control search-input" 
               placeholder="Buscar archivos o carpetas...">
    </div>

    <!-- Contenedor Scrollable -->
<div class="scrollable-container">
    
    <!-- Contenedor para Resultados de Búsqueda -->
    <div id="resultadosBusqueda" class="row" style="display: none;">
        <!-- Los resultados se cargarán aquí dinámicamente -->
    </div>

     <!-- Loader para Búsqueda -->
    <div id="loaderBusqueda" class="text-center my-4" style="display: none;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Buscando...</span>
        </div>
    </div>

    <!-- Estado Vacío para Búsqueda -->
    <div id="estadoVacioBusqueda" class="empty-state w-100 text-center py-5" style="display: none;">
        <i class="bi bi-file-earmark-x fs-1"></i>
        <h5 class="mt-3">No se encontraron resultados</h5>
        <p class="text-muted">Intenta con otra búsqueda.</p>
    </div>

    <!-- Contenedor de Carpetas y Archivos -->
    <div id="contenidoCarpetas" class="row">
        @foreach($paginated as $item)
            @if($item['type'] === 'folder')
                <div class="col-md-3 mb-4 carpeta-item">
                    <div class="card folder p-3 text-center position-relative fixed-card">
                        <input type="checkbox" class="form-check-input checkbox-select">
                        <i class="bi bi-folder-fill" style="font-size: 2rem; color: #0d6efd;"></i>
                        <p class="mt-2">{{ $item['name'] }}</p>
                    </div>
                </div>                              
            @elseif($item['type'] === 'file')
                <div class="col-md-3 mb-4 archivo-item">
                    <div class="card file p-3 text-center position-relative fixed-card">
                        <input type="checkbox" class="form-check-input checkbox-select">
                        <i class="bi bi-file-earmark-fill" style="font-size: 2rem; color: #198754;"></i>
                        <p class="mt-2">{{ $item['name'] }}</p>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Loader para Infinite Scroll -->
    <div id="loader" class="text-center my-4" style="display: none;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <!-- Estado Vacío para Paginación -->
    @if(count($carpetas) === 0 && count($archivos) === 0)
        <div class="empty-state w-100 text-center py-5">
            <i class="bi bi-folder-x fs-1"></i>
            <h5 class="mt-3">Carpeta Vacía</h5>
            <p class="text-muted">No hay archivos o carpetas en esta ubicación.</p>
        </div>
    @endif

</div> <!-- Fin de .scrollable-container -->


       


    <!-- Modal para Crear Nueva Carpeta -->

<div class="modal fade" id="modalNuevaCarpeta" tabindex="-1" aria-labelledby="modalNuevaCarpetaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formCrearCarpeta">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevaCarpetaLabel">Crear Nueva Carpeta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombreCarpeta" class="form-label">Nombre de la Carpeta</label>
                        <input type="text" class="form-control" id="nombreCarpeta" name="nombreCarpeta" required>
                        <input type="hidden" id="currentPath" name="currentPath" value="{{ $currentPath }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitCarpeta">Crear Carpeta</button>
                </div>
            </div>
        </form>
    </div>
</div>


    <!-- Modal para Renombrar -->
    <div class="modal fade" id="modalRenombrar" tabindex="-1" aria-labelledby="modalRenombrarLabel" aria-hidden="true">
        <div class="modal-dialog">
        <form id="formRenombrar">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRenombrarLabel">Renombrar Elemento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                <label for="nuevoNombre" class="form-label">Nuevo Nombre</label>
                <input type="text" class="form-control" id="nuevoNombre" name="nuevoNombre" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Renombrar</button>
            </div>
            </div>
        </form>
        </div>
    </div>
  



<!-- Modal para Cargar Archivos y Carpetas -->
<div class="modal fade" id="modalCargarArchivos" tabindex="-1" aria-labelledby="modalCargarArchivosLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formCargarArchivos" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCargarArchivosLabel">Cargar Archivos y Carpetas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Tabs para Seleccionar Tipo de Carga -->
                    <ul class="nav nav-tabs" id="cargarArchivosTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="cargar-archivos-tab" data-bs-toggle="tab" data-bs-target="#cargar-archivos" type="button" role="tab" aria-controls="cargar-archivos" aria-selected="true">Cargar Archivos</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cargar-carpetas-tab" data-bs-toggle="tab" data-bs-target="#cargar-carpetas" type="button" role="tab" aria-controls="cargar-carpetas" aria-selected="false">Cargar Carpeta</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="cargarArchivosTabContent">
                        <!-- Tab para Cargar Archivos -->
                        <div class="tab-pane fade show active p-3" id="cargar-archivos" role="tabpanel" aria-labelledby="cargar-archivos-tab">
                            <div class="mb-3">
                                <label for="carpetaDestinoArchivos" class="form-label">Carpeta de Destino</label>
                                <select class="form-select" id="carpetaDestinoArchivos" name="carpetaDestinoArchivos" required>
                                    <option value="{{ $currentPath }}" selected>{{ basename($currentPath) }}</option>
                                    @foreach($carpetas as $carpeta)
                                        @php
                                            $carpetaNombre = basename($carpeta);
                                            $rutaCarpeta = rtrim($currentPath, '/') . '/' . $carpetaNombre;
                                        @endphp
                                        <option value="{{ $rutaCarpeta }}">{{ $carpetaNombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="archivos" class="form-label">Selecciona Archivos</label>
                                <input type="file" class="form-control" id="archivos" name="archivos[]" multiple>
                                <small class="form-text text-muted">Puedes seleccionar uno o varios archivos.</small>
                            </div>
                        </div>
                        <!-- Tab para Cargar Carpetas -->
                        <div class="tab-pane fade p-3" id="cargar-carpetas" role="tabpanel" aria-labelledby="cargar-carpetas-tab">
                            <div class="mb-3">
                                <label for="carpetaDestinoCarpeta" class="form-label">Carpeta de Destino</label>
                                <select class="form-select" id="carpetaDestinoCarpeta" name="carpetaDestinoCarpeta" required>
                                    <option value="{{ $currentPath }}" selected>{{ basename($currentPath) }}</option>
                                    @foreach($carpetas as $carpeta)
                                        @php
                                            $carpetaNombre = basename($carpeta);
                                            $rutaCarpeta = rtrim($currentPath, '/') . '/' . $carpetaNombre;
                                        @endphp
                                        <option value="{{ $rutaCarpeta }}">{{ $carpetaNombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="carpetas" class="form-label">Selecciona Carpetas</label>
                                <input type="file" class="form-control" id="carpetas" name="carpetas[]" multiple webkitdirectory directory>
                                <small class="form-text text-muted">Puedes seleccionar una o varias carpetas. Se cargarán todos los archivos y subcarpetas contenidos.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Cargar</button>
                </div>
            </div>
        </form>
    </div>
</div>






    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 (opcional para alertas más bonitas) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Script Personalizado -->
    <script>

        $(document).ready(function(){
            // ======================================
            // Inicialización de Modales
            // ======================================
            var modalNuevaCarpeta = new bootstrap.Modal(document.getElementById('modalNuevaCarpeta'));
            var modalCargarArchivos = new bootstrap.Modal(document.getElementById('modalCargarArchivos'));
            var modalRenombrar = new bootstrap.Modal(document.getElementById('modalRenombrar')); // Asegúrate de tener este modal en tu HTML
        
            // ======================================
            // Funciones de Utilidad para Escapar HTML
            // ======================================
            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        
            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& significa toda la cadena coincidente
            }
        
            // ======================================
            // Función para Actualizar Estado de Botones
            // ======================================
            function actualizarEstadoBotones() {
                const seleccionados = $('.checkbox-select:checked').length;
                const total = $('.checkbox-select').length;

                if (seleccionados > 0) {
                    $('#btnDeseleccionar').show();
                } else {
                    $('#btnDeseleccionar').hide();
                }

                if (seleccionados === 1) {
                    $('#btnRenombrarSeleccionado').show();
                } else {
                    $('#btnRenombrarSeleccionado').hide();
                }

                // Opcional: Controlar la visibilidad de "Seleccionar Todo"
                if (seleccionados === total && total > 0) {
                    $('#btnSeleccionarTodo').hide();
                } else {
                    $('#btnSeleccionarTodo').show();
                }
            }

            // ======================================
            // Selección y Detección de Elementos
            // ======================================
            // Manejar selección visual al hacer clic en la card (excluyendo el checkbox)

            $(document).on('click', '.folder, .file', function (e) {
                // Si el clic proviene del checkbox, no realizar otra acción
                if ($(e.target).is('.checkbox-select')) {
                    return;
                }

                e.preventDefault(); // Prevenir la navegación del <a> al hacer clic

                var checkbox = $(this).find('.checkbox-select');
                var isChecked = checkbox.prop('checked');
                checkbox.prop('checked', !isChecked);
                $(this).toggleClass('selected', !isChecked);
                actualizarEstadoBotones();
            });

            // Manejar selección visual al hacer clic directamente en el checkbox
            $(document).on('click', '.checkbox-select', function () { // Removed e.preventDefault()
                var card = $(this).closest('.folder, .file');
                var isChecked = $(this).is(':checked');
                card.toggleClass('selected', isChecked);
                actualizarEstadoBotones();
            });

            // ======================================
            // Manejadores para "Seleccionar Todo" y "Deseleccionar"
            // ======================================
            // Manejar "Seleccionar Todo"

            $(document).on('click', '#btnSeleccionarTodo', function () {
                $('.checkbox-select').prop('checked', true);
                $('.folder, .file').addClass('selected');
                actualizarEstadoBotones();
            });

            // Manejar "Deseleccionar"
            $(document).on('click', '#btnDeseleccionar', function () {
                $('.checkbox-select').prop('checked', false);
                $('.folder, .file').removeClass('selected');
                actualizarEstadoBotones();
            });

            // ======================================
            // Navegación al Doble Clic
            // ======================================
            // Manejar navegación al hacer doble clic en una carpeta

            $(document).on('dblclick', '.folder', function (e) {
                var carpeta = $(this).find('p').text();
                var currentPath = '{{ $currentPath }}';
                var newPath = currentPath + '/' + carpeta;

                // Redirigir a la nueva ruta
                window.location.href = '{{ route("pedimento.index") }}?path=' + encodeURIComponent(newPath);
            });

            // Manejar descarga directa de archivos al hacer doble clic
            $(document).on('dblclick', '.file', function (e) {
                var archivo = $(this).find('p').text();
                var currentPath = '{{ $currentPath }}';
                var filePath = currentPath + '/' + archivo;

                // Solicitar la URL del archivo al servidor
                $.ajax({
                    url: '{{ route("pedimento.getFileUrl") }}',
                    method: 'GET',
                    data: {
                        path: filePath,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            window.open(response.url, '_blank');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje,
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo obtener la URL del archivo.',
                        });
                    }
                });
            });








        
            // ======================================
            // Crear Nueva Carpeta
            // ======================================
            // Botón de Nueva Carpeta

            $('#btnNuevaCarpeta').click(function(){
                modalNuevaCarpeta.show();
            });
        
            // Manejar el clic del botón Crear Carpeta
            $('#btnSubmitCarpeta').click(function () {
                var nombreCarpeta = $('#nombreCarpeta').val().trim();
                var currentPath = $('#currentPath').val();
        
                // Validar que el nombre de la carpeta no esté vacío
                if (nombreCarpeta === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El nombre de la carpeta es obligatorio.',
                    });
                    return;
                }
        
                $.ajax({
                    url: '{{ route("pedimento.crearCarpeta") }}', // Ruta para crear la carpeta
                    method: 'POST',
                    data: {
                        nombreCarpeta: nombreCarpeta,
                        currentPath: currentPath,
                        _token: '{{ csrf_token() }}', // Token CSRF para la seguridad
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.mensaje,
                                timer: 2000,
                                showConfirmButton: false,
                            });
        
                            // Cerrar el modal
                            modalNuevaCarpeta.hide();
        
                            // Construir la URL con las variables dinámicas
                            var nuevaRuta = `{{ route('pedimento.index', ['path' => '__path__']) }}`
                                .replace('__path__', encodeURIComponent(`${currentPath}/${nombreCarpeta}`));
        
                            // Agregar la nueva carpeta al DOM sin envolver en <a>
                            var nuevaCarpetaHTML = `
                                <div class="col-md-3 mb-4 carpeta-item">
                                    <div class="card folder p-3 text-center position-relative fixed-card">
                                        <input type="checkbox" class="form-check-input checkbox-select">
                                        <i class="bi bi-folder-fill" style="font-size: 2rem; color: #0d6efd;"></i>
                                        <p class="mt-2">${escapeHtml(nombreCarpeta)}</p>
                                    </div>
                                </div>
                            `;
        
                            $('#contenidoCarpetas').append(nuevaCarpetaHTML);
        
                            // Limpiar el formulario
                            $('#nombreCarpeta').val('');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje || 'No se pudo crear la carpeta.',
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al intentar crear la carpeta.',
                        });
                        console.error(xhr.responseText); // Registrar el error en la consola
                    },
                });
            });



             // ======================================
            // Cargar Archivos y Carpetas
            // ======================================

            // Botón de Cargar Archivos
            $('#btnCargarArchivos').click(function(){
                modalCargarArchivos.show();
            });

            // Función para actualizar los atributos 'required' según la pestaña activa
            function actualizarRequeridos(tabId) {
                if(tabId === '#cargar-archivos'){
                    $('#archivos').attr('required', 'required');
                    $('#carpetas').removeAttr('required');
                } else if(tabId === '#cargar-carpetas'){
                    $('#carpetas').attr('required', 'required');
                    $('#archivos').removeAttr('required');
                }
            }

            // Evento al cambiar de pestaña
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                var target = $(e.target).attr("data-bs-target");
                actualizarRequeridos(target);
            });

            // Inicializar los 'required' al cargar la página
            var activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');
            actualizarRequeridos(activeTab);

            // Manejar el formulario de Cargar Archivos y Carpetas
            $('#formCargarArchivos').submit(function(e){
                e.preventDefault();
                
                const formData = new FormData();
                let currentPath = '';

                const activeTab = $('.nav-tabs .nav-link.active').attr('data-bs-target');

                if (activeTab === '#cargar-archivos') {
                    currentPath = $('#carpetaDestinoArchivos').val();
                    const archivos = $('#archivos')[0].files;
                    if (archivos.length > 0) {
                        Array.from(archivos).forEach((file) => {
                            formData.append('archivos[]', file);
                        });
                    }
                } else if (activeTab === '#cargar-carpetas') {
                    currentPath = $('#carpetaDestinoCarpeta').val();
                    const carpetas = $('#carpetas')[0].files;
                    if (carpetas.length > 0) {
                        Array.from(carpetas).forEach(file => {
                            // **Preservar la estructura completa de carpetas**
                            const relativePath = file.webkitRelativePath; // Eliminamos la manipulación que elimina la carpeta
                            formData.append(`carpetasArchivos[${relativePath}]`, file);
                        });
                    }
                }

                formData.append('currentPath', currentPath);
                formData.append('_token', '{{ csrf_token() }}');

                // Mostrar loader
                const $submitBtn = $(this).find('button[type="submit"]');
                $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');

                $.ajax({
                    url: '{{ route("pedimento.cargarArchivos") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.mensaje,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Actualizar solo el contenido necesario
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            // Mostrar mensaje de error con SweetAlert2
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: response.mensaje,
                                scrollbarPadding: false,
                                customClass: {
                                    container: 'swal2-height-auto'
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Error desconocido';
                        if (xhr.responseJSON) {
                            errorMsg = xhr.responseJSON.mensaje || 
                                    (xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).join('<br>') : xhr.statusText);
                        }
                        // Mostrar mensaje de error con SweetAlert2
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMsg,
                            scrollbarPadding: false,
                            customClass: {
                                container: 'swal2-height-auto'
                            }
                        });
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).html('Subir archivos');
                    }
                });
            });
   




        // ======================================
// Renombrar Elemento Seleccionado
// ======================================

// Botón Renombrar Seleccionado
$('#btnRenombrarSeleccionado').on('click', function () {
    // Obtener el elemento seleccionado
    var cardSeleccionada = $('.checkbox-select:checked').closest('.folder, .file');
    var nombreActual = cardSeleccionada.find('p').text();
    $('#nuevoNombre').val(nombreActual);
    modalRenombrar.show();
});

// Manejar el envío del formulario de renombrado
$('#formRenombrar').on('submit', function (e) {
    e.preventDefault();

    var nuevoNombre = $('#nuevoNombre').val().trim();
    if (nuevoNombre === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Nombre Inválido',
            text: 'Por favor, ingresa un nombre válido.',
        });
        return;
    }

    // Obtener el elemento seleccionado
    var cardSeleccionada = $('.checkbox-select:checked').closest('.folder, .file');
    var nombreActual = cardSeleccionada.find('p').text();
    var currentPath = '{{ $currentPath }}';
    var pathCompleto = currentPath.replace(/\/$/, "") + '/' + nombreActual; // Evitar doble slash

    // Determinar si es una carpeta (si el elemento tiene una clase específica o algún indicador)
    var isFolder = cardSeleccionada.hasClass('folder'); // Asegúrate de que 'folder' es la clase correcta

    if (isFolder) {
        pathCompleto += '/'; // Añadir '/' al final para indicar que es una carpeta
    }

    // Enviar la solicitud de renombrado al servidor
    $.ajax({
        url: '{{ route("archivo.renombrar") }}', // Ruta Correcta
        method: 'POST',
        data: {
            path: pathCompleto,
            nuevoNombre: nuevoNombre,
            _token: '{{ csrf_token() }}'
        },
        success: function (response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Renombrado',
                    text: response.mensaje || 'Elemento renombrado correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
                // Recargar la página para reflejar los cambios
                window.location.reload();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.mensaje || 'No se pudo renombrar el elemento.',
                });
            }
        },
        error: function (xhr) {
            let errorMsg = 'Error desconocido';
            if(xhr.responseJSON && xhr.responseJSON.mensaje){
                errorMsg = xhr.responseJSON.mensaje;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg,
            });
        }
    });

    // Cerrar el modal
    modalRenombrar.hide();
});




        
            // ======================================
            // Descargar Seleccionados
            // ======================================

            $('#btnDescargarSeleccionados').click(function(){
                var seleccionados = [];
                
                // Recoger seleccionados en #contenidoCarpetas
                $('#contenidoCarpetas .selected').each(function(){
                    var nombre = $(this).find('p').text();
                    seleccionados.push(nombre);
                });
        
                // Recoger seleccionados en #resultadosBusqueda
                $('#resultadosBusqueda .selected').each(function(){
                    var nombre = $(this).find('p').text();
                    seleccionados.push(nombre);
                });
        
                if(seleccionados.length === 0){
                    Swal.fire({
                        icon: 'warning',
                        title: 'No hay elementos seleccionados',
                        text: 'Por favor, selecciona al menos una carpeta o archivo para descargar.',
                    });
                    return;
                }
        
                // Enviar los elementos seleccionados al servidor para crear el ZIP
                $.ajax({
                    url: '{{ route("pedimento.descargarElementos") }}',
                    method: 'POST',
                    data: {
                        elementos: seleccionados,
                        currentPath: '{{ $currentPath }}',
                        _token: '{{ csrf_token() }}'
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(blob){
                        var link = document.createElement('a');
                        var url = window.URL.createObjectURL(blob);
                        link.href = url;
                        link.download = 'descarga.zip';
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        window.URL.revokeObjectURL(url);
                    },
                    error: function(){
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron descargar los elementos.',
                        });
                    }
                });
            });
        
            










        







            $('#btnEliminarSeleccionados').click(function(){
                var seleccionados = [];
                
                // Obtener rutas completas desde atributos data
                $('#contenidoCarpetas .selected, #resultadosBusqueda .selected').each(function(){
                    const cardSeleccionada = $(this).closest('.folder, .file'); // Corrección: usar $(this) para el elemento actual
                    const nombreActual = cardSeleccionada.find('p').text().trim();
                    let pathCompleto = `${currentPath.replace(/\/$/, '')}/${nombreActual}`;
                    
                    // Verificar si es una carpeta y agregar '/' al final
                    if (cardSeleccionada.hasClass('folder')) { // Asumiendo que las carpetas tienen la clase 'folder'
                        pathCompleto += '/';
                    }
                    
                    seleccionados.push(pathCompleto);
                });

                if(seleccionados.length === 0){
                    Swal.fire({
                        icon: 'warning',
                        title: 'No hay elementos seleccionados',
                        text: 'Por favor, selecciona al menos una carpeta o archivo para eliminar.',
                    });
                    return;
                }

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Vas a eliminar ${seleccionados.length} elemento(s). Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if(result.isConfirmed){
                        $.ajax({
                            url: '{{ route("pedimento.eliminarElementos") }}',
                            method: 'POST',
                            data: {
                                paths: seleccionados,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response){
                                if(response.success){
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Eliminado',
                                        text: response.mensaje,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                   
                                     window.location.reload();
                                } else {
                                    let mensajeError = response.mensaje;
                                    if(response.failed){
                                        mensajeError += '\nNo se pudieron eliminar: ' + response.failed.join(', ');
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: mensajeError,
                                    });
                                }
                            },
                            error: function(xhr){
                                let errorMsg = xhr.responseJSON?.mensaje || 'Error desconocido';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMsg,
                                });
                            }
                        });
                    }
                });
            });





















        
            // ======================================
            // Búsqueda y Paginación (Infinite Scroll)
            // ======================================
            var page = {{ $page }};
            var perPage = {{ $perPage }};
            var totalItems = {{ $combinedCount }};
            var loading = false;
            var currentPath = "{{ $currentPath }}";
        
            // Variables para Búsqueda
            var debounceTimer;
            var debounceDelay = 300;
            var currentRequest = null;
        
            // Variables para Búsqueda Paginada
            var isSearchActive = false;
            var currentSearchQuery = '';
            var currentSearchPage = 1;
            var hasMoreSearchResults = true;
        
            $('#searchInput').on('input', function() {
                var query = $(this).val().trim();
                
                // Cancelar petición anterior
                if(currentRequest) currentRequest.abort();
                
                clearTimeout(debounceTimer);
                
                debounceTimer = setTimeout(function() {
                    if(query.length < 2) {
                        // Si la búsqueda es menor a 2 caracteres, limpiar y mostrar contenido paginado
                        isSearchActive = false;
                        currentSearchQuery = '';
                        currentSearchPage = 1;
                        hasMoreSearchResults = true;
        
                        $('#resultadosBusqueda').empty().hide();
                        $('#estadoVacioBusqueda').hide();
                        $('#contenidoCarpetas').show();
                        $('.empty-state').hide();
                        return;
                    }
                    
                    // Iniciar nueva búsqueda
                    isSearchActive = true;
                    currentSearchQuery = query;
                    currentSearchPage = 1;
                    hasMoreSearchResults = true;
        
                    realizarBusqueda(query, currentSearchPage);
                }, debounceDelay);
            });
            
        
            function realizarBusqueda(query, page = 1) {
                currentRequest = $.ajax({
                    url: '{{ route("pedimento.buscar") }}',
                    method: 'GET',
                    data: { 
                        query: query, 
                        path: currentPath,
                        page: page
                    },
                    beforeSend: function() {
                        $('#loaderBusqueda').show();
                        $('#contenidoCarpetas').hide();
                        $('.empty-state').hide();
                        $('#resultadosBusqueda').show();
                        if(page === 1){
                            $('#resultadosBusqueda').empty();
                        }
                    },
                    success: function(response) {
                        $('#loaderBusqueda').hide();
                        loading = false;
        
                        if(response.error) {
                            $('#resultadosBusqueda').html(`
                                <div class="col-12 text-center py-4">
                                    <div class="alert alert-warning">${response.error}</div>
                                </div>
                            `);
                            hasMoreSearchResults = false;
                            return;
                        }
                        
                        if(response.items.length === 0 && page === 1) {
                            $('#resultadosBusqueda').html(`
                                <div class="col-12 text-center py-5">
                                    <i class="bi bi-search-x fs-1 text-muted"></i>
                                    <h5 class="mt-3">No se encontraron resultados</h5>
                                    <p class="text-muted">Para: "${query}"</p>
                                </div>
                            `);
                            hasMoreSearchResults = false;
                            return;
                        }
        
                        var html = '';
                        response.items.forEach(function(item) {
                            html += `
                            <div class="col-md-3 mb-4">
                                <a href="{{ route('pedimento.index') }}?path=${encodeURIComponent(item.path)}" class="text-decoration-none">
                                    <div class="card ${item.type} p-3 text-center position-relative fixed-card">
                                        <input type="checkbox" class="form-check-input checkbox-select">
                                        <i class="bi ${item.type === 'folder' ? 'bi-folder-fill text-primary' : 'bi-file-earmark-fill text-success'}" 
                                           style="font-size: 2rem;"></i>
                                        <p class="mt-2">${escapeHtml(item.name).replace(new RegExp(escapeRegExp(query), 'gi'), '<span class="search-highlight">$&</span>')}</p>
                                    </div>
                                </a>
                            </div>`;
                        });
                        
                        $('#resultadosBusqueda').append(html);
        
                        // Si no hay más resultados, desactivar el flag
                        if(response.next_page === null){
                            hasMoreSearchResults = false;
                        }
                    },
                    error: function(xhr) {
                        if(xhr.statusText !== "abort") {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.status === 0 ? 'Verifica tu conexión a internet' : 'Error en la búsqueda'
                            });
                        }
                    },
                    complete: function() {
                        $('#loaderBusqueda').hide();
                        currentRequest = null;
                    }
                });
            }
        
            // Paginación Dinámica (Infinite Scroll)
            $('.scrollable-container').on('scroll', function() {
                if(loading) return;
        
                // Verifica si el usuario ha hecho scroll cerca del fondo del contenedor
                if($(this).scrollTop() + $(this).innerHeight() > this.scrollHeight - 100){
                    if(isSearchActive){
                        if(!hasMoreSearchResults){
                            return; // No hay más resultados de búsqueda
                        }
        
                        loading = true;
                        $('#loaderBusqueda').show();
                        currentSearchPage +=1;
                        realizarBusqueda(currentSearchQuery, currentSearchPage);
                    } else {
                        if((page * perPage) >= totalItems){
                            return; // No hay más páginas para cargar
                        }
        
                        loading = true;
                        $('#loader').show();
                        page += 1;
        
                        $.ajax({
                            url: '{{ route("pedimento.index") }}',
                            method: 'GET',
                            data: {
                                path: currentPath,
                                page: page
                            },
                            success: function(response){
                                if(response.items && response.items.length > 0){
                                    response.items.forEach(function(item){
                                        var html = '';
                                        if(item.type === 'folder'){
                                            html += '<div class="col-md-3 mb-4 carpeta-item">';
                                            html += '    <a href="{{ route("pedimento.index") }}?path=' + encodeURIComponent(item.path) + '" class="text-decoration-none">';
                                            html += '        <div class="card folder p-3 text-center position-relative fixed-card">';
                                            html += '            <input type="checkbox" class="form-check-input checkbox-select">';
                                            html += '            <i class="bi bi-folder-fill" style="font-size: 2rem; color: #0d6efd;"></i>';
                                            html += '            <p class="mt-2">' + escapeHtml(item.name) + '</p>';
                                            html += '        </div>';
                                            html += '    </a>';
                                            html += '</div>';
                                        } else if(item.type === 'file'){
                                            html += '<div class="col-md-3 mb-4 archivo-item">';
                                            html += '    <div class="card file p-3 text-center position-relative fixed-card">';
                                            html += '        <input type="checkbox" class="form-check-input checkbox-select">';
                                            html += '        <i class="bi bi-file-earmark-fill" style="font-size: 2rem; color: #198754;"></i>';
                                            html += '        <p class="mt-2">' + escapeHtml(item.name) + '</p>';
                                            html += '    </div>';
                                            html += '</div>';
                                        }
                                        $('#contenidoCarpetas').append(html);
                                    });
        
                                    $('#loader').hide();
                                    loading = false;
                                } else {
                                    // No hay más elementos para cargar
                                    $('#loader').hide();
                                    loading = false;
                                }
                            },
                            error: function(xhr){
                                $('#loader').hide();
                                loading = false;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudieron cargar más archivos.',
                                });
                            }
                        });
                    }
                }
            });
        
        });
        </script>
        



<!-- Estilos adicionales DE LA BARRA DE BUSQUEDA-->
<style>
    .empty-search {
        opacity: 0;
        animation: fadeIn 0.5s ease forwards;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .search-highlight {
        background-color: #fff3cd;
        font-weight: 500;
    }
    </style>


<style>
    /* Contenedor Scrollable */
    .scrollable-container {
        max-height: 80vh; /* 80% del alto de la ventana */
        overflow-y: auto;
        padding-right: 15px; /* Espacio para el scrollbar */
    }

    /* Scrollbar Personalizado */
    .scrollable-container::-webkit-scrollbar {
        width: 8px;
    }

    .scrollable-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .scrollable-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .scrollable-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Scrollbar para Firefox */
    .scrollable-container {
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }

    /* Cards de Carpetas y Archivos con Tamaño Fijo */
    .fixed-card {
        height: 150px; /* Ajusta según tus necesidades */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    /* Estado Vacío Centrado */
    .empty-state {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100%;
    }


    .fixed-card:hover{
            background: #f8f9fa;
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }

</style>


</body>
</html>
