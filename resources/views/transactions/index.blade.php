@extends('layouts.app')

@section('title', 'Transaksi')
@section('page-title', 'Daftar Transaksi')
@section('page-subtitle', 'Semua catatan pemasukan & pengeluaran Anda')

@section('content')
<div class="space-y-5">

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap gap-3 items-end">
            {{-- Search --}}
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi, kategori..."
                        class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 bg-slate-50">
                </div>
            </div>

            {{-- Type --}}
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Jenis</label>
                <select name="type" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                    <option value="">Semua</option>
                    <option value="Pemasukan" {{ request('type') === 'Pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="Pengeluaran" {{ request('type') === 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>

            {{-- Category --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Kategori</label>
                <select name="category" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                    <option value="">Semua Kategori</option>
                    @foreach(array_merge(array_keys($expenseCategories), array_keys($incomeCategories)) as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Month --}}
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Bulan</label>
                <input type="month" name="month" value="{{ request('month') }}"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filter
                </button>
                @if(request()->hasAny(['search','type','category','month']))
                <a href="{{ route('transactions.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Summary Strip --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
            <div class="text-xs text-emerald-700 font-medium mb-1">Total Pemasukan</div>
            <div class="text-lg font-bold text-emerald-800 font-mono">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
            <div class="text-[11px] text-emerald-600 mt-0.5">{{ $transactions->where('type','Pemasukan')->count() }} transaksi</div>
        </div>
        <div class="bg-rose-50 border border-rose-200 rounded-xl p-4">
            <div class="text-xs text-rose-700 font-medium mb-1">Total Pengeluaran</div>
            <div class="text-lg font-bold text-rose-800 font-mono">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
            <div class="text-[11px] text-rose-600 mt-0.5">{{ $transactions->where('type','Pengeluaran')->count() }} transaksi</div>
        </div>
        <div class="{{ $netBalance >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-amber-50 border-amber-200' }} border rounded-xl p-4">
            <div class="text-xs {{ $netBalance >= 0 ? 'text-blue-700' : 'text-amber-700' }} font-medium mb-1">Saldo Bersih</div>
            <div class="text-lg font-bold {{ $netBalance >= 0 ? 'text-blue-800' : 'text-amber-800' }} font-mono">
                {{ $netBalance < 0 ? '-' : '' }}Rp {{ number_format(abs($netBalance), 0, ',', '.') }}
            </div>
            <div class="text-[11px] {{ $netBalance >= 0 ? 'text-blue-600' : 'text-amber-600' }} mt-0.5">{{ $transactions->count() }} total transaksi</div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Daftar Transaksi</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $transactions->count() }} transaksi ditemukan</p>
            </div>
            <a href="{{ route('transactions.export', request()->only(['month','search','type','category'])) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>
        </div>

        @if($transactions->isEmpty())
        <div class="text-center py-16">
            <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-sm font-medium text-slate-400">Belum ada transaksi</p>
            <p class="text-xs text-slate-300 mt-1">Tambah transaksi pertama dengan tombol <strong>Catat Transaksi</strong> di atas</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Deskripsi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden md:table-cell">Kategori</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden lg:table-cell">Metode</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nominal</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50" id="transactionsTableBody">
                    @foreach($transactions as $trx)
                    <tr class="hover:bg-slate-50/50 transition-colors group"
                        data-id="{{ $trx->id }}"
                        data-date="{{ $trx->date->format('Y-m-d') }}"
                        data-type="{{ $trx->type }}"
                        data-category="{{ $trx->category }}"
                        data-description="{{ $trx->description }}"
                        data-amount="{{ (int)$trx->amount }}"
                        data-payment="{{ $trx->payment_method }}"
                        data-notes="{{ $trx->notes }}">
                        <td class="px-5 py-3.5">
                            <div class="text-xs font-medium text-slate-900">{{ $trx->date->translatedFormat('d M Y') }}</div>
                            <div class="text-[11px] text-slate-400">{{ $trx->date->translatedFormat('l') }}</div>
                        </td>
                        <td class="px-4 py-3.5 max-w-xs">
                            <div class="font-medium text-slate-900 truncate">{{ $trx->description ?: '—' }}</div>
                            @if($trx->notes)
                            <div class="text-[11px] text-slate-400 truncate">{{ $trx->notes }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 hidden md:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border {{ ($trx->type === 'Pengeluaran' ? ($expenseCategories[$trx->category]['bg'] ?? 'bg-slate-50 text-slate-700 border-slate-200') : ($incomeCategories[$trx->category]['bg'] ?? 'bg-emerald-50 text-emerald-700 border-emerald-200')) }}">
                                {{ $trx->category }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 hidden lg:table-cell">
                            <span class="text-xs text-slate-500">{{ $trx->payment_method }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-right">
                            <div class="text-sm font-bold font-mono {{ $trx->type === 'Pemasukan' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $trx->type === 'Pemasukan' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] {{ $trx->type === 'Pemasukan' ? 'text-emerald-500' : 'text-rose-500' }}">{{ $trx->type }}</div>
                        </td>
                        <td class="px-4 py-3.5 text-center no-print">
                            <div class="flex items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button"
                                    class="edit-btn p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                    title="Edit transaksi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button type="button"
                                    class="delete-btn p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                                    title="Hapus transaksi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
{{-- Add / Edit Modal --}}
<div id="trxModal" class="hidden fixed inset-0 z-50 pointer-events-none">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm pointer-events-auto" onclick="closeModal('trxModal')"></div>
    <div class="relative z-20 pointer-events-auto flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900" id="modalTitle">Tambah Transaksi</h3>
                <button onclick="closeModal('trxModal')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="trxForm" method="POST" class="px-6 py-5 space-y-4">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                {{-- Type Toggle --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">Jenis Transaksi</label>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl">
                        <label class="flex items-center justify-center gap-2 py-2 rounded-lg cursor-pointer transition-all text-sm font-semibold has-[:checked]:bg-white has-[:checked]:shadow has-[:checked]:text-emerald-700 text-slate-500">
                            <input type="radio" name="type" value="Pemasukan" class="sr-only" onchange="updateCategoryOptions(this.value)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Pemasukan
                        </label>
                        <label class="flex items-center justify-center gap-2 py-2 rounded-lg cursor-pointer transition-all text-sm font-semibold has-[:checked]:bg-white has-[:checked]:shadow has-[:checked]:text-rose-700 text-slate-500">
                            <input type="radio" name="type" value="Pengeluaran" class="sr-only" onchange="updateCategoryOptions(this.value)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            Pengeluaran
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="date" id="trxDate" required
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nominal (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" id="trxAmount" required min="1" placeholder="0"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 bg-slate-50 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori <span class="text-rose-500">*</span></label>
                    <select name="category" id="trxCategory" required
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 bg-slate-50">
                        <option value="">Pilih kategori...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Deskripsi / Keperluan</label>
                    <input type="text" name="description" id="trxDescription" placeholder="Contoh: Makan siang, Bayar listrik..."
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 bg-slate-50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Metode Pembayaran</label>
                    <select name="payment_method" id="trxPayment"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 bg-slate-50">
                        @foreach($paymentMethods as $pm)
                        <option value="{{ $pm }}">{{ $pm }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('trxModal')" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                    <button type="submit" id="trxSubmitBtn" class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirm Modal --}}
<div id="deleteModal" class="hidden fixed inset-0 z-50 pointer-events-none">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm pointer-events-auto" onclick="closeModal('deleteModal')"></div>
    <div class="relative z-20 pointer-events-auto flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" onclick="event.stopPropagation()">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Hapus Transaksi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-3 mt-5">
                    <button type="button" onclick="closeModal('deleteModal')" class="flex-1 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors">Ya, Hapus</button>
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

    // Default to Pengeluaran
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

// Event delegation for edit/delete buttons
document.getElementById('transactionsTableBody')?.addEventListener('click', function(e) {
    const editBtn = e.target.closest('.edit-btn');
    const deleteBtn = e.target.closest('.delete-btn');
    const row = e.target.closest('tr[data-id]');
    if (!row) return;

    if (editBtn) {
        const d = row.dataset;
        document.getElementById('modalTitle').textContent = 'Edit Transaksi';
        document.getElementById('trxSubmitBtn').textContent = 'Update';
        document.getElementById('trxForm').action = `${baseUpdateUrl}/${d.id}`;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('trxDate').value = d.date;
        document.getElementById('trxAmount').value = d.amount;
        document.getElementById('trxDescription').value = d.description;

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

