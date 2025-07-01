<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{$title}}</title>

    <link rel="shortcut icon" href="/logo-sm.png">
    <link href="/assets/vendor/fullcalendar/main.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/vendor/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/vendor/daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />
    <link href="/assets/vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css">
    <link href="/assets/vendor/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css" />

    <script rel="stylesheet" src="/assets/js/config.js"></script>
    <link href="/assets/css/app.css" rel="stylesheet" type="text/css" id="app-style" />
    <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/toastr.min.css">
    <link rel="stylesheet" type="text/css" href="/css/style.css">

    <link href="/bs5-tour/css/bs5-intro-tour.css" rel="stylesheet"/>

    <link rel='stylesheet' href='/css/bootstrap-duallistbox.min.css'/>
    
    @yield('css')


</head>
<body>

    <div class="loader"></div>
    @if(isset(Auth::user()->empresa->empresa))
    <input type="hidden" value="{{ Auth::user()->empresa->empresa->id }}" id="empresa_id">
    @endif
    <input type="hidden" value="{{ Auth::user()->id }}" id="usuario_id">

    <div class="wrapper">
        <!-- ========== Topbar Start ========== -->
        <div class="navbar-custom">
            <div class="topbar container-fluid">
                <div class="d-flex align-items-center gap-lg-2 gap-1" id="step1">
                    <form class="d-flex w-100">
                        <input class="form-control me-2" type="search" placeholder="Período geral" aria-label="Search">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-arrow-clockwise"></i></button>
                    </form>
                    <div>
                        <i class="bi bi-bell fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- ========== Topbar End ========== -->

        <div class="d-flex">
            <!-- Sidebar -->
            <nav class="sidebar p-3">
                <div class="logo mb-4 text-center">
                    <img src="/logo-sm.png" alt="Logo" style="width: 60px;">
                    <div class="small">{{ Auth::user()->empresa->empresa->cnpj ?? 'CNPJ' }}</div>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active" href="/"><i class="bi bi-house"></i> Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-folder"></i> Cadastros</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-arrow-left-right"></i> Movimentações</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-cash-stack"></i> Financeiro</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-info-circle"></i> Informações</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-gear"></i> Configurações</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-person"></i> Usuário</a></li>
                </ul>
            </nav>
            <!-- Main Content -->
            <div class="flex-grow-1">
                <!-- Dashboard Content -->
                <div class="container-fluid py-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('js')
</body>
</html>
