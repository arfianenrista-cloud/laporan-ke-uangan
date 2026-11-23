<!DOCTYPE html>
<html lang="id" class="h-full antialiased dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#07090e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — KeuanganKu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-width: 250px;
        }
        body { 
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Sidebar transitions */
        #sidebar {
            width: var(--sidebar-width);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @media (max-width: 1023px) {
            #sidebar { 
                transform: translateX(-100%); 
                position: fixed; 
                z-index: 50; 
                width: 280px;
                box-shadow: 20px 0 50px rgba(0, 0, 0, 0.8);
            }
            #sidebar.open { transform: translateX(0); }
            #main-content { margin-left: 0; }
        }

        /* Nav item active */
        .nav-link.active {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.12);
            font-weight: 600;
        }
        .nav-link.active svg { color: #f4ba88; }
        .nav-link {
            transition: background 0.15s, color 0.15s, border-color 0.15s;
        }
        .nav-link:hover:not(.active) {
            background: rgba(255, 255, 255, 0.04);
            color: #f1f5f9;
        }

        /* Bottom Nav Item active */
        .bottom-nav-item.active {
            color: #f4ba88;
        }
        .bottom-nav-item.active svg {
            color: #f4ba88;
        }

        @media print {
            #sidebar, #topbar, #mobileBottomNav { display: none !important; }
            #main-content { margin-left: 0 !important; }
        }
    </style>
    @stack('head')
</head>
<body class="h-full bg-[#07090e] text-slate-300 antialiased selection:bg-[#f4ba88]/20 selection:text-[#f4ba88]">

    <!-- Sidebar Overlay (Mobile) -->
    <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black/80 backdrop-blur-md z-40 lg:hidden transition-opacity" onclick="closeSidebar()"></div>

    <!-- Sidebar (Lull Dark Ribbon Theme) -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 bg-[#090d14]/98 backdrop-blur-2xl border-r border-white/[0.08] flex flex-col h-full z-50">
        <!-- Brand & Mobile Close -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-white/[0.08]">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#1b2234] to-[#0f1422] border border-white/10 flex items-center justify-center shadow-lg shadow-black/40 flex-shrink-0">
                    <svg class="w-5 h-5 text-[#f4ba88]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-base font-bold text-white tracking-tight flex items-center gap-1.5">
                        KeuanganKu
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium tracking-wide">Personal Finance</div>
                </div>
            </a>
            <button onclick="closeSidebar()" class="lg:hidden p-1.5 rounded-xl text-slate-400 hover:text-white hover:bg-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-5 space-y-1 overflow-y-auto">
            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest px-3 pb-2.5">Menu Utama</p>

            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-slate-300 border border-transparent">
                <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('transactions.index') }}"
               class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-slate-300 border border-transparent">
                <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Transaksi</span>
                @if(isset($totalTransactionCount) && $totalTransactionCount > 0)
                <span class="ml-auto text-[10px] font-semibold bg-white/10 text-[#f4ba88] px-2 py-0.5 rounded-full border border-white/5">{{ $totalTransactionCount }}</span>
                @endif
            </a>

            <a href="{{ route('budgets.index') }}"
               class="nav-link {{ request()->routeIs('budgets.*') ? 'active' : '' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-slate-300 border border-transparent">
                <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Anggaran</span>
            </a>

            <a href="{{ route('reports.index') }}"
               class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-slate-300 border border-transparent">
                <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Laporan</span>
            </a>

            <div class="pt-6">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest px-3 pb-2.5">Akun</p>
                <a href="{{ route('profile.edit') }}"
                   class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }} flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-slate-300 border border-transparent">
                    <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil & Pengaturan</span>
                </a>
            </div>
        </nav>

        <!-- User Info + Logout at Bottom -->
        @auth
        <div class="border-t border-white/[0.08] p-3.5 bg-[#07090e]/60">
            <div class="flex items-center gap-3 px-2 py-1.5">
                <div class="w-8 h-8 rounded-full bg-[#1b2234] border border-white/10 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors">
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
    <div id="main-content" class="min-h-screen flex flex-col bg-transparent">
        <!-- Top Bar (Lull Night Bar) -->
        <header id="topbar" class="sticky top-0 z-30 bg-[#07090e]/85 backdrop-blur-xl border-b border-white/[0.08] no-print">
            <div class="flex items-center justify-between gap-3 px-4 sm:px-8 h-16">
                <!-- Mobile hamburger + Breadcrumb -->
                <div class="flex items-center gap-3 min-w-0">
                    <button onclick="toggleSidebar()" class="lg:hidden p-2 -ml-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 active:bg-white/10 transition-colors" aria-label="Buka Menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="min-w-0">
                        <h1 class="text-sm font-bold text-white tracking-tight truncate">@yield('page-title', 'Dashboard')</h1>
                        @hasSection('page-subtitle')
                        <p class="text-[11px] text-slate-400 truncate hidden xs:block">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>

                <!-- Right side actions: Signature Peach Pill Button -->
                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                    <button type="button" onclick="openCreateModal()" class="btn-lull-primary text-xs !py-2 !px-3 sm:!px-4">
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

        <!-- Page Content: with extra bottom padding on mobile for the bottom nav bar -->
        <main class="flex-1 px-3 sm:px-8 lg:px-10 py-5 sm:py-7 max-w-[1600px] w-full mx-auto pb-28 lg:pb-8">
            @if(session('toast_success'))
            <div id="topAlertSuccess" class="mb-5 flex items-center gap-3 p-3.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-2xl text-xs font-medium backdrop-blur">
                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('toast_success') }}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-emerald-400 hover:text-white">✕</button>
            </div>
            @endif
            @if(session('toast_error') || $errors->any())
            <div class="mb-5 flex items-center gap-3 p-3.5 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-2xl text-xs font-medium backdrop-blur">
                <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('toast_error') ?? $errors->first() }}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-rose-400 hover:text-white">✕</button>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- Desktop Footer -->
        <footer class="no-print hidden lg:block border-t border-white/[0.08] bg-[#07090e]/80 backdrop-blur py-5 px-8 mt-auto">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
                <div class="flex items-center gap-2">
                    <span class="text-white font-semibold">KeuanganKu</span>
                    <span>&bull;</span>
                    <span>Sistem Pencatatan Finansial Pribadi</span>
                </div>
                <div class="flex items-center gap-3 text-slate-400">
                    <span>Format Rupiah (IDR)</span>
                    <span>&bull;</span>
                    <span>Privat & Aman</span>
                </div>
            </div>
        </footer>
    </div>

    <!-- ─── MOBILE FLOATING BOTTOM DOCK NAVIGATION BAR (Fintech App Style) ─── -->
    @auth
    <nav id="mobileBottomNav" class="lg:hidden no-print fixed bottom-0 inset-x-0 z-40 bg-[#090d14]/90 backdrop-blur-2xl border-t border-white/[0.08] px-3 py-2 pb-[max(0.5rem,env(safe-area-inset-bottom))]">
        <div class="max-w-md mx-auto grid grid-cols-5 items-center justify-items-center text-center">
            
            <!-- 1. Dashboard -->
            <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : 'text-slate-400' }} flex flex-col items-center gap-1 py-1 px-2 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-[10px] font-semibold leading-none">Home</span>
            </a>

            <!-- 2. Transaksi -->
            <a href="{{ route('transactions.index') }}" class="bottom-nav-item {{ request()->routeIs('transactions.*') ? 'active' : 'text-slate-400' }} flex flex-col items-center gap-1 py-1 px-2 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span class="text-[10px] font-semibold leading-none">Transaksi</span>
            </a>

            <!-- 3. Prominent Center Quick-Add Button (Peach Glow) -->
            <button type="button" onclick="openCreateModal()" class="flex items-center justify-center -mt-5 w-12 h-12 rounded-full bg-[#f4ba88] text-[#1c130b] shadow-xl shadow-amber-950/60 active:scale-90 transition-transform border-2 border-[#090d14]" aria-label="Catat Transaksi">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
            </button>

            <!-- 4. Anggaran -->
            <a href="{{ route('budgets.index') }}" class="bottom-nav-item {{ request()->routeIs('budgets.*') ? 'active' : 'text-slate-400' }} flex flex-col items-center gap-1 py-1 px-2 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="text-[10px] font-semibold leading-none">Anggaran</span>
            </a>

            <!-- 5. Laporan -->
            <a href="{{ route('reports.index') }}" class="bottom-nav-item {{ request()->routeIs('reports.*') ? 'active' : 'text-slate-400' }} flex flex-col items-center gap-1 py-1 px-2 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-[10px] font-semibold leading-none">Laporan</span>
            </a>

        </div>
    </nav>
    @endauth

    @stack('modals')

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');
            if (sidebar.classList.contains('open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Shortcut 'N'
        document.addEventListener('keydown', function(e) {
            if (e.key.toLowerCase() === 'n' && !['input','textarea','select'].includes(document.activeElement.tagName.toLowerCase())) {
                e.preventDefault();
                if (typeof openCreateModal === 'function') openCreateModal();
            }
        });

        // Auto-dismiss top alert
        setTimeout(() => {
            const alert = document.getElementById('topAlertSuccess');
            if (alert) { alert.style.opacity = '0'; setTimeout(() => alert.remove(), 300); }
        }, 5000);
    </script>
    @stack('scripts')
</body>
</html>
