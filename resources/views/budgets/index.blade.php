@extends('layouts.app')

@section('title', 'Anggaran Bulanan')
@section('page-title', 'Anggaran Bulanan')
@section('page-subtitle', 'Set batas pengeluaran per kategori dan pantau realisasinya')

@section('content')
<div class="space-y-5">

    {{-- Period + Add Budget Bar --}}
    <div class="flex flex-wrap items-center gap-3">
        <form method="GET" action="{{ route('budgets.index') }}" class="flex gap-2 items-center">
            <label class="text-xs font-medium text-slate-600">Periode:</label>
            <input type="month" name="month" value="{{ $selectedMonth }}"
                class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
            <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors">Tampilkan</button>
        </form>
        <div class="ml-auto">
            <button onclick="openBudgetModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Set Anggaran
            </button>
        </div>
    </div>

    {{-- Overall Budget Summary --}}
    @if($totalBudget > 0)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Total Anggaran vs Realisasi</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->translatedFormat('F Y') }}</p>
            </div>
            <div class="text-right">
                <div class="text-xs text-slate-500">Terpakai</div>
                <div class="text-lg font-bold {{ $totalUsed > $totalBudget ? 'text-rose-600' : 'text-slate-900' }} font-mono">
                    {{ $budgetUsedPercent }}%
                </div>
            </div>
        </div>
        <div class="h-3 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full progress-bar {{ $totalUsed > $totalBudget ? 'bg-rose-500' : ($budgetUsedPercent >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                style="width: {{ min($budgetUsedPercent, 100) }}%"></div>
        </div>
        <div class="flex justify-between mt-2 text-xs text-slate-400">
            <span>Rp {{ number_format($totalUsed, 0, ',', '.') }} terpakai</span>
            <span>Anggaran: Rp {{ number_format($totalBudget, 0, ',', '.') }}</span>
        </div>
    </div>
    @endif

    {{-- Budget Cards Grid --}}
    @if($budgetItems->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm text-center py-16">
        <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <p class="text-sm font-medium text-slate-400">Belum ada anggaran</p>
        <p class="text-xs text-slate-300 mt-1">Klik <strong>Set Anggaran</strong> untuk menambahkan batas pengeluaran per kategori</p>
        <button onclick="openBudgetModal()" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Anggaran Pertama
        </button>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($budgetItems as $item)
        @php
            $pct = $item['budget'] > 0 ? min(round(($item['used'] / $item['budget']) * 100, 1), 100) : 0;
            $overBudget = $item['used'] > $item['budget'];
            $nearBudget = !$overBudget && $pct >= 80;
            $barColor = $overBudget ? 'bg-rose-500' : ($nearBudget ? 'bg-amber-500' : 'bg-emerald-500');
            $borderColor = $overBudget ? 'border-rose-200' : ($nearBudget ? 'border-amber-200' : 'border-slate-200');
            $remaining = $item['budget'] - $item['used'];
        @endphp
        <div class="bg-white rounded-2xl border {{ $borderColor }} shadow-sm p-5 group">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: {{ $item['color'] }}20">
                        <div class="w-3.5 h-3.5 rounded-full" style="background: {{ $item['color'] }}"></div>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-900">{{ $item['name'] }}</div>
                        <div class="text-[11px] text-slate-400">{{ $item['count'] }} transaksi</div>
                    </div>
                </div>
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="openEditBudget({{ $item['budget_id'] ?? 'null' }}, '{{ $item['name'] }}', {{ $item['budget'] }})"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    @if($item['budget_id'] ?? false)
                    <form method="POST" action="{{ route('budgets.destroy', $item['budget_id']) }}" onsubmit="return confirm('Hapus anggaran ini?')">
                        @csrf @method('DELETE')
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            @if($overBudget)
            <div class="flex items-center gap-1.5 mb-2 text-xs font-semibold text-rose-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Melebihi anggaran!
            </div>
            @elseif($nearBudget)
            <div class="flex items-center gap-1.5 mb-2 text-xs font-semibold text-amber-600">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Hampir mencapai batas
            </div>
            @endif

            <div class="flex justify-between text-xs font-mono mb-1.5">
                <span class="{{ $overBudget ? 'text-rose-600 font-bold' : 'text-slate-700' }}">Rp {{ number_format($item['used'], 0, ',', '.') }}</span>
                <span class="text-slate-400">/ Rp {{ number_format($item['budget'], 0, ',', '.') }}</span>
            </div>
            <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full progress-bar {{ $barColor }}" style="width: {{ min($pct, 100) }}%"></div>
            </div>
            <div class="flex justify-between mt-1.5 text-[11px] text-slate-400">
                <span>{{ $pct }}% terpakai</span>
                <span>{{ $remaining >= 0 ? 'Sisa: Rp ' . number_format($remaining, 0, ',', '.') : 'Lebih: Rp ' . number_format(abs($remaining), 0, ',', '.') }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection

@push('modals')
<div id="budgetModal" class="hidden fixed inset-0 z-50 pointer-events-none">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm pointer-events-auto" onclick="closeModal('budgetModal')"></div>
    <div class="relative z-20 pointer-events-auto flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900" id="budgetModalTitle">Set Anggaran</h3>
                <button onclick="closeModal('budgetModal')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="budgetForm" method="POST" action="{{ route('budgets.store') }}" class="px-6 py-5 space-y-4">
                @csrf
                <input type="hidden" name="_method" id="budgetFormMethod" value="POST">
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <input type="hidden" name="budget_id" id="budgetId" value="">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori <span class="text-rose-500">*</span></label>
                    <select name="category" id="budgetCategory" required
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                        <option value="">Pilih kategori...</option>
                        @foreach(array_keys($expenseCategories) as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Batas Anggaran (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" id="budgetAmount" required min="1" placeholder="Contoh: 500000"
                        class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 font-mono">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('budgetModal')" class="flex-1 py-2 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
function openBudgetModal() {
    document.getElementById('budgetModalTitle').textContent = 'Set Anggaran';
    document.getElementById('budgetFormMethod').value = 'POST';
    document.getElementById('budgetId').value = '';
    document.getElementById('budgetCategory').value = '';
    document.getElementById('budgetAmount').value = '';
    document.getElementById('budgetCategory').disabled = false;
    openModal('budgetModal');
}

function openEditBudget(id, category, amount) {
    document.getElementById('budgetModalTitle').textContent = 'Edit Anggaran';
    document.getElementById('budgetFormMethod').value = id ? 'PUT' : 'POST';
    document.getElementById('budgetId').value = id || '';
    document.getElementById('budgetCategory').value = category;
    document.getElementById('budgetCategory').disabled = true;
    document.getElementById('budgetAmount').value = amount;
    if (id) {
        document.getElementById('budgetForm').action = `/budgets/${id}`;
    }
    openModal('budgetModal');
}

function openModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.body.style.overflow = '';
}
function openCreateModal() { /* stub for topbar button */ }
</script>
@endpush

