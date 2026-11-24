~@extends('layouts.app')

@section('title', 'Transaksi')
@section('page-title', 'Daftar Transaksi')
@section('page-subtitle', 'Semua catatan pemasukan & pengeluaran Anda')

@section('content')
<div class="space-y-6">

    {{-- Filter Bar --}}
    <div class="lull-card p-4 sm:p-5">
        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap gap-3 items-end">
            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Cari Transaksi</label>
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi, kategori..."
                        class="w-full pl-10 pr-3.5 py-2 text-xs">
                </div>
            </div>

            {{-- Type --}}
            <div class="min-w-[140px]">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Jenis</label>
                <select name="type" class="w-full px-3 py-2 text-xs">
                    <option value="">Semua</option>
                    <option value="Pemasukan" {{ request('type') === 'Pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="Pengeluaran" {{ request('type') === 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>

            {{-- Category --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Kategori</label>
                <select name="category" class="w-full px-3 py-2 text-xs">
                    <option value="">Semua Kategori</option>
                    @foreach(array_merge(array_keys($expenseCategories), array_keys($incomeCategories)) as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Month --}}
            <div class="min-w-[150px]">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Bulan</label>
                <input type="month" name="month" value="{{ request('month') }}"
                    class="w-full px-3 py-2 text-xs">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-lull-primary text-xs !py-2 !px-4">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>
                @if(request()->hasAny(['search','type','category','month']))
                <a href="{{ route('transactions.index') }}" class="btn-lull-secondary text-xs !py-2 !px-3.5">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Summary Strip (3 Dark Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="lull-card p-4 sm:p-5 border-l-4 border-l-emerald-500">
            <div class="text-xs text-slate-400 font-medium mb-1 flex items-center justify-between">
                <span>Total Pemasukan</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-xl font-bold text-white font-mono">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $transactions->where('type','Pemasukan')->count() }} transaksi</div>
        </div>

        <div class="lull-card p-4 sm:p-5 border-l-4 border-l-rose-500">
            <div class="text-xs text-slate-400 font-medium mb-1 flex items-center justify-between">
                <span>Total Pengeluaran</span>
                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
            </div>
            <div class="text-xl font-bold text-white font-mono">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $transactions->where('type','Pengeluaran')->count() }} transaksi</div>
        </div>

        <div class="lull-card p-4 sm:p-5 border-l-4 {{ $netBalance >= 0 ? 'border-l-[#f4ba88]' : 'border-l-rose-500' }}">
            <div class="text-xs text-slate-400 font-medium mb-1 flex items-center justify-between">
                <span>Saldo Bersih</span>
                <span class="w-2 h-2 rounded-full {{ $netBalance >= 0 ? 'bg-[#f4ba88]' : 'bg-rose-400' }}"></span>
            </div>
            <div class="text-xl font-bold {{ $netBalance >= 0 ? 'text-[#f4ba88]' : 'text-rose-400' }} font-mono">
                {{ $netBalance < 0 ? '-' : '' }}Rp {{ number_format(abs($netBalance), 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $transactions->count() }} total transaksi</div>
        </div>
    </div>

    {{-- Transactions Container (Lull Glass Card) --}}
    <div class="lull-card overflow-hidden">
        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-white/[0.08]">
            <div>
                <h2 class="text-sm font-bold text-white tracking-tight">Catatan Transaksi</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $transactions->count() }} transaksi ditemukan</p>
            </div>
            <a href="{{ route('transactions.export', request()->only(['month','search','type','category'])) }}"
               class="btn-lull-secondary text-xs !py-1.5 !px-3.5">
                <svg class="w-3.5 h-3.5 text-[#f4ba88]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Unduh Excel</span>
            </a>
        </div>

        @if($transactions->isEmpty())
        <div class="text-center py-16 px-4">
            <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-sm font-medium text-slate-400">Belum ada transaksi</p>
            <p class="text-xs text-slate-500 mt-1">Gunakan tombol <strong>Catat Transaksi</strong> untuk mencatat pemasukan atau pengeluaran.</p>
        </div>
        @else
        {{-- MOBILE VIEW: Interactive Native App Card List (sm:hidden) --}}
        <div class="sm:hidden divide-y divide-white/[0.06]" id="transactionsMobileList">
            @foreach($transactions as $trx)
            <div class="p-4 hover:bg-white/[0.02] transition-colors"
                data-id="{{ $trx->id }}"
                data-date="{{ $trx->date->format('Y-m-d') }}"
                data-type="{{ $trx->type }}"
                data-category="{{ $trx->category }}"
                data-description="{{ $trx->description }}"
                data-amount="{{ (int)$trx->amount }}"
                data-payment="{{ $trx->payment_method }}"
                data-notes="{{ $trx->notes }}">
                
                {{-- Top: Date & Category Badge --}}
                <div class="flex items-center justify-between text-xs mb-2">
                    <span class="text-slate-400 font-medium">
                        {{ $trx->date->translatedFormat('d M Y') }} &bull; {{ $trx->date->translatedFormat('l') }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $trx->type === 'Pengeluaran' ? 'bg-rose-500/10 text-rose-300 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20' }}">
                        {{ $trx->category }}
                    </span>
                </div>

                {{-- Middle: Description & Notes --}}
                <div class="mb-2.5">
                    <div class="font-semibold text-white text-sm">
                        {{ $trx->description ?: $trx->category }}
                    </div>
                    @if($trx->notes)
                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $trx->notes }}</div>
                    @endif
                </div>

                {{-- Bottom: Nominal & Actions --}}
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
                        <button type="button"
                            class="edit-btn p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 active:bg-white/20 transition-colors"
                            title="Edit transaksi" aria-label="Edit">
                            <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button type="button"
                            class="delete-btn p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 active:bg-rose-500/20 transition-colors"
                            title="Hapus transaksi" aria-label="Hapus">
                            <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- DESKTOP VIEW: Data Table (hidden on mobile, visible on sm and up) --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-[#0c1018] border-b border-white/[0.08] text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-4 py-3">Deskripsi</th>
                        <th class="px-4 py-3 hidden md:table-cell">Kategori</th>
                        <th class="px-4 py-3 hidden lg:table-cell">Metode</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3 text-center no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.05]" id="transactionsTableBody">
                    @foreach($transactions as $trx)
                    <tr class="hover:bg-white/[0.02] transition-colors group"
                        data-id="{{ $trx->id }}"
                        data-date="{{ $trx->date->format('Y-m-d') }}"
                        data-type="{{ $trx->type }}"
                        data-category="{{ $trx->category }}"
                        data-description="{{ $trx->description }}"
                        data-amount="{{ (int)$trx->amount }}"
                        data-payment="{{ $trx->payment_method }}"
                        data-notes="{{ $trx->notes }}">
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-white">{{ $trx->date->translatedFormat('d M Y') }}</div>
                            <div class="text-[10px] text-slate-500">{{ $trx->date->translatedFormat('l') }}</div>
                        </td>
                        <td class="px-4 py-3.5 max-w-xs">
                            <div class="font-medium text-white truncate">{{ $trx->description ?: '—' }}</div>
                            @if($trx->notes)
                            <div class="text-[10px] text-slate-400 truncate">{{ $trx->notes }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 hidden md:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $trx->type === 'Pengeluaran' ? 'bg-rose-500/10 text-rose-300 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-300 border-emerald-500/20' }}">
                                {{ $trx->category }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            <span class="text-slate-400">{{ $trx->payment_method }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="text-sm font-bold font-mono {{ $trx->type === 'Pemasukan' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $trx->type === 'Pemasukan' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </div>
                            <div class="text-[10px] text-slate-500">{{ $trx->type }}</div>
                        </td>
                        <td class="px-4 py-3.5 text-center no-print">
                            <div class="flex items-center justify-center gap-1.5 opacity-90 group-hover:opacity-100 transition-opacity">
                                <button type="button"
                                    class="edit-btn p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
                                    title="Edit transaksi">
                                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button type="button"
                                    class="delete-btn p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors"
                                    title="Hapus transaksi">
                                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection

@push('modals')
{{-- Add / Edit Modal (Lull Dark Glass Mobile Bottom Sheet & Desktop Dialog) --}}
<div id="trxModal" class="hidden fixed inset-0 z-50 pointer-events-none">
    <div class="fixed inset-0 bg-black/80 backdrop-blur-md pointer-events-auto" onclick="closeModal('trxModal')"></div>
    <div class="relative z-20 pointer-events-auto flex items-end sm:items-center justify-center min-h-screen p-0 sm:p-4">
        <div class="lull-card w-full sm:max-w-lg p-5 sm:p-6 shadow-2xl border-t sm:border border-white/15 rounded-t-3xl sm:rounded-2xl max-h-[92vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="w-12 h-1 bg-white/20 rounded-full mx-auto mb-4 sm:hidden"></div>
            <div class="flex items-center justify-between pb-4 border-b border-white/[0.08] mb-5">
                <h3 class="text-base font-bold text-white tracking-tight" id="modalTitle">Tambah Transaksi</h3>
                <button onclick="closeModal('trxModal')" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="trxForm" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                {{-- Type Toggle --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Jenis Transaksi</label>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-[#07090e] border border-white/[0.08] rounded-xl">
                        <label class="flex items-center justify-center gap-2 py-2 rounded-lg cursor-pointer transition-all text-xs font-semibold has-[:checked]:bg-[#f4ba88] has-[:checked]:text-[#181109] has-[:checked]:shadow text-slate-400">
                            <input type="radio" name="type" value="Pemasukan" class="sr-only" onchange="updateCategoryOptions(this.value)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Pemasukan (+)
                        </label>
                        <label class="flex items-center justify-center gap-2 py-2 rounded-lg cursor-pointer transition-all text-xs font-semibold has-[:checked]:bg-rose-500 has-[:checked]:text-white has-[:checked]:shadow text-slate-400">
                            <input type="radio" name="type" value="Pengeluaran" class="sr-only" onchange="updateCategoryOptions(this.value)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            Pengeluaran (-)
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Tanggal <span class="text-rose-400">*</span></label>
                        <input type="date" name="date" id="trxDate" required
                            class="w-full px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Nominal (Rp) <span class="text-rose-400">*</span></label>
                        <input type="number" name="amount" id="trxAmount" required min="1" placeholder="0"
                            class="w-full px-3 py-2 text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Kategori <span class="text-rose-400">*</span></label>
                    <select name="category" id="trxCategory" required
                        class="w-full px-3 py-2 text-xs">
                        <option value="">Pilih kategori...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Deskripsi / Keperluan</label>
                    <input type="text" name="description" id="trxDescription" placeholder="Contoh: Makan siang, bayar wifi..."
                        class="w-full px-3 py-2 text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Metode Pembayaran</label>
                    <select name="payment_method" id="trxPayment"
                        class="w-full px-3 py-2 text-xs">
                        @foreach($paymentMethods as $pm)
                        <option value="{{ $pm }}">{{ $pm }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2.5 pt-3">
                    <button type="button" onclick="closeModal('trxModal')" class="btn-lull-secondary text-xs">Batal</button>
                    <button type="submit" id="trxSubmitBtn" class="btn-lull-primary text-xs">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div id="deleteModal" class="hidden fixed inset-0 z-50 pointer-events-none">
    <div class="fixed inset-0 bg-black/80 backdrop-blur-md pointer-events-auto" onclick="closeModal('deleteModal')"></div>
    <div class="relative z-20 pointer-events-auto flex items-end sm:items-center justify-center min-h-screen p-0 sm:p-4">
        <div class="lull-card w-full sm:max-w-sm p-6 shadow-2xl border-t sm:border border-white/15 rounded-t-3xl sm:rounded-2xl" onclick="event.stopPropagation()">
            <div class="w-12 h-1 bg-white/20 rounded-full mx-auto mb-4 sm:hidden"></div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight">Hapus Transaksi</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-2.5 mt-5">
                    <button type="button" onclick="closeModal('deleteModal')" class="btn-lull-secondary flex-1 text-xs justify-center">Batal</button>
                    <button type="submit" class="flex-1 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-full transition-colors justify-center flex items-center shadow-md">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
const expenseCategories = @json(array_keys($expenseCategories));
const incomeCategories = @json(array_keys($incomeCategories));
const baseStoreUrl = "{{ route('transactions.store') }}";
const baseUpdateUrl = "{{ url('/transactions') }}";
const baseDeleteUrl = "{{ url('/transactions') }}";

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Transaksi';
    document.getElementById('trxSubmitBtn').textContent = 'Simpan';
    document.getElementById('trxForm').action = baseStoreUrl;
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('trxDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('trxAmount').value = '';
    document.getElementById('trxDescription').value = '';

    document.querySelectorAll('input[name="type"]').forEach(r => { r.checked = r.value === 'Pengeluaran'; });
    updateCategoryOptions('Pengeluaran');

    openModal('trxModal');
}

function updateCategoryOptions(type) {
    const sel = document.getElementById('trxCategory');
    const cats = type === 'Pemasukan' ? incomeCategories : expenseCategories;
    sel.innerHTML = '<option value="">Pilih kategori...</option>' + cats.map(c => `<option value="${c}">${c}</option>`).join('');
}

function openModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    el.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const el = document.getElementById(id);
    el.classList.add('hidden');
    el.classList.remove('flex');
    document.body.style.overflow = '';
}

// Event delegation for both Desktop Table and Mobile Card list
document.addEventListener('click', function(e) {
    const editBtn = e.target.closest('.edit-btn');
    const deleteBtn = e.target.closest('.delete-btn');
    if (!editBtn && !deleteBtn) return;

    const row = e.target.closest('[data-id]');
    if (!row) return;

    if (editBtn) {
        const d = row.dataset;
        document.getElementById('modalTitle').textContent = 'Edit Transaksi';
        document.getElementById('trxSubmitBtn').textContent = 'Update';
        document.getElementById('trxForm').action = `${baseUpdateUrl}/${d.id}`;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('trxDate').value = d.date;
        document.getElementById('trxAmount').value = d.amount;
        document.getElementById('trxDescription').value = d.description || '';

        document.querySelectorAll('input[name="type"]').forEach(r => { r.checked = r.value === d.type; });
        updateCategoryOptions(d.type);
        document.getElementById('trxCategory').value = d.category;
        document.getElementById('trxPayment').value = d.payment;
        openModal('trxModal');
    }

    if (deleteBtn) {
        document.getElementById('deleteForm').action = `${baseDeleteUrl}/${row.dataset.id}`;
        openModal('deleteModal');
    }
});
</script>
@endpush
