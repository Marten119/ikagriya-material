<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Rekap Material Ikagriya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
@php
    // Menu hanya memuat halaman yang sudah dibuat. Menu lain ditambahkan pada langkah berikutnya.
    $menu = [
        ['Dashboard', 'dashboard', 'bi-speedometer2', 'dashboard', false],
        ['Input Material Masuk', 'receipts.index', 'bi-box-arrow-in-down', 'receipts.*', false],
        ['Data Material', 'materials.index', 'bi-box-seam', 'materials.*', false],
        ['Data Lokasi Cluster', 'clusters.index', 'bi-geo-alt', 'clusters.*', false],
        ['Rekapitulasi', 'recap.index', 'bi-table', 'recap.*', false],
        ['Laporan', 'reports.index', 'bi-file-earmark-text', 'reports.*', false],
        ['Manajemen Pengguna', 'users.index', 'bi-people', 'users.*', true],
    ];
@endphp

<nav class="app-sidebar offcanvas-lg offcanvas-start d-flex flex-column" id="sidebar" tabindex="-1">
    <div class="brand">
        Rekap Material
        <small>PT. Ikagriya Darmapersada</small>
    </div>
    <ul class="nav flex-column py-2 flex-grow-1">
        @foreach ($menu as [$label, $route, $icon, $pattern, $adminOnly])
            @if (! $adminOnly || auth()->user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}">
                        <i class="bi {{ $icon }}"></i> {{ $label }}
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
    <form method="POST" action="{{ route('logout') }}" class="border-top">
        @csrf
        <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start">
            <i class="bi bi-box-arrow-right"></i> Logout
        </button>
    </form>
</nav>

<div class="app-main">
    <header class="app-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>
            <h1>@yield('title', 'Dashboard')</h1>
        </div>
        <div class="text-muted small text-end">
            {{ auth()->user()->name }}
            <span class="badge text-bg-light border">{{ auth()->user()->role }}</span>
        </div>
    </header>

    <main class="app-content">
        @include('partials.flash')
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
