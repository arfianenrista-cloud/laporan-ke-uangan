@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil & Pengaturan')
@section('page-subtitle', 'Kelola informasi akun dan keamanan Anda')

@section('content')
<div class="max-w-2xl space-y-5">

    {{-- Profile Card --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 to-slate-700 px-6 py-8 text-white">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center text-2xl font-black ring-2 ring-white/20">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold">{{ Auth::user()->name }}</h2>
                    <p class="text-sm text-slate-300">{{ Auth::user()->email }}</p>
                    <p class="text-xs text-slate-400 mt-1">Bergabung sejak {{ Auth::user()->created_at->translatedFormat('d F Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Stats Strip --}}
        <div class="grid grid-cols-3 divide-x divide-slate-100 bg-slate-50">
            <div class="text-center py-4">
                <div class="text-xl font-bold text-slate-900">{{ $totalTransactions }}</div>
                <div class="text-xs text-slate-400">Total Transaksi</div>
            </div>
            <div class="text-center py-4">
                <div class="text-xl font-bold text-emerald-600">{{ $totalIncome }}</div>
                <div class="text-xs text-slate-400">Pemasukan</div>
            </div>
            <div class="text-center py-4">
                <div class="text-xl font-bold text-rose-600">{{ $totalExpense }}</div>
                <div class="text-xs text-slate-400">Pengeluaran</div>
            </div>
        </div>
    </div>

    {{-- Edit Profile Form --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">Informasi Akun</h3>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" class="px-6 py-5 space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" required
                    class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" required
                    class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-1">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">Ganti Password</h3>
        </div>
        <form method="POST" action="{{ route('profile.password') }}" class="px-6 py-5 space-y-4">
            @csrf @method('PATCH')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password Lama</label>
                <input type="password" name="current_password" required
                    class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                @error('current_password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password Baru</label>
                <input type="password" name="password" required minlength="8"
                    class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required minlength="8"
                    class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
            </div>

            <div class="flex justify-end pt-1">
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Update Password
                </button>
            </div>
        </form>
    </div>

    {{-- Danger Zone --}}
    <div class="bg-white rounded-2xl border border-rose-200 shadow-sm">
        <div class="px-6 py-4 border-b border-rose-100">
            <h3 class="text-sm font-semibold text-rose-700">Zona Berbahaya</h3>
        </div>
        <div class="px-6 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-slate-900">Keluar dari Aplikasi</div>
                    <div class="text-xs text-slate-400 mt-0.5">Anda akan diarahkan ke halaman login</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function openCreateModal() { /* stub */ }
</script>
@endpush

