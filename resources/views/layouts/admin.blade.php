<!DOCTYPE html>
<html lang="id"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin POS</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    @stack('styles')
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Sidebar -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ route('dashboard.admin') }}" class="app-brand-link">
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">Shop</span>
                    </a>
                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>
                <div class="menu-inner-shadow"></div>
                <ul class="menu-inner py-1">
                    <li class="menu-item {{ request()->routeIs('dashboard.admin') ? 'active' : '' }}">
                        <a href="{{ route('dashboard.admin') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div>Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Verifikasi & Master Data</span></li>
                    <li class="menu-item {{ request()->routeIs('users.verifikasi') ? 'active' : '' }}">
                        <a href="{{ route('users.verifikasi') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user-check"></i>
                            <div>Verifikasi Pengguna Baru</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('categories.index') ? 'active' : '' }}">
                        <a href="{{ route('categories.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-category"></i>
                            <div>Kelola Kategori</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('products.index') ? 'active' : '' }}">
                        <a href="{{ route('products.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-cube"></i>
                            <div>Kelola Produk Toko</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('suppliers.index') ? 'active' : '' }}">
                        <a href="{{ route('suppliers.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bxs-truck"></i>
                            <div>Kelola Supplier</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('mitras.index') ? 'active' : '' }}">
                        <a href="{{ route('mitras.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user-voice"></i>
                            <div>Kelola Mitra</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('customers.index') ? 'active' : '' }}">
                        <a href="{{ route('customers.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user"></i>
                            <div>Kelola Customer</div>
                        </a>
                    </li>
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Transaksi & Laporan</span></li>
                    <li class="menu-item {{ request()->routeIs('orders.index') ? 'active' : '' }}">
                        <a href="{{ route('orders.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-cart"></i>
                            <div>Transaksi Penjualan</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('laporan.*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-bar-chart-square"></i>
                            <div>Laporan</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('laporan.penjualan') ? 'active' : '' }}">
                                <a href="{{ route('laporan.penjualan') }}" class="menu-link">Penjualan Harian/Bulanan</a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('laporan.produk_terlaris') ? 'active' : '' }}">
                                <a href="{{ route('laporan.produk_terlaris') }}" class="menu-link">Produk Terlaris</a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('laporan.pembelian') ? 'active' : '' }}">
                                <a href="{{ route('laporan.pembelian') }}" class="menu-link">Pembelian Supplier</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </aside>
            <!-- /Sidebar -->

            <!-- Layout page (content & topbar) -->
            <div class="layout-page">
                <!-- Navbar -->
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" data-bs-toggle="dropdown">
                                    <i class="bx bx-bell bx-tada"></i>
                                    @if($notif_unread_count > 0)
                                        <span class="badge bg-danger">{{ $notif_unread_count }}</span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width:280px">
                                    <li class="dropdown-header">Produk Supplier Baru</li>
                                    @forelse($notif_products as $np)
                                    <li>
                                        <a href="{{ route('admin.products.show', [$np->id]) }}"
                                           class="dropdown-item d-flex align-items-start py-2 {{ $np->notif_admin_seen ? 'bg-light text-muted' : '' }}">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">
                                                    {{ $np->nama_produk }}
                                                    @if($np->notif_admin_seen)
                                                        <span class="badge bg-secondary ms-2">Dibaca</span>
                                                    @endif
                                                </div>
                                                <div class="small text-muted">
                                                    Supplier: {{ $np->supplier->name ?? '-' }}<br>
                                                    <span class="text-muted">{{ $np->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </a>

                                    </li>
                                    @empty
                                    <li>
                                        <div class="dropdown-item small text-muted">Tidak ada produk baru.</div>
                                    </li>
                                    @endforelse
                                </ul>
                            </li>

                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="#" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block">{{ auth()->user()->name ?? 'Admin' }}</span>
                                                    <small class="text-muted">Admin</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <form action="{{ route('logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="bx bx-power-off me-2"></i>
                                                <span class="align-middle">Logout</span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                </div>
            </div>
            <!-- / Layout page -->
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    @stack('scripts')
</body>

</html>