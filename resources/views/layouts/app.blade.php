<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('template_title', config('app.name', 'Tracking Agencia SAI'))</title>
    <link rel="icon" href="{{ asset('storage/SAI-Solucion_Aduanal_integral_logo.png') }}" type="image/jpeg">


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- POOPER -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
     <!-- Jquery -->    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- js bootrap-->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
         

    <!-- Scripts-->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

     <!-- DataTables CSS-->
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">

<!-- DataTables JS
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script> -->

   


    <style>
        .card-header{
            font-weight: bold;
            color:white;
            background-color: #212529;
            font-size: 1.3em;
        }

        body{
            background-color:silver;
        }
       
    </style>    

    <style>
        
        .fixed-bottom-right {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.5rem; /* Espacio entre los toasts */
    }
    .toast-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }


    </style>


</head>
<body>


    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark  shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('storage/SolucionAduanal-Integral-SAI-LOGO.png') }}" alt="LogoSAI" width="18%"/><span> Tracking Management System</span>
                </a>

              
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <!-- Botón para abrir el modal -->
   
                    </ul>
                       
                    <!-- Right Side Of Navbar -->

                    <ul class="navbar-nav ms-auto">
                        
                     
  
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Iniciar Sesion') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Registrarse') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-start" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Cerrar Sesion') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <!-- JSON CON LAS EMPRESAS DEL USUARIO AUTENTIFICADO -->

        <script>
            window.empresasDelUsuario = @json(optional(auth()->user())->empresas ? auth()->user()->empresas->pluck('empresa_id') : []);
        </script>
        
        
        
        
        

        <!--TOAST CONTAINER -->
        
        <div id="toast-container" class="toast-container fixed-bottom-right"></div>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!--SCRIPT PARA ELIMINAR LOS SUCCESS ALERTS -->

    <script>
        setTimeout(function() {
            $('.alert-success').fadeOut('fast');
        }, 3000); // 3 segundos

        setTimeout(function() {
            $('.alert-danger').fadeOut('fast');
        }, 3000); // 3 segundos


    </script>

    


    <style>
        .dropdown-toggle::after {
          display: none;
        }

        .rounded-btn {
            border-radius: 5px;
        }

        .hover-btn{
            background-color: #000000;
        }

        .hover-btn:hover {
            background-color: #880024; /* Cambia este color al que prefieras */
            }
        </style>





    
</body>
</html>
