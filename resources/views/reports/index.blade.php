@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('page-title', 'Laporan & Analisis')
@section('page-subtitle', 'Visualisasi tren arus kas dan komposisi pengeluaran')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-6">

    {{-- Period Selector Toolbar --}}
    <div class="flex flex-wrap gap-3 items-center justify-between p-3.5 lull-card">
        <form method="GET" action="{{ route('reports.index') }}" class="flex gap-2.5 items-center flex-wrap">
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Periode:</label>
            <input type="month" name="month" value="{{ $selectedMonth }}"
                class="px-3 py-1.5 text-xs">
            <button type="submit" class="btn-lull-secondary !py-1.5 !px-3.5 text-xs font-semibold">Tampilkan</button>
        </form>
        <div class="text-xs text-slate-400">Menampilkan: <strong class="text-white">{{ $periodLabel }}</strong></div>
    </div>

    {{-- KPI Cards Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="lull-card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-500/25 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Total Pemasukan</span>
            </div>
            <div class="text-xl font-bold text-white font-mono">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $incomeCount }} transaksi</div>
        </div>

        <div class="lull-card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-rose-500/15 border border-rose-500/25 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Total Pengeluaran</span>
            </div>
            <div class="text-xl font-bold text-white font-mono">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $expenseCount }} transaksi</div>
        </div>

        <div class="lull-card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl {{ $netBalance >= 0 ? 'bg-blue-500/15 border-blue-500/25 text-blue-400' : 'bg-rose-500/15 border-rose-500/25 text-rose-400' }} border flex items-center justify-center">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Saldo Bersih</span>
            </div>
            <div class="text-xl font-bold font-mono {{ $netBalance >= 0 ? 'text-[#f4ba88]' : 'text-rose-400' }}">
                {{ $netBalance < 0 ? '-' : '' }}Rp {{ number_format(abs($netBalance), 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ $netBalance >= 0 ? 'Surplus aman' : 'Defisit kas' }}</div>
        </div>

        <div class="lull-card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-[#f4ba88]/15 border border-[#f4ba88]/25 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-[#f4ba88]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Rasio Tabungan</span>
            </div>
            <div class="text-xl font-bold text-white">{{ $savingsRatio }}%</div>
            <div class="text-[11px] text-slate-500 mt-0.5">dari total pemasukan</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Donut Chart: Expense Breakdown --}}
        <div class="lull-card p-6">
            <h3 class="text-sm font-bold text-white tracking-tight mb-5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#f4ba88]"></span>
                Komposisi Pos Belanja
            </h3>
            @if($totalExpense > 0)
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <div class="w-44 h-44 flex-shrink-0 relative">
                    <canvas id="donutChart"></canvas>
                </div>
                <div class="flex-1 w-full space-y-2.5">
                    @foreach($categoryBreakdown as $cat)
                    @if($cat['total'] > 0)
                    <div class="flex items-center gap-2.5 text-xs">
                        <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background: {{ $cat['color'] }}; box-shadow: 0 0 6px {{ $cat['color'] }}80;"></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-slate-200 truncate">{{ $cat['name'] }}</div>
                            <div class="text-[10px] text-slate-500">{{ $cat['percentage'] }}%</div>
                        </div>
                        <div class="font-mono font-semibold text-white">Rp {{ number_format($cat['total'], 0, ',', '.') }}</div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @else
            <div class="text-center py-12 text-slate-500">
                <p class="text-xs">Belum ada pengeluaran di periode ini</p>
            </div>
            @endif
        </div>

        {{-- Bar Chart: Monthly Trend --}}
        <div class="lull-card p-6">
            <h3 class="text-sm font-bold text-white tracking-tight mb-5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                Tren Arus Kas (6 Bulan Terakhir)
            </h3>
            <div class="h-52">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Category Breakdown Table --}}
    <div class="lull-card overflow-hidden">
        <div class="px-6 py-4 border-b border-white/[0.08]">
            <h3 class="text-sm font-bold text-white tracking-tight">Detail Alokasi Tiap Pos</h3>
        </div>
        <div class="divide-y divide-white/[0.05]">
            @foreach($categoryBreakdown as $cat)
            <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-white/[0.02] transition-colors">
                <div class="w-8 h-8 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center flex-shrink-0">
                    <div class="w-3 h-3 rounded-full" style="background: {{ $cat['color'] }}; box-shadow: 0 0 6px {{ $cat['color'] }}80;"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-semibold text-white">{{ $cat['name'] }}</span>
                        <span class="text-xs font-bold font-mono text-white">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-1.5 bg-[#07090e] border border-white/5 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500" style="width: {{ $cat['percentage'] }}%; background: {{ $cat['color'] }};"></div>
                        </div>
                        <span class="text-[11px] font-mono text-slate-400 w-12 text-right">{{ $cat['percentage'] }}%</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Donut Chart
@if($totalExpense > 0)
const donutData = {
    labels: @json(array_column(array_filter($categoryBreakdown, fn($c) => $c['total'] > 0), 'name')),
    datasets: [{
        data: @json(array_column(array_filter($categoryBreakdown, fn($c) => $c['total'] > 0), 'total')),
        backgroundColor: @json(array_column(array_filter($categoryBreakdown, fn($c) => $c['total'] > 0), 'color')),
        borderWidth: 2,
        borderColor: '#0f141f',
        hoverBorderWidth: 0,
    }]
};
new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: donutData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '70%',
        plugins: { 
            legend: { display: false }, 
            tooltip: {
                backgroundColor: '#131926',
                titleColor: '#ffffff',
                bodyColor: '#cbd5e1',
                borderColor: 'rgba(255, 255, 255, 0.1)',
                borderWidth: 1,
                callbacks: {
                    label: (ctx) => ` Rp ${ctx.raw.toLocaleString('id-ID')}`
                }
            }
        },
    }
});
@endif

// Trend Bar Chart
const trendData = {
    labels: @json($trendLabels),
    datasets: [
        {
            label: 'Pemasukan',
            data: @json($trendIncome),
            backgroundColor: 'rgba(52, 211, 153, 0.35)',
            borderColor: '#34d399',
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false,
        },
        {
            label: 'Pengeluaran',
            data: @json($trendExpense),
            backgroundColor: 'rgba(244, 186, 136, 0.35)',
            borderColor: '#f4ba88',
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false,
        }
    ]
};
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: trendData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { 
                position: 'top', 
                labels: { 
                    color: '#cbd5e1',
                    font: { size: 11, family: 'Plus Jakarta Sans' }, 
                    boxWidth: 10, 
                    boxHeight: 10,
                    padding: 12,
                    usePointStyle: true
                } 
            },
            tooltip: {
                backgroundColor: '#131926',
                titleColor: '#ffffff',
                bodyColor: '#cbd5e1',
                borderColor: 'rgba(255, 255, 255, 0.1)',
                borderWidth: 1,
                callbacks: {
                    label: (ctx) => ` Rp ${ctx.raw.toLocaleString('id-ID')}`
                }
            }
        },
        scales: {
            x: { 
                grid: { display: false }, 
                ticks: { color: '#828c9b', font: { size: 11 } } 
            },
            y: {
                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                ticks: {
                    color: '#828c9b',
                    font: { size: 10 },
                    callback: v => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v >= 1000 ? (v/1000).toFixed(0)+'rb' : v))
                }
            }
        }
    }
});
</script>
@endpush
