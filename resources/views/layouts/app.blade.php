<!DOCTYPE html>
<html lang="id" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — KeuanganKu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-width: 240px;
        }
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Sidebar */
        #sidebar {
            width: var(--sidebar-width);
            transition: transform 0.25s cubic-bezier(.4,0,.2,1), width 0.25s cubic-bezier(.4,0,.2,1);
        }
        #main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.25s cubic-bezier(.4,0,.2,1);
        }
        @media (max-width: 1023px) {
            #sidebar { transform: translateX(-100%); position: fixed; z-index: 50; }
            #sidebar.open { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }

        /* Nav item active */
        .nav-link.active {
            background: #f0fdf4;
            color: #15803d;
            font-weight: 600;
        }
        .nav-link.active svg { color: #16a34a; }
        .nav-link {
            transition: background 0.15s, color 0.15s;
        }
        .nav-link:hover:not(.active) {
            background: #f8fafc;
            color: #0f172a;
        }

        /* Smooth progress bar */
        .progress-bar { transition: width 0.6s cubic-bezier(.4,0,.2,1); }

        @media print {
            #sidebar, #topbar { display: none !important; }
            #main-content { margin-left: 0 !important; }
        }
    </style>
    @stack('head')
</head>
<body class="h-full bg-slate-50 text-slate-800">

    <!-- Sidebar Overlay (Mobile) -->
    <div id="sidebarOverlay" class="hidden fixed inset-0 bg-slate-900/50 z-40 lg:hidden" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 bg-white border-r border-slate-200 flex flex-col h-full">
        <!-- Brand -->
        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100">
            <div class="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center shadow-sm flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0">
                <div class="text-sm font-bold text-slate-900 tracking-tight">KeuanganKu</div>
                <div class="text-[11px] text-slate-400">Personal Finance</div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest px-3 pb-2">Menu Utama</p>

            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-600">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('transactions.index') }}"
               class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-600">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Transaksi</span>
                @if(isset($totalTransactionCount) && $totalTransactionCount > 0)
                <span class="ml-auto text-[10px] font-semibold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded-full">{{ $totalTransactionCount }}</span>
                @endif
            </a>

            <a href="{{ route('reports.index') }}"
               class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-600">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Laporan</span>
            </a>

            <a href="{{ route('budgets.index') }}"
               class="nav-link {{ request()->routeIs('budgets.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-600">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Anggaran</span>
            </a>

            <div class="pt-4">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest px-3 pb-2">Akun</p>
                <a href="{{ route('profile.edit') }}"
                   class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-600">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil</span>
                </a>
            </div>
        </nav>

        <!-- User Info + Logout at Bottom -->
        @auth
        <div class="border-t border-slate-100 p-3">
            <div class="flex items-center gap-3 px-2 py-2 rounded-lg">
                <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 ring-2 ring-emerald-400/30">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-semibold text-slate-900 truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </aside>

    <!-- Main Area -->
    <div id="main-content" class="min-h-screen flex flex-col">
        <!-- Top Bar -->
        <header id="topbar" class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200 no-print">
            <div class="flex items-center gap-3 px-4 sm:px-6 h-14">
                <!-- Mobile hamburger -->
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Page title breadcrumb -->
                <div class="flex-1 min-w-0">
                    <h1 class="text-sm font-semibold text-slate-900 truncate">@yield('page-title', 'Dashboard')</h1>
                    @hasSection('page-subtitle')
                    <p class="text-[11px] text-slate-400 truncate">@yield('page-subtitle')</p>
                    @endif
                </div>

                <!-- Right side actions -->
                <div class="flex items-center gap-2">
                    @auth
                    <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">Catat Transaksi</span>
                        <span class="sm:hidden">Catat</span>
                    </button>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
            @if(session('toast_success'))
            <div id="topAlertSuccess" class="mb-4 flex items-center gap-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('toast_success') }}
                <button onclick="this.parentElement.remove()" class="ml-auto text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
            @endif
            @if(session('toast_error') || $errors->any())
            <div class="mb-4 flex items-center gap-3 p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium">
                <svg class="w-4 h-4 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('toast_error') ?? $errors->first() }}
                <button onclick="this.parentElement.remove()" class="ml-auto text-rose-500 hover:text-rose-700">✕</button>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="no-print border-t border-slate-200 bg-white py-4 px-6 mt-auto">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400">
                <span>&copy; {{ date('Y') }} <strong class="text-slate-600">KeuanganKu</strong> — Pencatatan Keuangan Harian</span>
                <span class="hidden sm:inline">Format Rupiah (IDR) &bull; Aman &amp; Privat</span>
            </div>
        </footer>
    </div>

    <!-- Floating Mobile Add Button -->
    @auth
    <div class="lg:hidden no-print fixed bottom-6 right-6 z-40">
        <button type="button" onclick="openCreateModal()"
            class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-600 text-white shadow-xl shadow-emerald-600/30 active:scale-95 transition-all">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
        </button>
    </div>
    @endauth

    @stack('modals')

    <script>
        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.add('hidden');
        }

        // Keyboard shortcut N = add transaction
        document.addEventListener('keydown', function(e) {
            if (e.key.toLowerCase() === 'n' && !['input','textarea','select'].includes(document.activeElement.tagName.toLowerCase())) {
                e.preventDefault();
                if (typeof openCreateModal === 'function') openCreateModal();
            }
        });

        // Auto-dismiss alerts
        setTimeout(() => {
            const alert = document.getElementById('topAlertSuccess');
            if (alert) { alert.style.opacity = '0'; setTimeout(() => alert.remove(), 300); }
        }, 5000);
    </script>
    @stack('scripts')
</body>
</html>
