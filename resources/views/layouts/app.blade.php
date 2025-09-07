<!DOCTYPE html>
<html lang="id" class="light-style layout-navbar-fixed" data-theme="theme-default"
      data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template-free">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Katalog Produk | POS')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <!-- Sneat Core -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css">
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
    <style>
        body { background: #f7f7fb; }
        .navbar-brand { font-weight: 600; letter-spacing: 1px; font-size: 1.3rem; }
        .sneat-navbar {
            background: #fff; border-bottom: 1px solid #e3e3e3;
            position: sticky; top: 0; z-index: 1030;
            transition: box-shadow 0.2s;
        }
        .navbar-toggler {
            border: none;
        }
        .navbar-toggler:focus {
            box-shadow: none;
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Sneat Navbar -->
    <nav class="navbar navbar-expand-lg sneat-navbar shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home.katalog') }}">
                <i class="bx bx-store-alt bx-sm me-2" style="color:#696cff"></i>
                Shop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="bx bx-menu bx-md"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    {{-- Keranjang --}}
                    @php
                        $userId = auth()->check() ? auth()->id() : 'guest';
                        $sessionKey = 'keranjang_' . $userId;
                        $keranjang = session($sessionKey, []);
                        $totalKeranjang = array_sum(array_column($keranjang, 'qty'));
                    @endphp
                    <li class="nav-item me-2">
                        <a class="nav-link position-relative" href="{{ route('home.keranjang') }}">
                            <i class="bx bx-cart bx-sm"></i>
                            <span class="visually-hidden">Keranjang</span>
                            <span id="keranjang-badge"
                                  class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle"
                                  style="font-size: 0.7em;{{ $totalKeranjang ? '' : 'display:none;' }}">
                                {{ $totalKeranjang }}
                            </span>
                        </a>
                    </li>
                    {{-- User menu --}}
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-user"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('home.profile') }}">
                                        <i class="bx bx-user-circle me-1"></i> Profil Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('home.myorders.index') }}">
                                        <i class="bx bx-receipt me-1"></i> Pesanan Saya
                                    </a>
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bx bx-power-off me-1"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}"><i class="bx bx-log-in"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}"><i class="bx bx-user-plus"></i> Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-xxl">
        @yield('content')
    </div>

    <!-- Sneat Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>
