@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('page-title', 'Laporan & Analisis')
@section('page-subtitle', 'Grafik tren dan breakdown pengeluaran per kategori')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-5">

    {{-- Period Selector --}}
    <div class="flex flex-wrap gap-3 items-center">
        <form method="GET" action="{{ route('reports.index') }}" class="flex gap-2 items-center">
            <label class="text-xs font-medium text-slate-600">Periode:</label>
            <input type="month" name="month" value="{{ $selectedMonth }}"
                class="px-3 py-1.5 text-sm border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
            <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg transition-colors">Tampilkan</button>
        </form>
        <div class="ml-auto text-xs text-slate-400">Menampilkan: <strong class="text-slate-700">{{ $periodLabel }}</strong></div>
    </div>

    {{-- KPI Cards Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-500">Pemasukan</span>
            </div>
            <div class="text-xl font-bold text-slate-900 font-mono">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $incomeCount }} transaksi</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-500">Pengeluaran</span>
            </div>
            <div class="text-xl font-bold text-slate-900 font-mono">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $expenseCount }} transaksi</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl {{ $netBalance >= 0 ? 'bg-blue-100' : 'bg-amber-100' }} flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 {{ $netBalance >= 0 ? 'text-blue-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-500">Saldo Bersih</span>
            </div>
            <div class="text-xl font-bold font-mono {{ $netBalance >= 0 ? 'text-blue-700' : 'text-amber-700' }}">
                {{ $netBalance < 0 ? '-' : '' }}Rp {{ number_format(abs($netBalance), 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $netBalance >= 0 ? 'Surplus' : 'Defisit' }}</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-500">Rasio Tabungan</span>
            </div>
            <div class="text-xl font-bold text-slate-900">{{ $savingsRatio }}%</div>
            <div class="text-[11px] text-slate-400 mt-0.5">dari total pemasukan</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Donut Chart: Expense Breakdown --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Komposisi Pengeluaran</h3>
            @if($totalExpense > 0)
            <div class="flex items-center gap-6">
                <div class="w-44 h-44 flex-shrink-0">
                    <canvas id="donutChart"></canvas>
                </div>
                <div class="flex-1 space-y-2">
                    @foreach($categoryBreakdown as $cat)
                    @if($cat['total'] > 0)
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background: {{ $cat['color'] }}"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-medium text-slate-700 truncate">{{ $cat['name'] }}</div>
                            <div class="text-[11px] text-slate-400">{{ $cat['percentage'] }}%</div>
                        </div>
                        <div class="text-xs font-mono font-semibold text-slate-700">{{ number_format($cat['total'], 0, ',', '.') }}</div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @else
            <div class="text-center py-10 text-slate-300">
                <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="text-xs">Belum ada pengeluaran di periode ini</p>
            </div>
            @endif
        </div>

        {{-- Bar Chart: Monthly Trend --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-sm font-semibold text-slate-900 mb-4">Tren 6 Bulan Terakhir</h3>
            <div class="h-48">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Category Breakdown Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">Detail per Kategori</h3>
        </div>
        <div class="divide-y divide-slate-50">
            @foreach($categoryBreakdown as $cat)
            <div class="flex items-center gap-4 px-5 py-3.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: {{ $cat['color'] }}20">
                    <div class="w-3 h-3 rounded-full" style="background: {{ $cat['color'] }}"></div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-slate-800">{{ $cat['name'] }}</span>
                        <span class="text-sm font-bold font-mono text-slate-900">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full progress-bar" style="width: {{ $cat['percentage'] }}%; background: {{ $cat['color'] }}"></div>
                        </div>
                        <span class="text-[11px] text-slate-400 w-10 text-right">{{ $cat['percentage'] }}%</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">{{ $cat['count'] }} transaksi</div>
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
        borderColor: '#fff',
        hoverBorderWidth: 0,
    }]
};
new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: donutData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '65%',
        plugins: { legend: { display: false }, tooltip: {
            callbacks: {
                label: (ctx) => ` Rp ${ctx.raw.toLocaleString('id-ID')}`
            }
        }},
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
            backgroundColor: 'rgba(16,185,129,0.2)',
            borderColor: '#10b981',
            borderWidth: 2,
            borderRadius: 6,
            borderSkipped: false,
        },
        {
            label: 'Pengeluaran',
            data: @json($trendExpense),
            backgroundColor: 'rgba(244,63,94,0.15)',
            borderColor: '#f43f5e',
            borderWidth: 2,
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
            legend: { position: 'top', labels: { font: { size: 11, family: 'Plus Jakarta Sans' }, boxWidth: 12, padding: 12 } },
            tooltip: {
                callbacks: {
                    label: (ctx) => ` Rp ${ctx.raw.toLocaleString('id-ID')}`
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: {
                grid: { color: '#f1f5f9' },
                ticks: {
                    font: { size: 10 },
                    callback: v => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v >= 1000 ? (v/1000).toFixed(0)+'rb' : v))
                }
            }
        }
    }
});
</script>
@endpush

