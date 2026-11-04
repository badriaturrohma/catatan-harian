<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Monitoring Perjalanan Santri') - {{ config('app.name') }}</title>
    
    <!-- Google Font Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0F766E;
            --primary-dark: #0D655E;
            --primary-light: #CCFBF1;
            --primary-border: #99F6E4;
            --bg-body: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-main: #0F172A;
            --text-muted: #475569;
            --border-soft: #E2E8F0;
            --danger: #DC2626;
            --danger-bg: #FEE2E2;
            --warning: #D97706;
            --warning-bg: #FEF3C7;
            --success: #16A34A;
            --success-bg: #DCFCE7;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Sidebar Desktop */
        .sidebar {
            width: 250px;
            background-color: var(--card-bg);
            border-right: 1px solid var(--border-soft);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 40;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-soft);
        }

        .sidebar-brand h1 {
            font-size: 17px;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.3px;
        }

        .sidebar-brand span {
            font-size: 11px;
            background-color: var(--primary-light);
            color: var(--primary);
            padding: 2px 8px;
            border-radius: 9999px;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 16px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            overflow-y: auto;
        }

        .nav-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94A3B8;
            font-weight: 700;
            padding: 12px 12px 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .nav-link:hover {
            background-color: #F1F5F9;
            color: var(--primary);
        }

        .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border-soft);
            background-color: #FAFAFA;
        }

        /* Main Content */
        .main-wrapper {
            flex: 1;
            margin-left: 250px;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* Topbar */
        .topbar {
            height: 64px;
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-main);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .badge-role {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: capitalize;
        }

        .badge-pengurus { background: #E0E7FF; color: #4338CA; }
        .badge-petugas { background: #FEF3C7; color: #B45309; }
        .badge-wali { background: #DCFCE7; color: #15803D; }

        .content-body {
            padding: 28px;
            flex: 1;
            max-width: 1440px;
            width: 100%;
        }

        /* Responsive Mobile Drawer */
        .mobile-header {
            display: none;
            height: 60px;
            background: var(--card-bg);
            border-bottom: 1px solid var(--border-soft);
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-main);
            cursor: pointer;
            padding: 8px;
        }

        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
            }
            .mobile-header {
                display: flex;
            }
            .content-body {
                padding: 16px;
            }
            .sidebar-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.4);
                z-index: 35;
            }
            .sidebar-backdrop.active {
                display: block;
            }
        }

        /* Card System */
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-soft);
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #FFFFFF;
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
        }

        .card-body {
            padding: 20px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            min-height: 42px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #FFFFFF;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-soft);
            color: var(--text-main);
        }

        .btn-outline:hover {
            background-color: #F8FAFC;
        }

        .btn-danger {
            background-color: var(--danger);
            color: #FFFFFF;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
            min-height: 32px;
        }

        /* Alerts & Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-kuning { background: var(--warning-bg); color: var(--warning); border: 1px solid #FDE68A; }
        .badge-merah { background: var(--danger-bg); color: var(--danger); border: 1px solid #FECACA; }
        .badge-hijau { background: var(--success-bg); color: var(--success); border: 1px solid #BBF7D0; }
        .badge-teal { background: var(--primary-light); color: var(--primary); border: 1px solid var(--primary-border); }

        /* Tables */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table.custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        table.custom-table th {
            background-color: #F8FAFC;
            color: #64748B;
            font-weight: 600;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-soft);
            white-space: nowrap;
        }

        table.custom-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-soft);
            color: var(--text-main);
            vertical-align: middle;
        }

        table.custom-table tr:hover td {
            background-color: #F8FAFC;
        }

        /* Form elements */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-soft);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text-main);
            background-color: #FFFFFF;
            outline: none;
            transition: border-color 0.15s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15);
        }

        /* Flash notifications */
        .flash-alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .flash-success { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
        .flash-danger { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Mobile Top Header -->
    <div class="mobile-header">
        <div style="display:flex; align-items:center; gap:10px;">
            <button class="menu-toggle" id="menuToggle">☰</button>
            <strong style="color:var(--primary); font-size:16px;">Monitoring Santri</strong>
        </div>
        <span class="badge-role badge-{{ auth()->user()->role ?? 'guest' }}">
            {{ auth()->user()->role ?? 'Guest' }}
        </span>
    </div>

    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="appSidebar">
            <div class="sidebar-brand">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary)">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <polyline points="16 11 18 13 22 9"></polyline>
                </svg>
                <div>
                    <h1>SantriTrack</h1>
                    <span>v1.1</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                @if(auth()->check())
                    @if(auth()->user()->isPengurus())
                        <div class="nav-label">Monitoring Utama</div>
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <span>📊</span> Dashboard Pengurus
                        </a>
                        <a href="{{ route('admin.santri.index') }}" class="nav-link {{ request()->routeIs('admin.santri.*') ? 'active' : '' }}">
                            <span>🎓</span> Data Santri & QR
                        </a>
                        <a href="{{ route('admin.permits.index') }}" class="nav-link {{ request()->routeIs('admin.permits.*') ? 'active' : '' }}">
                            <span>📝</span> Perizinan Keluar
                        </a>

                        <div class="nav-label">Laporan & Evaluasi</div>
                        <a href="{{ route('admin.reports.daily') }}" class="nav-link {{ request()->routeIs('admin.reports.daily') ? 'active' : '' }}">
                            <span>📅</span> Rekap Harian
                        </a>
                        <a href="{{ route('admin.reports.monthly') }}" class="nav-link {{ request()->routeIs('admin.reports.monthly') ? 'active' : '' }}">
                            <span>📈</span> Rekap Bulanan
                        </a>
                        <a href="{{ route('admin.corrections.index') }}" class="nav-link {{ request()->routeIs('admin.corrections.*') ? 'active' : '' }}">
                            <span>⚖️</span> Review Koreksi
                        </a>
                        <a href="{{ route('admin.audit.index') }}" class="nav-link {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                            <span>📜</span> Jejak Audit
                        </a>

                        <div class="nav-label">Master & Aturan</div>
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <span>👥</span> Akun Pengguna
                        </a>
                        <a href="{{ route('officer.scan') }}" class="nav-link {{ request()->routeIs('officer.scan') ? 'active' : '' }}">
                            <span>📷</span> Mode Scanner Pos
                        </a>
                        <a href="{{ route('admin.master.index') }}" class="nav-link {{ request()->routeIs('admin.master.*') ? 'active' : '' }}">
                            <span>🏢</span> Kampus & Pos
                        </a>
                        <a href="{{ route('admin.assignments.index') }}" class="nav-link {{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}">
                            <span>🛡️</span> Penugasan Petugas
                        </a>
                        <a href="{{ route('admin.holidays.index') }}" class="nav-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">
                            <span>🏖️</span> Kalender Libur
                        </a>
                        <a href="{{ route('admin.rules.index') }}" class="nav-link {{ request()->routeIs('admin.rules.*') ? 'active' : '' }}">
                            <span>⚙️</span> Parameter Aturan
                        </a>
                    @elseif(auth()->user()->isPetugas())
                        <div class="nav-label">Petugas Lapangan</div>
                        <a href="{{ route('officer.scan') }}" class="nav-link {{ request()->routeIs('officer.scan') ? 'active' : '' }}">
                            <span>📷</span> Scan QR Tapping
                        </a>
                    @elseif(auth()->user()->isWali())
                        <div class="nav-label">Portal Wali Santri</div>
                        <a href="{{ route('parent.dashboard') }}" class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
                            <span>🏠</span> Pemantauan Anak
                        </a>
                    @endif
                @endif
            </nav>

            <div class="sidebar-footer">
                @if(auth()->check())
                    <div style="font-size:13px; font-weight:700; color:var(--text-main); margin-bottom:2px;">
                        {{ auth()->user()->name }}
                    </div>
                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:12px;">
                        {{ auth()->user()->email }}
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" style="width:100%;">
                            🚪 Keluar
                        </button>
                    </form>
                @endif
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <header class="topbar">
                <div class="topbar-title">@yield('page_title', 'Monitoring Perjalanan')</div>
                <div class="topbar-actions">
                    <span style="font-size:13px; color:var(--text-muted); display:flex; align-items:center; gap:6px;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background-color:#10B981;"></span>
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                    @if(auth()->check())
                        <span class="badge-role badge-{{ auth()->user()->role }}">
                            {{ auth()->user()->role }}
                        </span>
                    @endif
                </div>
            </header>

            <main class="content-body">
                @if(session('success'))
                    <div class="flash-alert flash-success">
                        <span>✓ {{ session('success') }}</span>
                        <span style="cursor:pointer;" onclick="this.parentElement.remove();">✕</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="flash-alert flash-danger">
                        <span>✕ {{ session('error') }}</span>
                        <span style="cursor:pointer;" onclick="this.parentElement.remove();">✕</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="flash-alert flash-danger">
                        <div>
                            <strong>Perhatian:</strong>
                            <ul style="margin-left: 20px; margin-top: 4px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <span style="cursor:pointer;" onclick="this.parentElement.remove();">✕</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const appSidebar = document.getElementById('appSidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        if (menuToggle && appSidebar && sidebarBackdrop) {
            menuToggle.addEventListener('click', () => {
                appSidebar.classList.toggle('open');
                sidebarBackdrop.classList.toggle('active');
            });
            sidebarBackdrop.addEventListener('click', () => {
                appSidebar.classList.remove('open');
                sidebarBackdrop.classList.remove('active');
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
