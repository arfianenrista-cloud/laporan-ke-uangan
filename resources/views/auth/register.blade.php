@extends('layouts.app')

@section('title', 'Daftar Akun Baru')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-6 sm:py-12">
    <div class="w-full max-w-md lull-card p-8 sm:p-10 shadow-2xl border border-white/10">
        
        <div class="text-center">
            <div class="w-12 h-12 rounded-2xl bg-[#f4ba88]/15 text-[#f4ba88] border border-[#f4ba88]/25 mx-auto flex items-center justify-center mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Buat Akun KeuanganKu</h2>
            <p class="mt-1 text-xs sm:text-sm text-slate-400">
                Kelola alokasi pengeluaran dan tabungan bulanan secara rapi.
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Nama Lengkap
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}" 
                    required 
                    autofocus 
                    placeholder="Contoh: Rifki Pratama"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm"
                >
                @error('name')
                    <p class="mt-1 text-xs text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Alamat Email
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    value="{{ old('email') }}" 
                    required 
                    placeholder="nama@email.com"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm"
                >
                @error('email')
                    <p class="mt-1 text-xs text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Kata Sandi
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    required 
                    placeholder="Minimal 6 karakter"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm"
                >
                @error('password')
                    <p class="mt-1 text-xs text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Konfirmasi Kata Sandi
                </label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation" 
                    required 
                    placeholder="Ketik ulang kata sandi"
                    class="w-full px-3.5 py-2.5 text-xs sm:text-sm"
                >
            </div>

            <!-- Submit -->
            <button 
                type="submit" 
                class="btn-lull-primary w-full mt-4 py-3 text-sm"
            >
                <span>Daftar Akun Baru</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-white/[0.08] text-center text-xs text-slate-400">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-semibold text-[#f4ba88] hover:underline ml-1">
                Masuk ke akun Anda
            </a>
        </div>
    </div>
</div>
@endsection
