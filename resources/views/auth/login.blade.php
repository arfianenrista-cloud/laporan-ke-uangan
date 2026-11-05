@extends('layouts.app')

@section('title', 'Masuk ke Akun Anda')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-6 sm:py-12">
    <div class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-12 bg-white rounded-2xl shadow-xl border border-slate-200/80 overflow-hidden">
        
        <!-- Left Brand & Features Panel (Slate Dark theme) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <!-- Subtle background pattern -->
            <div class="absolute inset-0 opacity-10 pointer-events-none">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                    <defs>
                        <pattern id="grid" width="30" height="30" patternUnits="userSpaceOnUse">
                            <path d="M 30 0 L 0 0 0 30" fill="none" stroke="currentColor" stroke-width="0.5" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Pembukuan Pribadi
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                    Kelola uang dengan tenang & transparan.
                </h2>
                <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                    Catat setiap pos pemasukan dan pengeluaran harian, pantau alokasi kategori, dan lihat sisa tabungan bulanan Anda secara otomatis.
                </p>

                <!-- Value Highlights -->
                <div class="mt-8 space-y-3.5">
                    <div class="flex items-center gap-3 text-xs text-slate-200">
                        <div class="w-6 h-6 rounded-md bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span>Ringkasan Pemasukan & Pengeluaran Otomatis</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-200">
                        <div class="w-6 h-6 rounded-md bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span>Distribusi 8 Kategori Pengeluaran Standar</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-200">
                        <div class="w-6 h-6 rounded-md bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span>Ekspor format laporan CSV / Excel kapan saja</span>
                    </div>
                </div>
            </div>

            <!-- Mini Live Stat Preview -->
            <div class="relative z-10 mt-8 pt-6 border-t border-slate-700/60">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Sample Excel Ready</span>
                    <span class="font-mono font-semibold text-emerald-400">Rp 1.244.808 Saldo</span>
                </div>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-center">
            <div class="max-w-md w-full mx-auto">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">Selamat Datang Kembali</h3>
                    <p class="mt-1 text-xs sm:text-sm text-slate-500">
                        Masukkan kredensial akun Anda untuk membuka dashboard keuangan.
                    </p>
                </div>

                <!-- 1-Click Demo Account Quick Fill Button -->
                <div class="mt-5 p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-900">Uji Coba Cepat?</div>
                            <div class="text-[11px] text-slate-500">Gunakan akun demo dengan data Excel terisi</div>
                        </div>
                    </div>
                    <button type="button" onclick="fillDemoAccount()" class="px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-100/80 hover:bg-emerald-200 active:bg-emerald-300 rounded-lg transition-colors whitespace-nowrap">
                        Isi Akun Demo
                    </button>
                </div>

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                name="email" 
                                id="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus 
                                placeholder="nama@email.com"
                                class="w-full pl-10 pr-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition-all @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            >
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Kata Sandi
                            </label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                required 
                                placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 transition-all @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-500/20 @enderror"
                            >
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                id="remember" 
                                {{ old('remember') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            >
                            <span class="text-xs text-slate-600">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full mt-2 py-2.5 px-4 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-sm font-semibold rounded-xl shadow-md transition-all flex items-center justify-center gap-2"
                    >
                        <span>Masuk ke Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-slate-100 text-center text-xs text-slate-500">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-emerald-600 hover:text-emerald-700 ml-1">
                        Daftar akun baru
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function fillDemoAccount() {
        document.getElementById('email').value = 'demo@keuangan.id';
        document.getElementById('password').value = 'password123';
    }

    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
        }
    }
</script>
@endpush

