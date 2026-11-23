@extends('layouts.app')

@section('title', 'Dashboard Keuangan Bulanan')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan arus kas & tabungan bulanan Anda')

@section('content')
<div class="space-y-6 sm:space-y-8">

    <!-- Top Hero Section: Title & Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-3">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.05] border border-white/10 text-[#f4ba88] text-xs font-semibold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-[#f4ba88] animate-pulse"></span>
                <span>Periode: {{ $activeMonthLabel }}</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
                Ringkasan Keuangan Bulanan.
            </h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-400">
                Pantau total pemasukan, pengeluaran, dan sisa saldo bulanan secara otomatis.
            </p>
        </div>

        <!-- Month / Date Filter Selector & Export / Actions -->
        <div class="no-print flex flex-wrap items-center gap-2.5 sm:self-center">
            
            <!-- Interactive Windows-Style Calendar Date & Month Picker -->
            <div class="relative" id="calendarPickerContainer">
                <button 
                    type="button" 
                    onclick="toggleCalendarPicker()" 
                    id="calendarPickerTrigger"
                    class="btn-lull-secondary text-xs sm:text-sm !py-2.5 !px-4"
                    title="Buka Kalender Pemilih Periode & Tanggal"
                >
                    <svg class="w-4 h-4 text-[#f4ba88]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span id="calendarTriggerText" class="text-white font-medium">{{ $activeMonthLabel }}</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- CALENDAR POPUP: Centered on mobile phones, dropdown on desktop -->
                <div 
                    id="calendarPickerDropdown" 
                    class="hidden fixed inset-x-3 top-24 sm:absolute sm:inset-auto sm:right-0 sm:top-auto sm:mt-2 z-50 w-auto sm:w-[330px] max-w-[340px] mx-auto bg-[#0d121c] text-white rounded-3xl shadow-2xl border border-white/15 p-4 transition-all"
                >
                    <!-- Calendar Header Bar -->
                    <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                        <!-- Clickable Title to switch Days -> Months -> Years -->
                        <button 
                            type="button" 
                            onclick="cycleCalendarView()" 
                            id="calHeaderTitleBtn"
                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-sm font-bold text-white hover:bg-slate-800 hover:text-emerald-400 transition-colors cursor-pointer"
                            title="Klik untuk memilih Bulan atau Tahun"
                        >
                            <span id="calHeaderTitleText">November 2026</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Up and Down Navigation Arrows (matching Windows Calendar) -->
                        <div class="flex items-center gap-1">
                            <button 
                                type="button" 
                                onclick="navCalendarPrev()" 
                                class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer" 
                                title="Sebelumnya"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                            </button>
                            <button 
                                type="button" 
                                onclick="navCalendarNext()" 
                                class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer" 
                                title="Berikutnya"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- VIEW 1: DAYS VIEW -->
                    <div id="calViewDays" class="pt-2.5">
                        <!-- Days of week headers: Su Mo Tu We Th Fr Sa -->
                        <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold text-slate-400 pb-1.5">
                            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                        </div>
                        <!-- Days Grid (42 cells) -->
                        <div class="grid grid-cols-7 gap-1 text-center text-xs" id="calDaysGrid">
                            <!-- Injected dynamically via JS -->
                        </div>
                    </div>

                    <!-- VIEW 2: MONTHS VIEW (12 Months) -->
                    <div id="calViewMonths" class="hidden pt-3">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 pb-2 text-center">
                            Pilih Bulan
                        </div>
                        <div class="grid grid-cols-3 gap-2" id="calMonthsGrid">
                            <!-- Injected via JS -->
                        </div>
                    </div>

                    <!-- VIEW 3: YEARS VIEW (Decades / All Years) -->
                    <div id="calViewYears" class="hidden pt-3">
                        <div class="flex items-center justify-between pb-2 text-xs font-semibold text-slate-400">
                            <span>Pilih Tahun</span>
                            <span id="calYearsRangeLabel">2020 - 2031</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2" id="calYearsGrid">
                            <!-- Injected via JS -->
                        </div>
                    </div>

                    <!-- Footer Action Bar -->
                    <div class="mt-3 pt-2.5 border-t border-slate-800 flex items-center justify-between text-xs font-medium">
                        <button 
                            type="button" 
                            onclick="selectFullMonth()" 
                            class="text-emerald-400 hover:text-emerald-300 hover:underline cursor-pointer"
                            title="Tampilkan transaksi 1 bulan penuh"
                        >
                            1 Bulan Penuh
                        </button>
                        <button 
                            type="button" 
                            onclick="selectAllPeriod()" 
                            class="text-slate-400 hover:text-white hover:underline cursor-pointer"
                            title="Tampilkan semua periode"
                        >
                            Semua Periode
                        </button>
                        <button 
                            type="button" 
                            onclick="selectToday()" 
                            class="text-blue-400 hover:text-blue-300 hover:underline cursor-pointer"
                            title="Pilih hari ini"
                        >
                            Hari Ini
                        </button>
                    </div>
                </div>
            </div>

            @if(($selectedMonth !== $currentMonthKey && $selectedMonth !== 'all') || $selectedDate)
                <a 
                    href="{{ route('dashboard', ['month' => $currentMonthKey]) }}" 
                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl transition-colors cursor-pointer"
                    title="Kembali ke bulan berjalan saat ini"
                >
                    Bulan Ini
                </a>
            @endif

            <!-- Export to CSV / Excel Button -->
            <a 
                href="{{ route('transactions.export', ['month' => $selectedMonth]) }}" 
                class="btn-lull-secondary text-xs sm:text-sm !py-2.5 !px-4"
                title="Unduh laporan ke format Excel / CSV"
            >
                <svg class="w-4 h-4 text-[#f4ba88]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Excel</span>
            </a>

            <!-- Print Button -->
            <button 
                type="button" 
                onclick="window.print()" 
                class="btn-lull-secondary text-xs sm:text-sm !py-2.5 !px-3.5"
                title="Cetak Laporan Keuangan"
            >
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span class="hidden md:inline">Cetak</span>
            </button>

            <!-- Add Transaction Primary Button: Signature Peach Pill -->
            <button 
                type="button" 
                onclick="openCreateModal()" 
                class="btn-lull-primary text-xs sm:text-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>+ Transaksi Baru</span>
            </button>
        </div>
    </div>

    <!-- 3 Big Metric Cards: Lull Ribbon Dark Glass -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        
        <!-- 1. TOTAL PEMASUKAN -->
        <div class="lull-card p-6 relative overflow-hidden group hover:border-white/20 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    TOTAL PEMASUKAN
                </span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center border border-emerald-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-white font-mono tracking-tight tabular-nums">
                    Rp {{ number_format($totalIncome, 0, ',', '.') }}
                </div>
                <div class="mt-2.5 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/15 text-emerald-300 border border-emerald-500/20">
                        <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        Penerimaan Kas
                    </span>
                    <span class="text-xs text-slate-400">
                        {{ $transactions->where('type', 'Pemasukan')->count() }} transaksi
                    </span>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-600 to-emerald-400"></div>
        </div>

        <!-- 2. TOTAL PENGELUARAN -->
        <div class="lull-card p-6 relative overflow-hidden group hover:border-white/20 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    TOTAL PENGELUARAN
                </span>
                <div class="w-9 h-9 rounded-xl bg-rose-500/15 text-rose-400 flex items-center justify-center border border-rose-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold text-white font-mono tracking-tight tabular-nums">
                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                </div>
                <div class="mt-2.5 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-500/15 text-rose-300 border border-rose-500/20">
                        <svg class="w-3 h-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        Biaya & Konsumsi
                    </span>
                    <span class="text-xs text-slate-400">
                        {{ $transactions->where('type', 'Pengeluaran')->count() }} transaksi
                    </span>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-rose-600 to-rose-400"></div>
        </div>

        <!-- 3. SISA SALDO / TABUNGAN (Signature Peach Accent) -->
        <div class="lull-card p-6 relative overflow-hidden group hover:border-white/20 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    SISA SALDO / TABUNGAN
                </span>
                <div class="w-9 h-9 rounded-xl bg-[#f4ba88]/15 text-[#f4ba88] flex items-center justify-center border border-[#f4ba88]/20 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-extrabold {{ $netBalance >= 0 ? 'text-[#f4ba88]' : 'text-rose-400' }} font-mono tracking-tight tabular-nums">
                    Rp {{ number_format($netBalance, 0, ',', '.') }}
                </div>
                <div class="mt-2.5 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $netBalance >= 0 ? 'bg-[#f4ba88]/15 text-[#f4ba88] border border-[#f4ba88]/30' : 'bg-rose-500/15 text-rose-300 border border-rose-500/20' }}">
                        {{ $savingsRatio }}% Rasio Tabungan
                    </span>
                    <span class="text-xs text-slate-400">
                        {{ $netBalance >= 0 ? 'Surplus aman' : 'Defisit' }}
                    </span>
                </div>
            </div>
            <div class="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-[#d97706] to-[#f4ba88]"></div>
        </div>

    </div>

    <!-- Main Split: Left (Daily Transactions Table) vs Right (Category Breakdown & Insights) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
        
        <!-- Left Column: Catatan Transaksi Harian (8 cols on lg) -->
        <div class="lg:col-span-8 space-y-4">
            
            <div class="lull-card overflow-hidden">
                <!-- Card Header -->
                <div class="p-5 sm:p-6 border-b border-white/[0.08]">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-white tracking-tight">
                                Catatan Transaksi Harian (Pemasukan & Pengeluaran)
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Pantau dan kelola seluruh transaksi pemasukan serta pos belanja Anda.
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold px-2.5 py-1 bg-white/10 text-slate-200 border border-white/10 rounded-xl">
                                <span id="visibleRowCount">{{ $transactions->count() }}</span> Data
                            </span>
                        </div>
                    </div>

                    <!-- Filter & Search Toolbar -->
                    <div class="no-print mt-4 pt-4 border-t border-white/[0.08] grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                        <!-- Search Box -->
                        <div class="sm:col-span-5 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="tableSearchInput" 
                                onkeyup="filterTransactionsTable()" 
                                placeholder="Cari keterangan, keperluan..." 
                                class="w-full pl-9 pr-3.5 py-2 text-xs bg-[#0c1018] hover:bg-[#0f1422] focus:bg-[#0f1422] border border-white/15 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-[#f4ba88]/20 focus:border-[#f4ba88] transition-all"
                            >
                        </div>

                        <!-- Type Filter -->
                        <div class="sm:col-span-3">
                            <select 
                                id="typeFilter" 
                                onchange="filterTransactionsTable()" 
                                class="w-full px-3 py-2 text-xs bg-[#0c1018] border border-white/15 rounded-xl text-slate-200 font-medium focus:outline-none focus:ring-2 focus:ring-[#f4ba88]/20 focus:border-[#f4ba88] cursor-pointer"
                            >
                                <option value="">Semua Jenis</option>
                                <option value="Pemasukan">Pemasukan (+)</option>
                                <option value="Pengeluaran">Pengeluaran (-)</option>
                            </select>
                        </div>

                        <!-- Category Filter -->
                        <div class="sm:col-span-4">
                            <select 
                                id="categoryFilter" 
                                onchange="filterTransactionsTable()" 
                                class="w-full px-3 py-2 text-xs bg-[#0c1018] border border-white/15 rounded-xl text-slate-200 font-medium focus:outline-none focus:ring-2 focus:ring-[#f4ba88]/20 focus:border-[#f4ba88] cursor-pointer"
                            >
                                <option value="">Semua Kategori</option>
                                <optgroup label="Pengeluaran">
                                    @foreach($expenseCategories as $cat => $meta)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Pemasukan">
                                    @foreach($incomeCategories as $cat => $meta)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- MOBILE VIEW: Interactive Native App Card List (sm:hidden) --}}
                <div class="sm:hidden divide-y divide-white/[0.06]" id="dashboardMobileTransactionsList">
                    @forelse($transactions as $trx)
                    <div 
                        class="dashboard-mobile-card p-4 hover:bg-white/[0.02] transition-colors"
                        data-type="{{ $trx->type }}" 
                        data-category="{{ $trx->category }}"
                        data-desc="{{ strtolower($trx->description . ' ' . $trx->notes) }}"
                        data-amount-income="{{ $trx->type === 'Pemasukan' ? $trx->amount : 0 }}"
                        data-amount-expense="{{ $trx->type === 'Pengeluaran' ? $trx->amount : 0 }}"
                    >
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="text-slate-400 font-medium">
                                {{ \Carbon\Carbon::parse($trx->date)->format('d M Y') }} &bull; {{ \Carbon\Carbon::parse($trx->date)->translatedFormat('l') }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $trx->type === 'Pengeluaran' ? 'bg-rose-500/10 text-rose-300 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20' }}">
                                {{ $trx->category }}
                            </span>
                        </div>

                        <div class="mb-2">
                            <div class="font-semibold text-white text-sm">
                                {{ $trx->description ?: $trx->category }}
                            </div>
                            @if($trx->notes)
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $trx->notes }}</div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-white/[0.04]">
                            <div>
                                <div class="text-base font-bold font-mono {{ $trx->type === 'Pemasukan' ? 'text-emerald-400' : 'text-rose-400' }}">
                                    {{ $trx->type === 'Pemasukan' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $trx->payment_method }}
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button 
                                    type="button" 
                                    class="btn-edit-transaction p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 active:bg-white/20 transition-colors" 
                                    data-id="{{ $trx->id }}"
                                    data-type="{{ $trx->type }}"
                                    data-date="{{ \Carbon\Carbon::parse($trx->date)->format('Y-m-d') }}"
                                    data-category="{{ $trx->category }}"
                                    data-amount="{{ number_format($trx->amount, 0, ',', '.') }}"
                                    data-payment-method="{{ $trx->payment_method }}"
                                    data-description="{{ $trx->description ?? '' }}"
                                    data-notes="{{ $trx->notes ?? '' }}"
                                    title="Edit transaksi"
                                    aria-label="Edit"
                                >
                                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button 
                                    type="button" 
                                    class="btn-delete-transaction p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 active:bg-rose-500/20 transition-colors" 
                                    data-id="{{ $trx->id }}"
                                    data-title="{{ $trx->description ?: $trx->category }}"
                                    data-amount="{{ $trx->formatted_amount }}"
                                    title="Hapus transaksi"
                                    aria-label="Hapus"
                                >
                                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-12 px-4 text-center text-slate-400" id="emptyMobilePlaceholder">
                        <div class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 text-slate-400 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="font-semibold text-white">Belum ada transaksi di periode ini</p>
                        <p class="text-xs text-slate-400 mt-1">Tekan tombol "+ Transaksi Baru" untuk mulai mencatat keuangan.</p>
                    </div>
                    @endforelse
                </div>

                <!-- DESKTOP VIEW: Transaction Table (hidden on mobile, visible on sm and up) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="transactionsTable">
                        <thead>
                            <tr class="bg-[#0c1018] border-b border-white/[0.08] text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th scope="col" class="py-3 px-4">Tanggal</th>
                                <th scope="col" class="py-3 px-3">Jenis</th>
                                <th scope="col" class="py-3 px-3">Kategori</th>
                                <th scope="col" class="py-3 px-3">Deskripsi / Keperluan</th>
                                <th scope="col" class="py-3 px-3 text-right">Pemasukan (Rp)</th>
                                <th scope="col" class="py-3 px-3 text-right">Pengeluaran (Rp)</th>
                                <th scope="col" class="py-3 px-3">Metode / Akun</th>
                                <th scope="col" class="no-print py-3 px-3 text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.05] text-xs text-slate-300">
                            @forelse($transactions as $trx)
                            <tr 
                                class="transaction-row hover:bg-white/[0.02] transition-colors group" 
                                data-type="{{ $trx->type }}" 
                                data-category="{{ $trx->category }}"
                                data-desc="{{ strtolower($trx->description . ' ' . $trx->notes) }}"
                                data-amount-income="{{ $trx->type === 'Pemasukan' ? $trx->amount : 0 }}"
                                data-amount-expense="{{ $trx->type === 'Pengeluaran' ? $trx->amount : 0 }}"
                            >
                                <!-- Tanggal -->
                                <td class="py-3.5 px-4 whitespace-nowrap font-medium text-white">
                                    {{ \Carbon\Carbon::parse($trx->date)->format('Y-m-d') }}
                                    <span class="block text-[10px] text-slate-400">
                                        {{ \Carbon\Carbon::parse($trx->date)->translatedFormat('D, d M') }}
                                    </span>
                                </td>

                                <!-- Jenis Transaksi -->
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    @if($trx->type === 'Pemasukan')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Pemasukan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-500/10 text-rose-300 border border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                            Pengeluaran
                                        </span>
                                    @endif
                                </td>

                                <!-- Kategori -->
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 font-medium text-slate-200">
                                        {{ $trx->category }}
                                    </span>
                                </td>

                                <!-- Deskripsi / Keperluan -->
                                <td class="py-3.5 px-3 max-w-[180px] sm:max-w-[220px]">
                                    <div class="font-medium text-white truncate" title="{{ $trx->description }}">
                                        {{ $trx->description ?: '-' }}
                                    </div>
                                    @if($trx->notes)
                                        <div class="text-[11px] text-slate-400 truncate" title="{{ $trx->notes }}">
                                            {{ $trx->notes }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Pemasukan (Rp) -->
                                <td class="py-3.5 px-3 text-right whitespace-nowrap font-mono tabular-nums font-semibold">
                                    @if($trx->type === 'Pemasukan')
                                        <span class="text-emerald-400">
                                            {{ number_format($trx->amount, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-slate-600">-</span>
                                    @endif
                                </td>

                                <!-- Pengeluaran (Rp) -->
                                <td class="py-3.5 px-3 text-right whitespace-nowrap font-mono tabular-nums font-semibold">
                                    @if($trx->type === 'Pengeluaran')
                                        <span class="text-rose-400">
                                            {{ number_format($trx->amount, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-slate-600">-</span>
                                    @endif
                                </td>

                                <!-- Metode / Akun -->
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] bg-white/5 text-slate-300 border border-white/10">
                                        {{ $trx->payment_method }}
                                    </span>
                                </td>

                                <!-- Aksi (Edit & Delete) -->
                                <td class="no-print py-3.5 px-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5 opacity-90 group-hover:opacity-100 transition-opacity">
                                        <button 
                                            type="button" 
                                            class="btn-edit-transaction p-1.5 text-slate-400 hover:text-white hover:bg-white/10 active:bg-white/20 rounded-lg transition-colors cursor-pointer" 
                                            data-id="{{ $trx->id }}"
                                            data-type="{{ $trx->type }}"
                                            data-date="{{ \Carbon\Carbon::parse($trx->date)->format('Y-m-d') }}"
                                            data-category="{{ $trx->category }}"
                                            data-amount="{{ number_format($trx->amount, 0, ',', '.') }}"
                                            data-payment-method="{{ $trx->payment_method }}"
                                            data-description="{{ $trx->description ?? '' }}"
                                            data-notes="{{ $trx->notes ?? '' }}"
                                            title="Edit transaksi"
                                        >
                                            <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn-delete-transaction p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 active:bg-rose-500/20 rounded-lg transition-colors cursor-pointer" 
                                            data-id="{{ $trx->id }}"
                                            data-title="{{ $trx->description ?: $trx->category }}"
                                            data-amount="{{ $trx->formatted_amount }}"
                                            title="Hapus transaksi"
                                        >
                                            <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr id="emptyRowPlaceholder">
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 text-slate-400 mx-auto flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="font-semibold text-white">Belum ada transaksi di periode ini</p>
                                    <p class="text-xs text-slate-400 mt-1">Tekan tombol "+ Transaksi Baru" untuk mulai mencatat keuangan.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        
                        <!-- Table Footer Summary: Exact matching the Excel bottom total row -->
                        <tfoot class="bg-[#0c1018] border-t-2 border-white/15 font-bold text-xs text-white">
                            <tr>
                                <td colspan="4" class="py-3.5 px-4 text-right uppercase tracking-wider text-slate-400">
                                    TOTAL KESELURUHAN:
                                </td>
                                <td class="py-3.5 px-3 text-right font-mono tabular-nums text-emerald-400" id="footerTotalIncome">
                                    {{ number_format($filteredTotalIncome, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-3 text-right font-mono tabular-nums text-rose-400" id="footerTotalExpense">
                                    {{ number_format($filteredTotalExpense, 0, ',', '.') }}
                                </td>
                                <td colspan="2" class="py-3.5 px-3 text-slate-400 font-normal text-[11px]">
                                    Sisa: <span class="font-mono font-bold {{ ($filteredTotalIncome - $filteredTotalExpense) >= 0 ? 'text-[#f4ba88]' : 'text-rose-400' }}" id="footerNet">Rp {{ number_format($filteredTotalIncome - $filteredTotalExpense, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

        </div>

        <!-- Right Column: Kategori Pengeluaran (4 cols on lg) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Kategori Pengeluaran Card (Lull Glass) -->
            <div class="lull-card overflow-hidden print-card">
                <div class="p-5 border-b border-white/[0.08]">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">Kategori Pengeluaran</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Proporsi belanja pos kebutuhan</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-rose-500/10 text-rose-300 border border-rose-500/20">
                            8 Pos Standar
                        </span>
                    </div>
                </div>

                <div class="divide-y divide-white/[0.05]">
                    @foreach($categoryBreakdown as $catName => $item)
                    <div class="p-3.5 hover:bg-white/[0.02] transition-colors">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $item['color'] ?? '#f4ba88' }}; box-shadow: 0 0 6px {{ $item['color'] ?? '#f4ba88' }}80;"></span>
                                <span class="font-semibold text-slate-200 truncate">{{ $catName }}</span>
                                @if($item['count'] > 0)
                                    <span class="text-[10px] text-slate-400 flex-shrink-0">({{ $item['count'] }})</span>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="font-mono font-bold text-white tabular-nums">
                                    Rp {{ number_format($item['total'], 0, ',', '.') }}
                                </span>
                                <span class="text-[11px] font-semibold text-slate-400 ml-1.5 w-9 inline-block text-right">
                                    {{ $item['percentage'] }}%
                                </span>
                            </div>
                        </div>
                        <!-- Progress Bar (Lull Track) -->
                        <div class="w-full bg-white/5 h-1.5 rounded-full overflow-hidden">
                            <div 
                                class="h-full rounded-full transition-all duration-500" 
                                style="width: {{ $item['percentage'] }}%; background-color: {{ $item['color'] ?? '#f4ba88' }}; box-shadow: 0 0 8px {{ $item['color'] ?? '#f4ba88' }};"
                            ></div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Footer Summary -->
                <div class="p-4 bg-[#0c1018] border-t border-white/[0.08] flex items-center justify-between text-xs font-bold text-white">
                    <span>Total Pengeluaran</span>
                    <div class="text-right">
                        <span class="font-mono text-rose-400 mr-2">
                            Rp {{ number_format($totalExpense, 0, ',', '.') }}
                        </span>
                        <span class="text-slate-400">
                            {{ $totalExpense > 0 ? '100%' : '0%' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Financial Health & Insights Card (Lull Amber Glass Accent) -->
            <div class="lull-card p-5 relative overflow-hidden border border-[#f4ba88]/20 shadow-xl shadow-black/40">
                <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-8 h-8 rounded-xl bg-[#f4ba88]/15 text-[#f4ba88] border border-[#f4ba88]/25 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#f4ba88]">Analisis Finansial</span>
                </div>
                
                @if($totalIncome > 0)
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Anda menghemat <strong class="text-white">{{ $savingsRatio }}%</strong> dari total pemasukan periode ini. 
                        @if($totalExpense > 0)
                            Pengeluaran didominasi pos kebutuhan pokok dengan total <strong class="text-[#f4ba88]">Rp {{ number_format($totalExpense, 0, ',', '.') }}</strong>.
                        @else
                            Belum ada pengeluaran yang tercatat pada periode ini.
                        @endif
                    </p>
                @else
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Belum ada data pemasukan untuk menghitung rasio tabungan. Tambahkan transaksi pemasukan seperti gaji pokok atau penghasilan usaha.
                    </p>
                @endif

                <div class="mt-4 pt-3 border-t border-white/[0.08] flex items-center justify-between text-[11px] text-slate-400">
                    <span>Aturan 50/30/20 Anggaran</span>
                    <span class="text-emerald-400 font-medium">Kondisi Sehat</span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('modals')
<!-- MODAL: Tambah Transaksi Baru (Mobile Bottom-Sheet & Desktop Centered Dialog) -->
<div id="createModal" class="hidden fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity z-0 cursor-pointer" onclick="closeCreateModal()"></div>

    <!-- Centering & Bottom Sheet wrapper -->
    <div class="relative z-10 flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4 text-center pointer-events-none">
        <!-- Modal Dialog Card -->
        <div class="relative z-20 pointer-events-auto transform overflow-hidden rounded-t-3xl sm:rounded-2xl bg-[#0d121c] text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg border-t sm:border border-white/15 max-h-[92vh] overflow-y-auto">
            <div class="w-12 h-1 bg-white/20 rounded-full mx-auto my-2.5 sm:hidden"></div>
            <form method="POST" action="{{ route('transactions.store') }}" id="createTransactionForm">
                @csrf

                <!-- Modal Header -->
                <div class="px-5 sm:px-6 pt-3 sm:pt-5 pb-4 border-b border-white/[0.08] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center border border-emerald-500/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">Catat Transaksi Baru</h3>
                            <p class="text-xs text-slate-400">Pilih jenis, isi nominal, dan tentukan pos kebutuhan</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-colors cursor-pointer">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 sm:p-6 space-y-4">
                    <!-- Segmented Switcher: Pemasukan vs Pengeluaran -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Jenis Transaksi</label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-[#07090e] border border-white/[0.08] rounded-xl">
                            <label class="relative flex items-center justify-center py-2 px-3 rounded-lg text-xs font-bold cursor-pointer transition-all has-[:checked]:bg-[#f4ba88] has-[:checked]:text-[#181109] text-slate-400">
                                <input type="radio" name="type" value="Pemasukan" class="sr-only" onchange="handleTypeChange('create', 'Pemasukan')">
                                <svg class="w-3.5 h-3.5 mr-1.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                Pemasukan (+)
                            </label>
                            <label class="relative flex items-center justify-center py-2 px-3 rounded-lg text-xs font-bold cursor-pointer transition-all has-[:checked]:bg-rose-500 has-[:checked]:text-white text-slate-400">
                                <input type="radio" name="type" value="Pengeluaran" class="sr-only" checked onchange="handleTypeChange('create', 'Pengeluaran')">
                                <svg class="w-3.5 h-3.5 mr-1.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                Pengeluaran (-)
                            </label>
                        </div>
                    </div>

                    <!-- Nominal Amount with Instant Live Currency Formatting -->
                    <div>
                        <label for="create_amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Nominal (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-bold text-slate-400">Rp</span>
                            <input 
                                type="text" 
                                id="create_amount" 
                                name="amount" 
                                required 
                                oninput="formatRupiahInput(this)" 
                                placeholder="0" 
                                class="w-full pl-10 pr-4 py-2.5 bg-[#07090e] border border-white/15 rounded-xl font-mono text-base font-bold text-white focus:outline-none focus:border-[#f4ba88] focus:ring-2 focus:ring-[#f4ba88]/20 transition-all"
                            >
                        </div>
                    </div>

                    <!-- Tanggal & Metode (Grid 2 cols) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label for="create_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Tanggal</label>
                            <input 
                                type="date" 
                                id="create_date" 
                                name="date" 
                                value="{{ date('Y-m-d') }}" 
                                required 
                                class="w-full px-3 py-2 bg-[#07090e] border border-white/15 rounded-xl text-xs sm:text-sm text-white focus:outline-none focus:border-[#f4ba88] focus:ring-2 focus:ring-[#f4ba88]/20"
                            >
                        </div>
                        <div>
                            <label for="create_payment_method" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Metode / Akun</label>
                            <select 
                                id="create_payment_method" 
                                name="payment_method" 
                                required 
                                class="w-full px-3 py-2 bg-[#07090e] border border-white/15 rounded-xl text-xs sm:text-sm text-white focus:outline-none focus:border-[#f4ba88] focus:ring-2 focus:ring-[#f4ba88]/20"
                            >
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm }}">{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Kategori Dropdown -->
                    <div>
                        <label for="create_category" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Kategori</label>
                        <select 
                            id="create_category" 
                            name="category" 
                            required 
                            class="w-full px-3 py-2 bg-[#07090e] border border-white/15 rounded-xl text-xs sm:text-sm text-white focus:outline-none focus:border-[#f4ba88] focus:ring-2 focus:ring-[#f4ba88]/20"
                        >
                            <!-- Populated via JS based on type -->
                        </select>
                    </div>

                    <!-- Deskripsi / Keperluan -->
                    <div>
                        <label for="create_description" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Deskripsi / Keperluan
                        </label>
                        <input 
                            type="text" 
                            id="create_description" 
                            name="description" 
                            placeholder="Contoh: makan siang, bensin, token listrik..." 
                            class="w-full px-3.5 py-2 bg-[#07090e] border border-white/15 rounded-xl text-xs sm:text-sm text-white focus:outline-none focus:border-[#f4ba88] focus:ring-2 focus:ring-[#f4ba88]/20"
                        >
                        <!-- Quick Suggestions Chips -->
                        <div class="mt-2 flex flex-wrap gap-1.5" id="createQuickChips">
                            <!-- Populated via JS based on type -->
                        </div>
                    </div>

                    <!-- Catatan Tambahan (Opsional) -->
                    <div>
                        <label for="create_notes" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Catatan Tambahan <span class="text-slate-500 font-normal">(Opsional)</span>
                        </label>
                        <input 
                            type="text" 
                            id="create_notes" 
                            name="notes" 
                            placeholder="Catatan kecil atau referensi nota/struk" 
                            class="w-full px-3.5 py-2 bg-[#07090e] border border-white/15 rounded-xl text-xs sm:text-sm text-white focus:outline-none focus:border-[#f4ba88] focus:ring-2 focus:ring-[#f4ba88]/20"
                        >
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="px-5 sm:px-6 py-4 bg-[#090d14] border-t border-white/[0.08] flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeCreateModal()" class="btn-lull-secondary text-xs">
                        Batal
                    </button>
                    <button type="submit" class="btn-lull-primary text-xs">
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Edit Transaksi -->
<div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity z-0 cursor-pointer" onclick="closeEditModal()"></div>

    <!-- Centering & Bottom Sheet wrapper -->
    <div class="relative z-10 flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4 text-center pointer-events-none">
        <!-- Modal Dialog Card -->
        <div class="relative z-20 pointer-events-auto transform overflow-hidden rounded-t-3xl sm:rounded-2xl bg-[#0d121c] text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg border-t sm:border border-white/15 max-h-[92vh] overflow-y-auto">
            <div class="w-12 h-1 bg-white/20 rounded-full mx-auto my-2.5 sm:hidden"></div>
            <form method="POST" action="" id="editTransactionForm">
                @csrf
                @method('PUT')

                <div class="px-5 sm:px-6 pt-3 sm:pt-5 pb-4 border-b border-white/[0.08] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/15 text-blue-400 flex items-center justify-center border border-blue-500/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight">Edit Data Transaksi</h3>
                            <p class="text-xs text-slate-400">Perbarui rincian transaksi keuangan</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-colors cursor-pointer">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Jenis Transaksi</label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-[#07090e] border border-white/[0.08] rounded-xl">
                            <label class="relative flex items-center justify-center py-2 px-3 rounded-lg text-xs font-bold cursor-pointer transition-all has-[:checked]:bg-[#f4ba88] has-[:checked]:text-[#181109] text-slate-400">
                                <input type="radio" name="type" id="edit_type_income" value="Pemasukan" class="sr-only" onchange="handleTypeChange('edit', 'Pemasukan')">
                                <svg class="w-3.5 h-3.5 mr-1.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                                Pemasukan (+)
                            </label>
                            <label class="relative flex items-center justify-center py-2 px-3 rounded-lg text-xs font-bold cursor-pointer transition-all has-[:checked]:bg-rose-500 has-[:checked]:text-white text-slate-400">
                                <input type="radio" name="type" id="edit_type_expense" value="Pengeluaran" class="sr-only" onchange="handleTypeChange('edit', 'Pengeluaran')">
                                <svg class="w-3.5 h-3.5 mr-1.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                Pengeluaran (-)
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="edit_amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Nominal (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-bold text-slate-400">Rp</span>
                            <input 
                                type="text" 
                                id="edit_amount" 
                                name="amount" 
                                required 
                                oninput="formatRupiahInput(this)" 
                                class="w-full pl-10 pr-4 py-2.5 bg-[#07090e] border border-white/15 rounded-xl font-mono text-base font-bold text-white focus:outline-none focus:border-[#f4ba88] focus:ring-2 focus:ring-[#f4ba88]/20"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label for="edit_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Tanggal</label>
                            <input 
                                type="date" 
                                id="edit_date" 
                                name="date" 
                                required 
                                class="w-full px-3 py-2 bg-[#07090e] border border-white/15 rounded-xl text-xs sm:text-sm text-white focus:outline-none focus:border-[#f4ba88] focus:ring-2 focus:ring-[#f4ba88]/20"
                            >
                        </div>
                        <div>
                            <label for="edit_payment_method" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Metode / Akun</label>
                            <select 
                                id="edit_payment_method" 
                                name="payment_method" 
                                required 
                                class="w-full px-3 py-2 bg-[#07090e] border border-white/15 rounded-xl text-xs sm:text-sm text-white"
                            >
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm }}">{{ $pm }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="edit_category" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Kategori</label>
                        <select 
                            id="edit_category" 
                            name="category" 
                            required 
                            class="w-full px-3 py-2 bg-[#07090e] border border-white/15 rounded-xl text-xs sm:text-sm text-white"
                        >
                            <!-- Populated via JS -->
                        </select>
                    </div>

                    <div>
                        <label for="edit_description" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Deskripsi / Keperluan</label>
                        <input 
                            type="text" 
                            id="edit_description" 
                            name="description" 
                            class="w-full px-3.5 py-2 bg-[#07090e] border border-white/15 rounded-xl text-xs sm:text-sm text-white"
                        >
                    </div>

                    <div>
                        <label for="edit_notes" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Catatan Tambahan</label>
                        <input 
                            type="text" 
                            id="edit_notes" 
                            name="notes" 
                            class="w-full px-3.5 py-2 bg-[#07090e] border border-white/15 rounded-xl text-xs sm:text-sm text-white"
                        >
                    </div>
                </div>

                <div class="px-5 sm:px-6 py-4 bg-[#090d14] border-t border-white/[0.08] flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeEditModal()" class="btn-lull-secondary text-xs">
                        Batal
                    </button>
                    <button type="submit" class="btn-lull-primary text-xs">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Konfirmasi Hapus Transaksi -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto" style="display: none;" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity z-0 cursor-pointer" onclick="closeDeleteModal()"></div>

    <!-- Centering & Bottom Sheet wrapper -->
    <div class="relative z-10 flex min-h-screen items-end sm:items-center justify-center p-0 sm:p-4 text-center pointer-events-none">
        <!-- Modal Dialog Card -->
        <div class="relative z-20 pointer-events-auto transform overflow-hidden rounded-t-3xl sm:rounded-2xl bg-[#0d121c] text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md border-t sm:border border-white/15 p-6">
            <div class="w-12 h-1 bg-white/20 rounded-full mx-auto mb-4 sm:hidden"></div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Hapus Transaksi?</h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Apakah Anda yakin ingin menghapus transaksi <strong id="deleteTargetTitle" class="text-white"></strong> (<span id="deleteTargetAmount" class="font-mono font-semibold text-rose-400"></span>)?
                    </p>
                    <p class="text-[11px] text-slate-500 mt-1">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <form method="POST" action="" id="deleteTransactionForm" class="mt-6 flex items-center justify-end gap-2.5">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteModal()" class="btn-lull-secondary flex-1 text-xs justify-center">
                    Batal
                </button>
                <button type="submit" class="flex-1 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-full transition-colors justify-center flex items-center shadow-md">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const transactionsBaseUrl = "{{ url('transactions') }}";
    const expenseCategories = @json(array_keys($expenseCategories));
    const incomeCategories = @json(array_keys($incomeCategories));

    // Calendar Picker Data & State
    const transactionDates = @json($transactionDates);
    const currentSelectedDate = @json($selectedDate);
    const currentSelectedMonth = @json($selectedMonth);
    const todayStr = '{{ date('Y-m-d') }}';

    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    let calMode = 'days'; // 'days' | 'months' | 'years'
    let calYear = {{ (int) date('Y') }};
    let calMonth = {{ (int) date('n') - 1 }}; // 0 - 11
    let decadeStart = Math.floor(calYear / 12) * 12;

    // Initialize calendar view from current selection
    if (currentSelectedDate) {
        const parts = currentSelectedDate.split('-');
        if (parts.length === 3) {
            calYear = parseInt(parts[0], 10);
            calMonth = parseInt(parts[1], 10) - 1;
        }
    } else if (currentSelectedMonth && currentSelectedMonth !== 'all') {
        const parts = currentSelectedMonth.split('-');
        if (parts.length === 2) {
            calYear = parseInt(parts[0], 10);
            calMonth = parseInt(parts[1], 10) - 1;
        }
    }
    decadeStart = Math.floor(calYear / 12) * 12;

    window.toggleCalendarPicker = function() {
        const dropdown = document.getElementById('calendarPickerDropdown');
        if (!dropdown) return;
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            renderCalendar();
        }
    };

    window.cycleCalendarView = function() {
        if (calMode === 'days') {
            calMode = 'months';
        } else if (calMode === 'months') {
            calMode = 'years';
            decadeStart = Math.floor(calYear / 12) * 12;
        } else {
            calMode = 'days';
        }
        renderCalendar();
    };

    window.navCalendarPrev = function() {
        if (calMode === 'days') {
            calMonth--;
            if (calMonth < 0) {
                calMonth = 11;
                calYear--;
            }
        } else if (calMode === 'months') {
            calYear--;
        } else if (calMode === 'years') {
            decadeStart -= 12;
        }
        renderCalendar();
    };

    window.navCalendarNext = function() {
        if (calMode === 'days') {
            calMonth++;
            if (calMonth > 11) {
                calMonth = 0;
                calYear++;
            }
        } else if (calMode === 'months') {
            calYear++;
        } else if (calMode === 'years') {
            decadeStart += 12;
        }
        renderCalendar();
    };

    function renderCalendar() {
        const titleEl = document.getElementById('calHeaderTitleText');
        const viewDays = document.getElementById('calViewDays');
        const viewMonths = document.getElementById('calViewMonths');
        const viewYears = document.getElementById('calViewYears');

        if (!titleEl || !viewDays || !viewMonths || !viewYears) return;

        if (calMode === 'days') {
            titleEl.textContent = `${monthNames[calMonth]} ${calYear}`;
            viewDays.classList.remove('hidden');
            viewMonths.classList.add('hidden');
            viewYears.classList.add('hidden');
            renderDaysGrid();
        } else if (calMode === 'months') {
            titleEl.textContent = `${calYear}`;
            viewDays.classList.add('hidden');
            viewMonths.classList.remove('hidden');
            viewYears.classList.add('hidden');
            renderMonthsGrid();
        } else if (calMode === 'years') {
            const decadeEnd = decadeStart + 11;
            titleEl.textContent = `${decadeStart} - ${decadeEnd}`;
            const rangeEl = document.getElementById('calYearsRangeLabel');
            if (rangeEl) rangeEl.textContent = `${decadeStart} - ${decadeEnd}`;
            viewDays.classList.add('hidden');
            viewMonths.classList.add('hidden');
            viewYears.classList.remove('hidden');
            renderYearsGrid();
        }
    }

    function renderDaysGrid() {
        const container = document.getElementById('calDaysGrid');
        if (!container) return;
        container.innerHTML = '';

        const firstDay = new Date(calYear, calMonth, 1).getDay();
        const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
        const daysInPrevMonth = new Date(calYear, calMonth, 0).getDate();

        // Previous month tail days (dimmed)
        for (let i = firstDay - 1; i >= 0; i--) {
            const dayNum = daysInPrevMonth - i;
            const btn = document.createElement('div');
            btn.className = 'py-2 text-slate-600 select-none text-xs';
            btn.textContent = dayNum;
            container.appendChild(btn);
        }

        // Current month days
        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${calYear}-${String(calMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const isToday = (dateStr === todayStr);
            const isSelected = (dateStr === currentSelectedDate);
            const hasTrx = Boolean(transactionDates[dateStr]);

            const cell = document.createElement('button');
            cell.type = 'button';
            
            let classes = 'relative flex flex-col items-center justify-center py-1.5 rounded-lg transition-colors cursor-pointer text-xs ';
            if (isSelected) {
                classes += 'bg-blue-600 text-white font-bold ';
            } else if (isToday) {
                classes += 'ring-2 ring-blue-500 text-blue-400 font-bold hover:bg-slate-800 ';
            } else {
                classes += 'text-slate-100 hover:bg-slate-800 ';
            }

            cell.className = classes;
            
            const numSpan = document.createElement('span');
            numSpan.className = 'leading-none pointer-events-none font-medium';
            numSpan.textContent = d;
            cell.appendChild(numSpan);

            // White dot indicator matching user screenshot if transactions exist
            if (hasTrx) {
                const dot = document.createElement('span');
                dot.className = 'w-1 h-1 rounded-full ' + (isSelected ? 'bg-white' : 'bg-emerald-400') + ' mt-1 pointer-events-none';
                cell.appendChild(dot);
            } else {
                const spacer = document.createElement('span');
                spacer.className = 'w-1 h-1 mt-1 opacity-0 pointer-events-none';
                cell.appendChild(spacer);
            }

            cell.onclick = () => {
                window.location.href = `{{ route('dashboard') }}?date=${dateStr}`;
            };

            container.appendChild(cell);
        }

        // Next month head days (dimmed)
        const totalRendered = firstDay + daysInMonth;
        const remaining = 42 - totalRendered;
        for (let j = 1; j <= remaining; j++) {
            const btn = document.createElement('div');
            btn.className = 'py-2 text-slate-600 select-none text-xs';
            btn.textContent = j;
            container.appendChild(btn);
        }
    }

    function renderMonthsGrid() {
        const container = document.getElementById('calMonthsGrid');
        if (!container) return;
        container.innerHTML = '';
        monthNames.forEach((name, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            const mKey = `${calYear}-${String(idx + 1).padStart(2, '0')}`;
            const isSelected = (currentSelectedMonth === mKey);

            let classes = 'py-2.5 px-2 rounded-xl text-xs font-semibold transition-colors cursor-pointer text-center ';
            if (isSelected) {
                classes += 'bg-blue-600 text-white font-bold ';
            } else {
                classes += 'bg-slate-800/80 text-slate-200 hover:bg-slate-700 hover:text-white ';
            }
            btn.className = classes;
            btn.textContent = name.substring(0, 3);
            btn.title = `${name} ${calYear}`;

            btn.onclick = () => {
                calMonth = idx;
                calMode = 'days';
                renderCalendar();
            };

            container.appendChild(btn);
        });
    }

    function renderYearsGrid() {
        const container = document.getElementById('calYearsGrid');
        if (!container) return;
        container.innerHTML = '';
        for (let y = decadeStart; y < decadeStart + 12; y++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            const isCurrentYear = (y === calYear);

            let classes = 'py-2.5 px-2 rounded-xl text-xs font-semibold transition-colors cursor-pointer text-center ';
            if (isCurrentYear) {
                classes += 'bg-blue-600 text-white font-bold ';
            } else {
                classes += 'bg-slate-800/80 text-slate-200 hover:bg-slate-700 hover:text-white ';
            }
            btn.className = classes;
            btn.textContent = y;

            btn.onclick = () => {
                calYear = y;
                calMode = 'months';
                renderCalendar();
            };

            container.appendChild(btn);
        }
    }

    window.selectFullMonth = function() {
        const mKey = `${calYear}-${String(calMonth + 1).padStart(2, '0')}`;
        window.location.href = `{{ route('dashboard') }}?month=${mKey}`;
    };

    window.selectAllPeriod = function() {
        window.location.href = `{{ route('dashboard') }}?month=all`;
    };

    window.selectToday = function() {
        window.location.href = `{{ route('dashboard') }}?date=${todayStr}`;
    };

    // Close calendar when clicking outside
    document.addEventListener('click', function(e) {
        const container = document.getElementById('calendarPickerContainer');
        const dropdown = document.getElementById('calendarPickerDropdown');
        if (container && dropdown && !container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    const quickChips = {
        'Pengeluaran': ['makan pagi', 'makan siang', 'rokok', 'bensin', 'kopi', 'pulsa & internet', 'belanja dapur'],
        'Pemasukan': ['Gaji Pokok', 'Tunjangan', 'Bonus Kinerja', 'Freelance Project', 'Keuntungan Usaha']
    };

    window.handleTypeChange = function(prefix, type, selectedCategory = '') {
        const select = document.getElementById(prefix + '_category');
        if (!select) return;
        select.innerHTML = '';
        const list = (type === 'Pemasukan') ? incomeCategories : expenseCategories;
        list.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat;
            if (cat === selectedCategory) opt.selected = true;
            select.appendChild(opt);
        });

        // Update quick chips for create modal
        if (prefix === 'create') {
            const chipContainer = document.getElementById('createQuickChips');
            if (chipContainer) {
                chipContainer.innerHTML = '';
                const chips = quickChips[type] || [];
                chips.forEach(chip => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'px-2 py-0.5 rounded-md text-[11px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/80 transition-colors cursor-pointer';
                    btn.textContent = chip;
                    btn.onclick = () => {
                        const descInput = document.getElementById('create_description');
                        if (descInput) descInput.value = chip;
                    };
                    chipContainer.appendChild(btn);
                });
            }
        }
    };

    window.formatRupiahInput = function(input) {
        let val = input.value.replace(/\D/g, '');
        if (val) {
            input.value = new Intl.NumberFormat('id-ID').format(val);
        } else {
            input.value = '';
        }
    };

    window.openCreateModal = function() {
        window.handleTypeChange('create', 'Pengeluaran');
        const modal = document.getElementById('createModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'block';
            setTimeout(() => {
                const amt = document.getElementById('create_amount');
                if (amt) amt.focus();
            }, 50);
        }
    };

    window.closeCreateModal = function() {
        const modal = document.getElementById('createModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    };

    window.openEditModal = function(data) {
        const form = document.getElementById('editTransactionForm');
        if (!form) return;
        form.action = `${transactionsBaseUrl}/${data.id}`;

        const isIncome = data.type === 'Pemasukan';
        const incRadio = document.getElementById('edit_type_income');
        const expRadio = document.getElementById('edit_type_expense');
        if (incRadio && expRadio) {
            if (isIncome) {
                incRadio.checked = true;
            } else {
                expRadio.checked = true;
            }
        }
        window.handleTypeChange('edit', data.type, data.category);

        const amtInput = document.getElementById('edit_amount');
        if (amtInput) amtInput.value = data.amount;

        const dateInput = document.getElementById('edit_date');
        if (dateInput) dateInput.value = data.date;

        const pmSelect = document.getElementById('edit_payment_method');
        if (pmSelect) pmSelect.value = data.paymentMethod;

        const descInput = document.getElementById('edit_description');
        if (descInput) descInput.value = data.description || '';

        const notesInput = document.getElementById('edit_notes');
        if (notesInput) notesInput.value = data.notes || '';

        const modal = document.getElementById('editModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }
    };

    window.closeEditModal = function() {
        const modal = document.getElementById('editModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    };

    window.openDeleteModal = function(id, title, amountFormatted) {
        const form = document.getElementById('deleteTransactionForm');
        if (!form) return;
        form.action = `${transactionsBaseUrl}/${id}`;

        const titleEl = document.getElementById('deleteTargetTitle');
        if (titleEl) titleEl.textContent = `"${title}"`;

        const amtEl = document.getElementById('deleteTargetAmount');
        if (amtEl) amtEl.textContent = amountFormatted;

        const modal = document.getElementById('deleteModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }
    };

    window.closeDeleteModal = function() {
        const modal = document.getElementById('deleteModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    };

    // Instant Live Filtering for Table & Mobile Cards
    window.filterTransactionsTable = function() {
        const searchInput = document.getElementById('tableSearchInput');
        const search = searchInput ? searchInput.value.toLowerCase() : '';
        const typeSelect = document.getElementById('typeFilter');
        const type = typeSelect ? typeSelect.value : '';
        const catSelect = document.getElementById('categoryFilter');
        const category = catSelect ? catSelect.value : '';

        const rows = document.querySelectorAll('.transaction-row');
        const mobileCards = document.querySelectorAll('.dashboard-mobile-card');
        let visibleCount = 0;
        let runningIncome = 0;
        let runningExpense = 0;

        const checkMatch = (el) => {
            const rowType = el.getAttribute('data-type');
            const rowCat = el.getAttribute('data-category');
            const rowDesc = el.getAttribute('data-desc');
            const matchesSearch = !search || rowDesc.includes(search) || rowCat.toLowerCase().includes(search);
            const matchesType = !type || rowType === type;
            const matchesCat = !category || rowCat === category;
            return matchesSearch && matchesType && matchesCat;
        };

        rows.forEach(row => {
            const inc = parseFloat(row.getAttribute('data-amount-income')) || 0;
            const exp = parseFloat(row.getAttribute('data-amount-expense')) || 0;

            if (checkMatch(row)) {
                row.style.display = '';
                visibleCount++;
                runningIncome += inc;
                runningExpense += exp;
            } else {
                row.style.display = 'none';
            }
        });

        mobileCards.forEach(card => {
            if (checkMatch(card)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        const countEl = document.getElementById('visibleRowCount');
        if (countEl) countEl.textContent = visibleCount;

        const incomeEl = document.getElementById('footerTotalIncome');
        if (incomeEl) incomeEl.textContent = new Intl.NumberFormat('id-ID').format(runningIncome);

        const expenseEl = document.getElementById('footerTotalExpense');
        if (expenseEl) expenseEl.textContent = new Intl.NumberFormat('id-ID').format(runningExpense);
        
        const net = runningIncome - runningExpense;
        const footerNetEl = document.getElementById('footerNet');
        if (footerNetEl) {
            footerNetEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(net);
            footerNetEl.className = 'font-mono font-bold ' + (net >= 0 ? 'text-[#f4ba88]' : 'text-rose-400');
        }
    };

    // Event Delegation for Table Action Buttons
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit-transaction');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            window.openEditModal({
                id: editBtn.dataset.id,
                type: editBtn.dataset.type,
                date: editBtn.dataset.date,
                category: editBtn.dataset.category,
                amount: editBtn.dataset.amount,
                paymentMethod: editBtn.dataset.paymentMethod,
                description: editBtn.dataset.description,
                notes: editBtn.dataset.notes
            });
            return;
        }

        const deleteBtn = e.target.closest('.btn-delete-transaction');
        if (deleteBtn) {
            e.preventDefault();
            e.stopPropagation();
            window.openDeleteModal(
                deleteBtn.dataset.id, 
                deleteBtn.dataset.title, 
                deleteBtn.dataset.amount
            );
            return;
        }
    });

    // Initial setup on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        window.handleTypeChange('create', 'Pengeluaran');
    });

    // Close modals on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            window.closeCreateModal();
            window.closeEditModal();
            window.closeDeleteModal();
        }
    });
</script>
@endpush

