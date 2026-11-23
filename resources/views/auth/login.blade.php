@extends('layouts.app')

@section('title', 'Masuk ke Akun Anda')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-6 sm:py-12">
    <div class="w-full max-w-4xl grid grid-cols-1 lg:grid-cols-12 lull-card overflow-hidden shadow-2xl border border-white/10">
        
        <!-- Left Brand & Features Panel (Lull Dark Ribbon Theme) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#121724] via-[#090d15] to-[#07090e] p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden border-b lg:border-b-0 lg:border-r border-white/[0.08]">
            <!-- Subtle background ambient light -->
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-[#f4ba88]/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.06] border border-white/10 text-[#f4ba88] text-xs font-semibold mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#f4ba88] animate-pulse"></span>
                    Sistem Pembukuan Finansial
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                    Kelola uang.<br>
                    <span class="text-slate-400">Dengan tenang &amp; presisi.</span>
                </h2>
                <p class="mt-4 text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Catat pengeluaran harian, susun anggaran bulanan, dan pantau saldo tabungan secara otomatis dalam satu tampilan minimalis.
                </p>

                <!-- Value Highlights -->
                <div class="mt-8 space-y-3">
                    <div class="flex items-center gap-3 text-xs text-slate-300">
                        <div class="w-6 h-6 rounded-lg bg-[#f4ba88]/15 text-[#f4ba88] flex items-center justify-center flex-shrink-0 border border-[#f4ba88]/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span>Arus Kas Real-Time &amp; Saldo Bersih</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-300">
                        <div class="w-6 h-6 rounded-lg bg-[#f4ba88]/15 text-[#f4ba88] flex items-center justify-center flex-shrink-0 border border-[#f4ba88]/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span>Anggaran Harian, Mingguan, Bulanan &amp; Tahunan</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-slate-300">
                        <div class="w-6 h-6 rounded-lg bg-[#f4ba88]/15 text-[#f4ba88] flex items-center justify-center flex-shrink-0 border border-[#f4ba88]/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span>Ekspor Laporan Resmi Excel (.xlsx)</span>
                    </div>
                </div>
            </div>

            <!-- Mini Live Stat Preview -->
            <div class="relative z-10 mt-8 pt-6 border-t border-white/[0.08]">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Kredensial Demo Tersedia</span>
                    <span class="font-mono font-semibold text-[#f4ba88]">1-Klik Siap Pakai</span>
                </div>
            </div>
        </div>

        <!-- Right Form Panel (Dark Glass) -->
        <div class="lg:col-span-7 p-8 sm:p-10 flex flex-col justify-center bg-[#0d121c]/95">
            <div class="max-w-md w-full mx-auto">
                <div>
                    <h3 class="text-2xl font-bold text-white tracking-tight">Selamat Datang Kembali</h3>
                    <p class="mt-1 text-xs sm:text-sm text-slate-400">
                        Masuk untuk membuka dashboard keuangan Anda.
                    </p>
                </div>

                <!-- 1-Click Demo Account Quick Fill Button -->
                <div class="mt-5 p-3.5 bg-white/[0.04] border border-white/[0.08] rounded-2xl flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-[#f4ba88]/15 text-[#f4ba88] flex items-center justify-center flex-shrink-0 border border-[#f4ba88]/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-white">Uji Coba Cepat?</div>
                            <div class="text-[11px] text-slate-400">Pakai akun demo dengan data terisi</div>
                        </div>
                    </div>
                    <button type="button" onclick="fillDemoAccount()" class="btn-lull-secondary !py-1.5 !px-3 text-xs !bg-[#f4ba88]/15 !text-[#f4ba88] !border-[#f4ba88]/30 hover:!bg-[#f4ba88]/25">
                        Isi Akun Demo
                    </button>
                </div>

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                    @csrf

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
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
                                class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm"
                            >
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                                Kata Sandi
                            </label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
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
                                class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm"
                            >
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-white">
                                <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
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
                                class="w-4 h-4 rounded border-white/20 bg-[#07090e] text-[#f4ba88] focus:ring-[#f4ba88]"
                            >
                            <span class="text-xs text-slate-300">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Submit Button: Signature Lull Peach Pill -->
                    <button 
                        type="submit" 
                        class="btn-lull-primary w-full mt-2 py-3 text-sm shadow-xl"
                    >
                        <span>Masuk ke Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-white/[0.08] text-center text-xs text-slate-400">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-[#f4ba88] hover:underline ml-1">
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
