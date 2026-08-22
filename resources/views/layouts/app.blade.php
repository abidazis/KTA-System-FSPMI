<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem KTA Federasi')</title>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary: #1e40af;
            --primary-hover: #1e3a8a;
            --secondary: #64748b;
            --success: #059669;
            --danger: #dc2626;
            --warning: #d97706;
            --info: #0284c7;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f1f5f9; color: #1e293b; }
        .sidebar { position: fixed; width: 260px; height: 100vh; background: var(--primary); color: #fff; padding: 1.5rem 0; overflow-y: auto; }
        .sidebar .logo { padding: 0 1.5rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 1rem; }
        .sidebar .logo h1 { font-size: 1.25rem; font-weight: 600; }
        .sidebar .logo span { font-size: 0.75rem; opacity: 0.7; }
        .sidebar nav a { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: rgba(255,255,255,0.85); text-decoration: none; transition: 0.2s; font-size: 0.9rem; }
        .sidebar nav a:hover, .sidebar nav a.active { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar nav a svg { width: 20px; height: 20px; }
        .sidebar .section-title { padding: 1rem 1.5rem 0.5rem; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.5; }
        .main { margin-left: 260px; min-height: 100vh; }
        .topbar { background: #fff; padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
        .topbar h2 { font-size: 1.25rem; font-weight: 600; }
        .topbar .user-menu { display: flex; align-items: center; gap: 1rem; }
        .topbar .user-menu .dropdown { position: relative; }
        .topbar .user-menu .dropdown-content { display: none; position: absolute; right: 0; top: 100%; background: #fff; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); min-width: 200px; z-index: 100; }
        .topbar .user-menu .dropdown:hover .dropdown-content { display: block; }
        .topbar .user-menu .dropdown-content a { display: block; padding: 0.75rem 1rem; color: #475569; text-decoration: none; transition: 0.2s; }
        .topbar .user-menu .dropdown-content a:hover { background: #f1f5f9; }
        .content { padding: 1.5rem; }
        .card { background: #fff; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem; margin-bottom: 1.5rem; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; }
        .card-header h3 { font-size: 1rem; font-weight: 600; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: 0.2s; text-decoration: none; border: none; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-success { background: var(--success); color: #fff; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-secondary { background: var(--secondary); color: #fff; }
        .btn-outline { background: transparent; border: 1px solid #cbd5e1; color: #475569; }
        .btn-outline:hover { background: #f1f5f9; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.375rem; color: #374151; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; transition: 0.2s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30,64,175,0.1); }
        .form-group .error { color: var(--danger); font-size: 0.75rem; margin-top: 0.25rem; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        table th { text-align: left; padding: 0.75rem; background: #f8fafc; font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #e2e8f0; }
        table td { padding: 0.75rem; border-bottom: 1px solid #f1f5f9; font-size: 0.875rem; vertical-align: middle; }
        table tr:hover { background: #f8fafc; }
        .badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .badge-draft { background: #fef3c7; color: #92400e; }
        .badge-ready { background: #dbeafe; color: #1e40af; }
        .badge-generated { background: #d1fae5; color: #065f46; }
        .badge-printed { background: #e0e7ff; color: #3730a3; }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-expired { background: #fee2e2; color: #991b1b; }
        .badge-inactive { background: #f1f5f9; color: #475569; }
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .pagination { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .pagination a, .pagination span { padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.375rem; font-size: 0.875rem; color: #475569; text-decoration: none; }
        .pagination .active { background: var(--primary); color: #fff; border-color: var(--primary); }
        .filters { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border-radius: 0.5rem; }
        .filters .form-group { margin-bottom: 0; flex: 1; min-width: 150px; }
        .filters .form-group input, .filters .form-group select { padding: 0.4rem 0.6rem; font-size: 0.8rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .stat-card { background: #fff; border-radius: 0.75rem; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-card .stat-icon { width: 40px; height: 40px; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: #1e293b; }
        .stat-card .stat-label { font-size: 0.8rem; color: #64748b; }
        .stat-card.primary .stat-icon { background: #dbeafe; color: var(--primary); }
        .stat-card.success .stat-icon { background: #d1fae5; color: var(--success); }
        .stat-card.danger .stat-icon { background: #fee2e2; color: var(--danger); }
        .stat-card.warning .stat-icon { background: #fef3c7; color: var(--warning); }
        .empty-state { text-align: center; padding: 3rem; color: #64748b; }
        .empty-state svg { width: 64px; height: 64px; margin-bottom: 1rem; opacity: 0.5; }
        .actions { display: flex; gap: 0.5rem; }
        .checkbox-wrapper { display: flex; align-items: center; }
        .checkbox-wrapper input[type="checkbox"] { width: 1rem; height: 1rem; }
        @media (max-width: 768px) { .sidebar { width: 80px; } .sidebar .logo h1, .sidebar .logo span, .sidebar nav a span { display: none; } .main { margin-left: 80px; } }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <h1>KTA FSPMI</h1>
            <span>Sistem Manajemen KTA</span>
        </div>
        <nav>
            <div class="section-title">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>
            @can('anggota-view')
            <a href="{{ route('members.index') }}" class="{{ request()->routeIs('members.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Data Anggota</span>
            </a>
            @endcan
            @can('import')
            <a href="{{ route('imports.index') }}" class="{{ request()->routeIs('imports.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                <span>Import Data</span>
            </a>
            @endcan
            @can('cetak')
            <a href="{{ route('print.index') }}" class="{{ request()->routeIs('print.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak KTA</span>
            </a>
            @endcan
            @can('pengurus')
            <div class="section-title">Pengaturan</div>
            <a href="{{ route('management.index') }}" class="{{ request()->routeIs('management.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span>Data Pengurus</span>
            </a>
            @endcan
            @can('wilayah')
            <a href="{{ route('regions.index') }}" class="{{ request()->routeIs('regions.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Data Wilayah</span>
            </a>
            @endcan
            @can('user-view')
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Manajemen User</span>
            </a>
            @endcan
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <h2>@yield('header', 'Dashboard')</h2>
            <div class="user-menu">
                <div class="dropdown">
                    <a href="#" style="display:flex;align-items:center;gap:0.5rem;color:#475569;text-decoration:none;">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>{{ auth()->user()->name }}</span>
                    </a>
                    <div class="dropdown-content">
                        <a href="{{ route('password.edit') }}">Ubah Password</a>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan:</strong>
                    <ul style="margin:0.5rem 0 0 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
