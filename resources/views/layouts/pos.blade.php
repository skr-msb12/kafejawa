<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Terminal Kasir') - KafeJawa POS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="pos-layout">
        <div class="pos-main">
            <header class="pos-topbar">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="sidebar-brand-icon" style="width: 26px; height: 26px; font-size: 13px;">KJ</div>
                    <span style="font-weight: 700; font-size: 15px; letter-spacing: 0.5px;">KafeJawa POS</span>
                    <span style="font-size: 12px; color: #94a3b8; margin-left: 6px;"><span class="pos-operator-text">Operator: </span><strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role }})</span>
                </div>
                <div class="pos-topbar-actions" style="display: flex; align-items: center; gap: 8px;">
                    <a href="{{ route('pos.history') }}" class="btn btn-secondary btn-sm" style="background: #1e293b; color: #f8fafc; border-color: #334155;" title="Riwayat Penjualan">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Riwayat Penjualan</span>
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm" style="background: #1e293b; color: #f8fafc; border-color: #334155;" title="Dashboard Admin">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            <span>Dashboard Admin</span>
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm" title="Keluar">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </header>

            <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
                @if (session('success'))
                    <div class="alert alert-success" style="margin: 12px 16px 0 16px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" style="margin: 12px 16px 0 16px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

        @yield('sidebar_cart')
    </div>
    @stack('scripts')
</body>
</html>
