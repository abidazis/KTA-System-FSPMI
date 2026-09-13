<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem KTA Federasi')</title>
    <link href="{{ asset('build/assets/app-C0sIAfS7.css') }}?v=20240908" rel="stylesheet">
    <style>
        /* ============================================
           CLEAN & PROFESSIONAL DESIGN SYSTEM
           FSPMI - Federasi Serikat Pekerja Metal Indonesia
           Designed for Bapak-bapak: Large fonts, clear contrast
        ============================================ */

        :root {
            /* Primary - Deep Navy Blue (Official Federation) */
            --primary: #1e3a5f;
            --primary-light: #2d4a6f;
            --primary-dark: #0f2440;

            /* Semantic Colors */
            --success: #047857;
            --success-light: #ecfdf5;
            --danger: #b91c1c;
            --danger-light: #fef2f2;
            --warning: #b45309;
            --warning-light: #fffbeb;
            --info: #0369a1;
            --info-light: #f0f9ff;
            --gold: #b8860b;

            /* Neutrals */
            --white: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--gray-100);
            color: var(--gray-800);
            font-size: 16px; /* Larger base font for Bapak-bapak */
            line-height: 1.6;
        }

        /* ===== SIDEBAR - Clean & Professional ===== */
        .sidebar {
            position: fixed;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 0;
            overflow-y: auto;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }

        .sidebar .logo {
            padding: 1.5rem 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 0.5rem;
            background: rgba(0,0,0,0.1);
        }
        .sidebar .logo h1 {
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar .logo .logo-icon {
            width: 24px;
            height: 24px;
            background: var(--gold);
            border-radius: 4px;
            display: inline-block;
        }
        .sidebar .logo span {
            font-size: 0.8rem;
            opacity: 0.75;
            display: block;
            margin-top: 0.25rem;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.875rem 1.5rem;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
        }
        .sidebar nav a:hover,
        .sidebar nav a.active {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            border-left-color: var(--gold);
        }
        .sidebar nav a svg { width: 22px; height: 22px; flex-shrink: 0; }

        .sidebar .section-title {
            padding: 1.25rem 1.5rem 0.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            opacity: 0.5;
            font-weight: 600;
        }

        /* ===== MAIN CONTENT ===== */
        .main { margin-left: 280px; min-height: 100vh; }

        /* ===== TOPBAR - Clean Header ===== */
        .topbar {
            background: var(--white);
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .topbar h2 {
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--gray-800);
        }
        .topbar .user-menu { display: flex; align-items: center; gap: 1rem; }
        .topbar .user-menu .dropdown { position: relative; }
        .topbar .user-menu .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: var(--white);
            border-radius: 0.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            min-width: 220px;
            z-index: 100;
            border: 1px solid var(--gray-200);
        }
        .topbar .user-menu .dropdown:hover .dropdown-content { display: block; }
        .topbar .user-menu .dropdown-content a {
            display: block;
            padding: 0.875rem 1.25rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: 0.2s;
            font-size: 0.95rem;
        }
        .topbar .user-menu .dropdown-content a:hover { background: var(--gray-50); }

        /* ===== CONTENT AREA ===== */
        .content { padding: 2rem; max-width: 1600px; }

        /* ===== CARDS - Clean Design ===== */
        .card {
            background: var(--white);
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--gray-100);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-100);
        }
        .card-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gray-800);
        }

        /* ===== BUTTONS - Larger for Bapak-bapak ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
            line-height: 1.4;
        }
        .btn-primary { background: var(--primary); color: var(--white); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-success { background: var(--success); color: var(--white); }
        .btn-success:hover { background: #065f46; }
        .btn-danger { background: var(--danger); color: var(--white); }
        .btn-danger:hover { background: #991b1b; }
        .btn-secondary { background: var(--gray-500); color: var(--white); }
        .btn-outline {
            background: transparent;
            border: 2px solid var(--gray-300);
            color: var(--gray-700);
        }
        .btn-outline:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
        }
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        .btn-lg {
            padding: 0.875rem 1.75rem;
            font-size: 1rem;
        }

        /* ===== FORMS - Clean & Accessible ===== */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: 0.2s;
            background: var(--white);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30,58,95,0.1);
        }
        .form-group .error {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.375rem;
            font-weight: 500;
        }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; }

        /* ===== TABLES - Clean & Readable ===== */
        table { width: 100%; border-collapse: collapse; }
        table th {
            text-align: left;
            padding: 1rem;
            background: var(--gray-50);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid var(--gray-200);
        }
        table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-100);
            font-size: 0.95rem;
            vertical-align: middle;
        }
        table tr:hover { background: var(--gray-50); }
        table tr:last-child td { border-bottom: none; }

        /* ===== BADGES - Clear Status ===== */
        .badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        .badge-draft { background: var(--gray-100); color: var(--gray-600); }
        .badge-ready { background: var(--info-light); color: var(--info); }
        .badge-generated { background: var(--success-light); color: var(--success); }
        .badge-printed { background: #ede9fe; color: #6d28d9; }
        .badge-active { background: var(--success-light); color: var(--success); }
        .badge-expired { background: var(--danger-light); color: var(--danger); }
        .badge-inactive { background: var(--gray-100); color: var(--gray-500); }

        /* ===== ALERTS - Clean & Visible ===== */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 1rem;
            border-left: 4px solid;
        }
        .alert-success {
            background: var(--success-light);
            color: var(--success);
            border-left-color: var(--success);
        }
        .alert-danger {
            background: var(--danger-light);
            color: var(--danger);
            border-left-color: var(--danger);
        }
        .alert-warning {
            background: var(--warning-light);
            color: var(--warning);
            border-left-color: var(--warning);
        }
        .alert-info {
            background: var(--info-light);
            color: var(--info);
            border-left-color: var(--info);
        }

        /* ===== PAGINATION ===== */
        .pagination { display: flex; gap: 0.5rem; margin-top: 1.5rem; }
        .pagination a, .pagination span {
            padding: 0.625rem 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.375rem;
            font-size: 0.95rem;
            color: var(--gray-700);
            text-decoration: none;
            background: var(--white);
        }
        .pagination a:hover { background: var(--gray-50); }
        .pagination .active {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }
        .pagination .disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* ===== FILTERS ===== */
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            background: var(--gray-50);
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200);
        }
        .filters .form-group { margin-bottom: 0; flex: 1; min-width: 180px; }
        .filters .form-group input,
        .filters .form-group select {
            padding: 0.625rem 0.875rem;
            font-size: 0.95rem;
        }

        /* ===== STATS GRID ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--white);
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid var(--gray-100);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-800);
            line-height: 1.2;
        }
        .stat-card .stat-label {
            font-size: 0.9rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }
        .stat-card.primary .stat-icon { background: #dbeafe; color: var(--primary); }
        .stat-card.success .stat-icon { background: var(--success-light); color: var(--success); }
        .stat-card.danger .stat-icon { background: var(--danger-light); color: var(--danger); }
        .stat-card.warning .stat-icon { background: var(--warning-light); color: var(--warning); }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray-500);
        }
        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        /* ===== ACTIONS ===== */
        .actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

        /* ===== CHECKBOX ===== */
        .checkbox-wrapper { display: flex; align-items: center; gap: 0.5rem; }
        .checkbox-wrapper input[type="checkbox"] {
            width: 1.125rem;
            height: 1.125rem;
            cursor: pointer;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .sidebar { width: 80px; }
            .sidebar .logo h1, .sidebar .logo span, .sidebar nav a span { display: none; }
            .sidebar nav a { justify-content: center; padding: 1rem; }
            .sidebar .section-title { display: none; }
            .main { margin-left: 80px; }
        }
        @media (max-width: 768px) {
            .content { padding: 1rem; }
            .filters { flex-direction: column; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }

        /* ===== CUSTOM MODAL SYSTEM - FSPMI Design ===== */
        .fspmi-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 36, 64, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            padding: 1rem;
        }
        .fspmi-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .fspmi-modal {
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            width: 100%;
            max-width: 420px;
            transform: scale(0.9) translateY(20px);
            transition: transform 0.2s ease;
            overflow: hidden;
        }
        .fspmi-modal-overlay.active .fspmi-modal {
            transform: scale(1) translateY(0);
        }
        .fspmi-modal-header {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
        }
        .fspmi-modal-icon {
            width: 44px;
            height: 44px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .fspmi-modal-icon svg { width: 22px; height: 22px; }
        .fspmi-modal-icon.success { background: var(--success-light); color: var(--success); }
        .fspmi-modal-icon.danger { background: var(--danger-light); color: var(--danger); }
        .fspmi-modal-icon.warning { background: var(--warning-light); color: var(--warning); }
        .fspmi-modal-icon.info { background: var(--info-light); color: var(--info); }
        .fspmi-modal-icon.question { background: var(--gray-100); color: var(--gray-600); }
        .fspmi-modal-header-text { flex: 1; }
        .fspmi-modal-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--gray-800);
            line-height: 1.3;
        }
        .fspmi-modal-subtitle {
            font-size: 0.85rem;
            color: var(--gray-500);
            margin-top: 0.125rem;
        }
        .fspmi-modal-body {
            padding: 1.25rem 1.5rem;
        }
        .fspmi-modal-body p {
            font-size: 0.95rem;
            color: var(--gray-600);
            line-height: 1.6;
            margin: 0;
        }
        .fspmi-modal-footer {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding: 1rem 1.5rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-100);
        }
        .fspmi-modal-footer .btn {
            padding: 0.625rem 1.25rem;
            font-size: 0.9rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .fspmi-modal-footer .btn-cancel {
            background: var(--white);
            color: var(--gray-600);
            border: 1.5px solid var(--gray-300);
        }
        .fspmi-modal-footer .btn-cancel:hover {
            background: var(--gray-100);
            border-color: var(--gray-400);
        }
        .fspmi-modal-footer .btn-confirm {
            background: var(--primary);
            color: var(--white);
            box-shadow: 0 2px 8px rgba(30, 58, 95, 0.3);
        }
        .fspmi-modal-footer .btn-confirm:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 58, 95, 0.4);
        }
        .fspmi-modal-footer .btn-confirm.danger {
            background: var(--danger);
            box-shadow: 0 2px 8px rgba(185, 28, 28, 0.3);
        }
        .fspmi-modal-footer .btn-confirm.danger:hover {
            background: #991b1b;
            box-shadow: 0 4px 12px rgba(185, 28, 28, 0.4);
        }
        .fspmi-modal-footer .btn-confirm.success {
            background: var(--success);
            box-shadow: 0 2px 8px rgba(4, 120, 87, 0.3);
        }
        .fspmi-modal-footer .btn-confirm.success:hover {
            background: #065f46;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Custom Modal System - FSPMI Design -->
    <div id="fspmi-modal-overlay" class="fspmi-modal-overlay">
        <div class="fspmi-modal">
            <div class="fspmi-modal-header">
                <div id="fspmi-modal-icon" class="fspmi-modal-icon info"></div>
                <div class="fspmi-modal-header-text">
                    <div id="fspmi-modal-title" class="fspmi-modal-title"></div>
                    <div id="fspmi-modal-subtitle" class="fspmi-modal-subtitle"></div>
                </div>
            </div>
            <div id="fspmi-modal-body" class="fspmi-modal-body">
                <p id="fspmi-modal-message"></p>
            </div>
            <div id="fspmi-modal-footer" class="fspmi-modal-footer"></div>
        </div>
    </div>
    <aside class="sidebar">
        <div class="logo">
            <h1><span class="logo-icon"></span> KTA FSPMI</h1>
            <span>Federasi Serikat Pekerja Metal Indonesia</span>
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
            <div class="section-title">Cetak KTA</div>
            <a href="{{ route('print.index') }}" class="{{ request()->routeIs('print.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak KTA</span>
            </a>
            <a href="{{ route('settings.kta-background.index') }}" class="{{ request()->routeIs('settings.kta-background.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Background KTA</span>
            </a>
            <a href="{{ route('settings.companies.index') }}" class="{{ request()->routeIs('settings.companies.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span>Master Perusahaan</span>
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
            @can('formulasi-nomor-anggota')
            <div class="section-title">Nomor Anggota</div>
            <a href="{{ route('member-numbers.index') }}" class="{{ request()->routeIs('member-numbers.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                <span>Kelola Nomor Anggota</span>
            </a>
            <a href="{{ route('member-number-formulas.index') }}" class="{{ request()->routeIs('member-number-formulas.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Formulasi Nomor</span>
            </a>
            @endcan
            @can('user-view')
            <div class="section-title">Manajemen</div>
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
                    <a href="#" style="display:flex;align-items:center;gap:0.75rem;color:var(--gray-700);text-decoration:none;font-weight:500;">
                        <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="background:var(--gray-100);border-radius:50%;padding:0.375rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>{{ auth()->user()->name }}</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </a>
                    <div class="dropdown-content">
                        <a href="{{ route('password.edit') }}">Ubah Password</a>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;padding:0.875rem 1.25rem;color:var(--gray-700);font-size:0.95rem;">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:0.5rem;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:0.5rem;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <strong>Gagal!</strong> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:0.5rem;"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <strong>Terjadi kesalahan:</strong>
                    <ul style="margin:0.75rem 0 0 1.5rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Custom Modal System - FSPMI JavaScript API -->
    <script>
    (function() {
        var overlay = document.getElementById('fspmi-modal-overlay');
        var iconEl = document.getElementById('fspmi-modal-icon');
        var titleEl = document.getElementById('fspmi-modal-title');
        var subtitleEl = document.getElementById('fspmi-modal-subtitle');
        var messageEl = document.getElementById('fspmi-modal-message');
        var footerEl = document.getElementById('fspmi-modal-footer');
        var resolveCallback = null;

        // Icon SVGs per type
        var icons = {
            success: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            danger: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            warning: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            info: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            question: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        };

        var titles = {
            success: 'Berhasil!',
            danger: 'Terjadi Kesalahan',
            warning: 'Peringatan',
            info: 'Informasi',
            question: 'Konfirmasi'
        };

        var subtitles = {
            success: 'Operasi berhasil dilakukan',
            danger: 'Mohon periksa kesalahan berikut',
            warning: 'Harap perhatian Anda',
            info: 'Informasi sistem',
            question: 'Tindakan memerlukan konfirmasi'
        };

        function openModal(type, title, subtitle, message, buttons) {
            return new Promise(function(resolve) {
                resolveCallback = resolve;

                // Set icon & colors
                iconEl.className = 'fspmi-modal-icon ' + type;
                iconEl.innerHTML = icons[type] || icons.info;

                // Set text
                titleEl.textContent = title || titles[type] || 'Informasi';
                subtitleEl.textContent = subtitle || subtitles[type] || '';
                messageEl.textContent = message || '';

                // Build footer buttons
                footerEl.innerHTML = '';
                buttons.forEach(function(btn) {
                    var el = document.createElement('button');
                    el.className = 'btn ' + btn.class;
                    el.textContent = btn.text;
                    el.style.cssText = btn.style || '';
                    el.addEventListener('click', function() {
                        closeModal();
                        if (resolveCallback) resolveCallback(btn.value);
                        resolveCallback = null;
                    });
                    footerEl.appendChild(el);
                });

                // Show
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';

                // Focus first button
                setTimeout(function() {
                    var firstBtn = footerEl.querySelector('button');
                    if (firstBtn) firstBtn.focus();
                }, 100);
            });
        }

        function closeModal() {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close on backdrop click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeModal();
                if (resolveCallback) { resolveCallback(false); resolveCallback = null; }
            }
        });

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) {
                closeModal();
                if (resolveCallback) { resolveCallback(false); resolveCallback = null; }
            }
        });

        // ===== PUBLIC API =====

        // Modal.alert(message, type, title) - Single OK button
        window.FSPMIModal = {
            alert: function(message, type, title, subtitle) {
                type = type || 'info';
                return openModal(type, title, subtitle, message, [
                    { text: 'OK', class: 'btn-confirm' + (type === 'danger' ? ' danger' : type === 'success' ? ' success' : '') }
                ]);
            },

            // Modal.success(message, title)
            success: function(message, title) {
                return openModal('success', title || 'Berhasil!', subtitles.success, message, [
                    { text: 'OK', class: 'btn-confirm success' }
                ]);
            },

            // Modal.error(message, title)
            error: function(message, title) {
                return openModal('danger', title || 'Terjadi Kesalahan', subtitles.danger, message, [
                    { text: 'OK', class: 'btn-confirm danger' }
                ]);
            },

            // Modal.warning(message, title)
            warning: function(message, title) {
                return openModal('warning', title || 'Peringatan', subtitles.warning, message, [
                    { text: 'OK', class: 'btn-confirm' }
                ]);
            },

            // Modal.info(message, title)
            info: function(message, title) {
                return openModal('info', title || 'Informasi', subtitles.info, message, [
                    { text: 'OK', class: 'btn-confirm' }
                ]);
            },

            // Modal.confirm(message, title) - Returns Promise<boolean>
            confirm: function(message, title, confirmText, cancelText, type) {
                type = type || 'question';
                title = title || 'Konfirmasi';
                confirmText = confirmText || 'Ya, Lanjutkan';
                cancelText = cancelText || 'Batal';
                return openModal(type, title, subtitles.question, message, [
                    { text: cancelText, class: 'btn-cancel', value: false },
                    { text: confirmText, class: 'btn-confirm' + (type === 'danger' ? ' danger' : ''), value: true }
                ]);
            },

            // Modal.confirmDelete(message, title) - Danger confirm for delete
            confirmDelete: function(message, title) {
                return this.confirm(message, title || 'Hapus Data', 'Ya, Hapus', 'Batal', 'danger');
            },

            // Modal.confirmStatus(message, title) - Confirm status change
            confirmStatus: function(message, title) {
                return this.confirm(message, title || 'Ubah Status', 'Ya, Ubah', 'Batal', 'warning');
            },

            // Modal.confirmBulk(count, action) - Bulk action confirm
            confirmBulk: function(count, action, type) {
                var messages = {
                    delete: count + ' anggota akan dihapus. Data yang dihapus tidak dapat dikembalikan.',
                    update_status: 'Status ' + count + ' anggota akan diubah.',
                    edit: count + ' anggota akan diedit massal.'
                };
                var titles = {
                    delete: 'Hapus ' + count + ' Anggota?',
                    update_status: 'Ubah Status ' + count + ' Anggota?',
                    edit: 'Edit Massal ' + count + ' Anggota?'
                };
                var confirmTexts = {
                    delete: 'Ya, Hapus',
                    update_status: 'Ya, Ubah',
                    edit: 'Ya, Edit'
                };
                var msg = messages[action] || count + ' item akan diproses.';
                var ttl = titles[action] || 'Konfirmasi Massal';
                var conf = confirmTexts[action] || 'Ya, Lanjutkan';
                return this.confirm(msg, ttl, conf, 'Batal', type || 'danger');
            }
        };

        // Backward compatible - override native confirm
        window.confirm = function(msg) {
            FSPMIModal.confirm(msg).then(function(ok) {
                // Can't really override native confirm sync behavior
                // So we rely on FSPMIModal.confirm() instead
            });
            return false; // Prevent default native confirm
        };

        // Override alert with FSPMI modal (non-blocking)
        window.alert = function(msg) {
            FSPMIModal.alert(msg, 'info', 'Informasi');
        };

        // ===== GLOBAL FORM CONFIRM HANDLER =====
        // Forms with data-fspmi-confirm attribute will use FSPMI modal instead of native confirm
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form) return;
            var msg = form.dataset.fspmiConfirm || form.dataset.confirmMsg;
            var type = form.dataset.fspmiConfirmType || 'question';
            var confirmBtn = form.dataset.fspmiConfirmBtn || 'Ya, Lanjutkan';
            var cancelBtn = form.dataset.fspmiConfirmCancel || 'Batal';

            if (!msg) return;

            e.preventDefault();
            e.stopImmediatePropagation();

            FSPMIModal.confirm(msg, form.dataset.fspmiConfirmTitle || 'Konfirmasi', confirmBtn, cancelBtn, type).then(function(ok) {
                if (ok) form.submit();
            });
        });

        // Also handle forms with data-confirm (backward compat + simple usage)
        document.querySelectorAll('form[data-confirm]').forEach(function(form) {
            var msg = form.dataset.confirm;
            if (!msg) return;
            // Remove inline onsubmit to avoid double-confirm
            form.removeAttribute('onsubmit');
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>
