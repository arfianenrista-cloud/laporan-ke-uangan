@extends('layouts.app')

@section('title', 'Anggaran')
@section('page-title', 'Anggaran Finansial')
@section('page-subtitle', 'Pantau alokasi dan batas pengeluaran dengan presisi')

@section('content')
<div class="space-y-6">

    {{-- ─── HERO HEADER ────────────────────────────────────────────────── --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.05] border border-white/10 text-[#f4ba88] text-xs font-semibold mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-[#f4ba88] animate-pulse"></span>
                <span>Alokasi &amp; Kontrol Pengeluaran</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Batas Pengeluaran.
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Atur target batas belanja untuk menjaga arus kas tetap sehat dan terukur.
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2.5">
            <button onclick="openBudgetModal()"
                class="btn-lull-primary text-xs sm:text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Set Anggaran {{ match($activeTab){ 'daily'=>'Harian','weekly'=>'Mingguan','yearly'=>'Tahunan',default=>'Bulanan' } }}</span>
            </button>
        </div>
    </div>

    {{-- ─── 4-TAB SWITCHER & PICKER TOOLBAR ────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 p-2.5 bg-[#0c1018]/90 border border-white/[0.08] rounded-2xl backdrop-blur">
        
        {{-- Pill Tabs (horizontally scrollable on mobile) --}}
        <div class="inline-flex bg-white/[0.04] p-1 rounded-xl gap-1 border border-white/5 overflow-x-auto max-w-full">
            @foreach([
                ['daily',   'Harian',   'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['weekly',  'Mingguan', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['monthly', 'Bulanan',  'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['yearly',  'Tahunan',  'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z'],
            ] as [$tab, $label, $icon])
            @php
                $params = match($tab) {
                    'daily'   => ['tab' => 'daily',   'date'  => $selectedDate],
                    'weekly'  => ['tab' => 'weekly',  'week'  => $selectedWeek],
                    'monthly' => ['tab' => 'monthly', 'month' => $selectedMonth],
                    'yearly'  => ['tab' => 'yearly',  'year'  => $selectedYear],
                };
            @endphp
            <a href="{{ route('budgets.index', $params) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all whitespace-nowrap
                      {{ $activeTab === $tab ? 'bg-[#f4ba88] text-[#181109] shadow-md shadow-amber-950/20' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Period pickers form --}}
        <form method="GET" action="{{ route('budgets.index') }}" class="flex items-center gap-2 flex-wrap">
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            @if($activeTab === 'daily')
                <input type="date" name="date" value="{{ $selectedDate }}"
                    class="px-3 py-1.5 text-xs border border-white/10 rounded-xl bg-[#07090e] text-white focus:outline-none focus:ring-2 focus:ring-[#f4ba88]/20 focus:border-[#f4ba88]">

            @elseif($activeTab === 'weekly')
                <input type="week" name="week" value="{{ $selectedWeek }}"
                    class="px-3 py-1.5 text-xs border border-white/10 rounded-xl bg-[#07090e] text-white focus:outline-none focus:ring-2 focus:ring-[#f4ba88]/20 focus:border-[#f4ba88]">

            @elseif($activeTab === 'monthly')
                <input type="month" name="month" value="{{ $selectedMonth }}"
                    class="px-3 py-1.5 text-xs border border-white/10 rounded-xl bg-[#07090e] text-white focus:outline-none focus:ring-2 focus:ring-[#f4ba88]/20 focus:border-[#f4ba88]">

            @elseif($activeTab === 'yearly')
                <select name="year"
                    class="px-3 py-1.5 text-xs border border-white/10 rounded-xl bg-[#07090e] text-white focus:outline-none focus:ring-2 focus:ring-[#f4ba88]/20 focus:border-[#f4ba88]">
                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            @endif

            <button type="submit"
                class="btn-lull-secondary !py-1.5 !px-3.5 text-xs font-semibold">
                Tampilkan
            </button>
            <span class="text-xs text-slate-400 pl-1 hidden sm:inline">
                Periode: <strong class="text-white">{{ $periodLabel }}</strong>
            </span>
        </form>
    </div>

    {{-- ─── TOTAL OVERVIEW CARD (Mixer Hero Style) ─────────────────────── --}}
    @if($totalBudget > 0)
    <div class="lull-card p-6 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#f4ba88]"></span>
                    TOTAL ANGGARAN {{ strtoupper(match($activeTab){ 'daily'=>'HARIAN','weekly'=>'MINGGUAN','yearly'=>'TAHUNAN',default=>'BULANAN' }) }}
                </span>
                <div class="text-xs text-slate-400 mt-0.5">{{ $periodLabel }}</div>
            </div>
            
            <div class="flex items-baseline gap-3">
                <div class="text-right">
                    <div class="text-[11px] text-slate-400">Realisasi / Batas</div>
                    <div class="text-lg font-bold font-mono text-white">
                        Rp {{ number_format($totalUsed, 0, ',', '.') }}
                        <span class="text-slate-500 font-normal">/ Rp {{ number_format($totalBudget, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="text-3xl font-black font-mono {{ $totalUsed > $totalBudget ? 'text-rose-400' : ($budgetUsedPercent >= 80 ? 'text-[#f4ba88]' : 'text-emerald-400') }}">
                    {{ $budgetUsedPercent }}%
                </div>
            </div>
        </div>

        {{-- Glowing track --}}
        <div class="h-3 bg-[#07090e] border border-white/[0.08] rounded-full overflow-hidden p-0.5 mb-3">
            <div class="h-full rounded-full transition-all duration-700
                {{ $totalUsed > $totalBudget ? 'bg-gradient-to-r from-rose-600 to-rose-400 shadow-[0_0_12px_rgba(251,113,133,0.5)]' : ($budgetUsedPercent >= 80 ? 'bg-gradient-to-r from-amber-600 to-[#f4ba88] shadow-[0_0_12px_rgba(244,186,136,0.5)]' : 'bg-gradient-to-r from-emerald-600 to-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.5)]') }}"
                 style="width: {{ min($budgetUsedPercent, 100) }}%"></div>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-400">
            <span>{{ $totalUsed > $totalBudget ? 'Melebihi anggaran' : 'Anggaran terpakai' }}</span>
            <span class="font-mono {{ $totalUsed > $totalBudget ? 'text-rose-400 font-semibold' : 'text-slate-300' }}">
                {{ $totalUsed > $totalBudget ? 'Lebih Rp ' . number_format($totalUsed - $totalBudget, 0, ',', '.') : 'Sisa Rp ' . number_format($totalBudget - $totalUsed, 0, ',', '.') }}
            </span>
        </div>
    </div>
    @endif

    {{-- ─── CATEGORY CARDS GRID (Lull Mixer Fader Cards) ───────────────── --}}
    @if($budgetItems->isEmpty())
    <div class="lull-card p-12 text-center border-dashed">
        <div class="w-14 h-14 rounded-full bg-white/5 border border-white/10 text-[#f4ba88] mx-auto flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-white">Belum Ada Anggaran {{ ucfirst(match($activeTab){ 'daily'=>'Harian','weekly'=>'Mingguan','yearly'=>'Tahunan',default=>'Bulanan' }) }}</h3>
        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
            Atur batas pengeluaran kategori belanja Anda untuk periode <strong>{{ $periodLabel }}</strong>.
        </p>
        <button onclick="openBudgetModal()" class="btn-lull-primary mt-5 text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Set Anggaran Sekarang</span>
        </button>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($budgetItems as $item)
        @php
            $pct       = $item['budget'] > 0 ? min(round(($item['used'] / $item['budget']) * 100, 1), 100) : 0;
            $rawPct    = $item['budget'] > 0 ? round(($item['used'] / $item['budget']) * 100, 1) : 0;
            $over      = $item['used'] > $item['budget'] && $item['budget'] > 0;
            $near      = !$over && $pct >= 80 && $item['budget'] > 0;
            $remaining = $item['budget'] - $item['used'];
            $noBudget  = $item['budget'] == 0;
        @endphp
        <div class="lull-card p-5 group hover:border-white/20 transition-all flex flex-col justify-between">
            <div>
                <!-- Top Row: Icon, Name & Actions -->
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-[#0c1018] border border-white/10 flex items-center justify-center flex-shrink-0 shadow-inner">
                            <span class="w-3.5 h-3.5 rounded-full" style="background-color: {{ $item['color'] ?? '#f4ba88' }}; box-shadow: 0 0 8px {{ $item['color'] ?? '#f4ba88' }}80;"></span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-white tracking-tight truncate">{{ $item['name'] }}</h4>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                {{ $item['count'] }} transaksi
                                @if($noBudget)
                                &bull; <span class="text-[#f4ba88]">Belum set limit</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex items-center gap-1 opacity-90 group-hover:opacity-100 transition-opacity flex-shrink-0">
                        <button
                            onclick="openEditBudget({{ $item['budget_id'] ?? 'null' }}, '{{ addslashes($item['name']) }}', {{ $item['budget'] ?? 0 }})"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors"
                            title="Ubah Anggaran">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        @if($item['budget_id'] ?? false)
                        <form method="POST" action="{{ route('budgets.destroy', $item['budget_id']) }}"
                              onsubmit="return confirm('Hapus batas anggaran kategori {{ addslashes($item['name']) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors"
                                title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- Numbers -->
                <div class="mb-3">
                    <div class="text-[11px] text-slate-400 mb-0.5">Pengeluaran Riil</div>
                    <div class="text-xl font-bold font-mono tracking-tight text-white flex items-baseline justify-between">
                        <span>Rp {{ number_format($item['used'], 0, ',', '.') }}</span>
                        @if(!$noBudget)
                        <span class="text-xs font-semibold {{ $over ? 'text-rose-400' : ($near ? 'text-[#f4ba88]' : 'text-slate-400') }}">
                            {{ $rawPct }}%
                        </span>
                        @endif
                    </div>
                    @if(!$noBudget)
                    <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                        Batas: Rp {{ number_format($item['budget'], 0, ',', '.') }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Fader Slider Track (Lull Mixer Style) -->
            <div>
                @if(!$noBudget)
                <div class="relative h-2 bg-[#07090e] border border-white/[0.08] rounded-full overflow-hidden mb-2.5">
                    <div class="h-full rounded-full transition-all duration-500
                        {{ $over ? 'bg-gradient-to-r from-rose-600 to-rose-400 shadow-[0_0_10px_rgba(251,113,133,0.5)]' : ($near ? 'bg-gradient-to-r from-amber-600 to-[#f4ba88] shadow-[0_0_10px_rgba(244,186,136,0.5)]' : 'bg-gradient-to-r from-emerald-600 to-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.5)]') }}"
                        style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] font-mono">
                    <span class="{{ $over ? 'text-rose-400 font-semibold' : 'text-slate-400' }}">
                        {{ $over ? 'Over budget' : 'Sisa kuota' }}
                    </span>
                    <span class="{{ $over ? 'text-rose-400 font-bold' : 'text-slate-300' }}">
                        {{ $remaining >= 0 ? 'Rp ' . number_format($remaining, 0, ',', '.') : '+Rp ' . number_format(abs($remaining), 0, ',', '.') }}
                    </span>
                </div>
                @else
                <button onclick="openEditBudget(null, '{{ addslashes($item['name']) }}', 0)"
                    class="w-full py-2 rounded-xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/5 text-xs text-slate-300 transition-colors text-center">
                    + Pasang Limit Anggaran
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection

@push('modals')
{{-- ─── MODAL (Dark Luxury Lull Modal / Mobile Bottom Sheet) ────────────── --}}
<div id="budgetModal" class="hidden fixed inset-0 z-50 pointer-events-none">
    <div class="fixed inset-0 bg-black/80 backdrop-blur-md pointer-events-auto" onclick="closeModal('budgetModal')"></div>
    <div class="relative z-20 pointer-events-auto flex items-end sm:items-center justify-center min-h-screen p-0 sm:p-4">
        <div class="lull-card w-full sm:max-w-sm p-6 shadow-2xl border-t sm:border border-white/15 rounded-t-3xl sm:rounded-2xl max-h-[92vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="w-12 h-1 bg-white/20 rounded-full mx-auto mb-4 sm:hidden"></div>

            <div class="flex items-center justify-between pb-4 border-b border-white/[0.08] mb-5">
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight" id="budgetModalTitle">Set Anggaran</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $periodLabel }}</p>
                </div>
                <button onclick="closeModal('budgetModal')"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="budgetForm" method="POST" action="{{ route('budgets.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_method"     id="budgetFormMethod" value="POST">
                <input type="hidden" name="budget_type" value="{{ $activeTab }}">
                <input type="hidden" name="period"      value="{{ $currentPeriod }}">
                <input type="hidden" name="budget_id"   id="budgetId" value="">

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Kategori Pengeluaran
                    </label>
                    <select name="category" id="budgetCategory" required
                        class="w-full px-3.5 py-2.5 text-xs">
                        <option value="">Pilih kategori...</option>
                        @foreach(array_keys($expenseCategories) as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Batas Anggaran (Rp)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                        <input type="number" name="amount" id="budgetAmount" required min="1" placeholder="0"
                            class="w-full pl-10 pr-3.5 py-2.5 text-xs font-mono">
                    </div>
                </div>

                <div class="flex gap-2.5 pt-3">
                    <button type="button" onclick="closeModal('budgetModal')"
                        class="btn-lull-secondary flex-1 text-xs justify-center">
                        Batal
                    </button>
                    <button type="submit"
                        class="btn-lull-primary flex-1 text-xs justify-center">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
function openBudgetModal() {
    document.getElementById('budgetModalTitle').textContent =
        'Set Anggaran {{ match($activeTab){ "daily"=>"Harian","weekly"=>"Mingguan","yearly"=>"Tahunan",default=>"Bulanan" } }}';
    document.getElementById('budgetFormMethod').value  = 'POST';
    document.getElementById('budgetId').value          = '';
    document.getElementById('budgetCategory').value    = '';
    document.getElementById('budgetCategory').disabled = false;
    document.getElementById('budgetAmount').value      = '';
    document.getElementById('budgetForm').action       = '{{ route("budgets.store") }}';
    openModal('budgetModal');
}

function openEditBudget(id, category, amount) {
    document.getElementById('budgetModalTitle').textContent =
        (id ? 'Edit' : 'Set') + ' Anggaran {{ match($activeTab){ "daily"=>"Harian","weekly"=>"Mingguan","yearly"=>"Tahunan",default=>"Bulanan" } }}';
    document.getElementById('budgetFormMethod').value  = id ? 'PUT' : 'POST';
    document.getElementById('budgetId').value          = id || '';
    document.getElementById('budgetCategory').value    = category;
    document.getElementById('budgetCategory').disabled = !!id;
    document.getElementById('budgetAmount').value      = amount > 0 ? amount : '';
    document.getElementById('budgetForm').action       = id ? `/budgets/${id}` : '{{ route("budgets.store") }}';
    openModal('budgetModal');
}

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.body.style.overflow = '';
}
function openCreateModal() {
    openBudgetModal();
}
</script>
@endpush
