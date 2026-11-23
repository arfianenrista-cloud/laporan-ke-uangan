@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil & Pengaturan')
@section('page-subtitle', 'Kelola informasi kredensial dan keamanan akun Anda')

@section('content')
<div class="max-w-2xl space-y-6">

    {{-- Profile Hero Card --}}
    <div class="lull-card overflow-hidden">
        <div class="bg-gradient-to-r from-[#141b2a] via-[#0f1422] to-[#0a0d15] px-7 py-8 border-b border-white/[0.08] relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-52 h-52 bg-[#f4ba88]/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex items-center gap-4 relative z-10">
                <div class="w-16 h-16 rounded-2xl bg-[#f4ba88]/15 border border-[#f4ba88]/30 text-[#f4ba88] flex items-center justify-center text-2xl font-black shadow-lg shadow-black/40">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white tracking-tight">{{ Auth::user()->name }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ Auth::user()->email }}</p>
                    <p class="text-[11px] text-slate-500 mt-1">Bergabung sejak {{ Auth::user()->created_at->translatedFormat('d F Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Stats Strip --}}
        <div class="grid grid-cols-3 divide-x divide-white/[0.06] bg-[#0c1018]/90">
            <div class="text-center py-4">
                <div class="text-lg font-bold text-white font-mono">{{ $totalTransactions }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Total Transaksi</div>
            </div>
            <div class="text-center py-4">
                <div class="text-lg font-bold text-emerald-400 font-mono">{{ $totalIncome }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Pemasukan</div>
            </div>
            <div class="text-center py-4">
                <div class="text-lg font-bold text-rose-400 font-mono">{{ $totalExpense }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Pengeluaran</div>
            </div>
        </div>
    </div>

    {{-- Edit Profile Form --}}
    <div class="lull-card p-6">
        <h3 class="text-sm font-bold text-white tracking-tight pb-3 border-b border-white/[0.08] mb-5">
            Informasi Akun
        </h3>
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm">
                @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm">
                @error('email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-lull-primary text-xs !py-2.5 !px-5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="lull-card p-6">
        <h3 class="text-sm font-bold text-white tracking-tight pb-3 border-b border-white/[0.08] mb-5">
            Ganti Password
        </h3>
        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Password Lama</label>
                <input type="password" name="current_password" required
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm">
                @error('current_password') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Password Baru</label>
                <input type="password" name="password" required minlength="8"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm">
                @error('password') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required minlength="8"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm">
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-lull-secondary text-xs !py-2.5 !px-5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Update Password</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Danger Zone --}}
    <div class="lull-card p-6 border-rose-500/20">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm font-bold text-white tracking-tight">Keluar Sesi</div>
                <div class="text-xs text-slate-400 mt-0.5">Keluar dari akun Anda di perangkat ini</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-rose-400 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/25 rounded-full transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function openCreateModal() { /* stub */ }
</script>
@endpush
