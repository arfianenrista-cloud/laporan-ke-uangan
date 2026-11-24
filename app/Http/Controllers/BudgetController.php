<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    // Semua tipe yang didukung
    private const TYPES = ['daily', 'weekly', 'monthly', 'yearly'];

    public function index(Request $request)
    {
        $user      = Auth::user();
        $activeTab = in_array($request->get('tab'), self::TYPES)
            ? $request->get('tab')
            : 'monthly';

        $now = Carbon::now();

        // Ambil semua parameter periode dari request, fallback ke hari ini / minggu ini / bulan ini / tahun ini
        $selectedDate  = $request->get('date',  $now->format('Y-m-d'));
        $selectedWeek  = $request->get('week',  $now->format('Y-\WW'));   // e.g. 2026-W47
        $selectedMonth = $request->get('month', $now->format('Y-m'));
        $selectedYear  = $request->get('year',  $now->format('Y'));

        $period = match ($activeTab) {
            'daily'   => $selectedDate,
            'weekly'  => $selectedWeek,
            'monthly' => $selectedMonth,
            'yearly'  => $selectedYear,
        };

        return $this->buildView($user, $activeTab, $period, $selectedDate, $selectedWeek, $selectedMonth, $selectedYear);
    }

    // ─── GENERIC VIEW BUILDER ─────────────────────────────────────────────
    private function buildView($user, string $type, string $period, string $selectedDate, string $selectedWeek, string $selectedMonth, string $selectedYear): \Illuminate\View\View
    {
        $expenseMeta = Transaction::EXPENSE_CATEGORIES;

        // Ambil anggaran sesuai tipe & periode
        $budgets = Budget::where('user_id', $user->id)
            ->where('budget_type', $type)
            ->where('period', $period)
            ->get()
            ->keyBy('category');

        // Query transaksi sesuai periode
        $txQuery = $user->transactions()->where('type', 'Pengeluaran');
        $txQuery = $this->scopeByPeriod($txQuery, $type, $period);
        $spending = $txQuery->get()->groupBy('category');

        $budgetItems = $this->buildBudgetItems($expenseMeta, $budgets, $spending);

        $totalBudget       = $budgetItems->sum('budget');
        $totalUsed         = $budgetItems->sum('used');
        $budgetUsedPercent = $totalBudget > 0
            ? min(round(($totalUsed / $totalBudget) * 100, 1), 100)
            : 0;

        return view('budgets.index', [
            'activeTab'          => $type,
            'selectedDate'       => $selectedDate,
            'selectedWeek'       => $selectedWeek,
            'selectedMonth'      => $selectedMonth,
            'selectedYear'       => $selectedYear,
            'currentPeriod'      => $period,
            'periodLabel'        => $this->periodLabel($type, $period),
            'budgetItems'        => $budgetItems->sortByDesc('budget')->values(),
            'totalBudget'        => $totalBudget,
            'totalUsed'          => $totalUsed,
            'budgetUsedPercent'  => $budgetUsedPercent,
            'expenseCategories'  => $expenseMeta,
        ]);
    }

    // ─── SCOPE QUERY BY PERIOD ───────────────────────────────────────────
    private function scopeByPeriod($query, string $type, string $period)
    {
        return match ($type) {
            'daily'   => $query->whereDate('date', $period),
            'weekly'  => (function () use ($query, $period) {
                            // period format: 2026-W47  → parse ke start/end of week
                            try {
                                $start = Carbon::fromIsoFormat('GGGG-\WW', $period)->startOfDay();
                                $end   = $start->copy()->addDays(6)->endOfDay();
                            } catch (\Exception $e) {
                                $start = Carbon::now()->startOfWeek();
                                $end   = Carbon::now()->endOfWeek();
                            }
                            return $query->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
                        })(),
            'monthly' => $query->whereRaw("strftime('%Y-%m', date) = ?", [$period]),
            'yearly'  => $query->whereRaw("strftime('%Y', date) = ?", [$period]),
        };
    }

    // ─── LABEL HELPER ────────────────────────────────────────────────────
    private function periodLabel(string $type, string $period): string
    {
        return match ($type) {
            'daily'   => Carbon::parse($period)->translatedFormat('d F Y'),
            'weekly'  => (function () use ($period) {
                            try {
                                $start = Carbon::fromIsoFormat('GGGG-\WW', $period);
                                $end   = $start->copy()->addDays(6);
                                return 'Minggu ' . $start->isoWeek() . ' (' . $start->translatedFormat('d M') . ' – ' . $end->translatedFormat('d M Y') . ')';
                            } catch (\Exception) {
                                return $period;
                            }
                        })(),
            'monthly' => Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y'),
            'yearly'  => 'Tahun ' . $period,
        };
    }

    // ─── SHARED BUDGET ITEMS BUILDER ─────────────────────────────────────
    private function buildBudgetItems(array $expenseMeta, $budgets, $spending): \Illuminate\Support\Collection
    {
        $items = collect();

        foreach ($expenseMeta as $catName => $meta) {
            $budget          = $budgets->get($catName);
            $catTransactions = $spending->get($catName, collect());
            $usedAmount      = (float) $catTransactions->sum('amount');

            if ($budget || $usedAmount > 0) {
                $items->push([
                    'name'      => $catName,
                    'color'     => $meta['color'],
                    'bg'        => $meta['bg'],
                    'budget'    => $budget ? (float) $budget->amount : 0,
                    'budget_id' => $budget?->id,
                    'used'      => $usedAmount,
                    'count'     => $catTransactions->count(),
                ]);
            }
        }

        // Kategori yang ada anggaran tapi belum ada transaksi
        foreach ($budgets as $catName => $budget) {
            if (!$items->firstWhere('name', $catName)) {
                $meta = $expenseMeta[$catName] ?? ['color' => '#64748b', 'bg' => 'bg-slate-50 text-slate-700 border-slate-200'];
                $items->push([
                    'name'      => $catName,
                    'color'     => $meta['color'],
                    'bg'        => $meta['bg'],
                    'budget'    => (float) $budget->amount,
                    'budget_id' => $budget->id,
                    'used'      => 0,
                    'count'     => 0,
                ]);
            }
        }

        return $items;
    }

    // ─── STORE ───────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $budgetType = $request->input('budget_type', 'monthly');
        if (!in_array($budgetType, self::TYPES)) {
            $budgetType = 'monthly';
        }

        $periodRules = match ($budgetType) {
            'daily'   => 'required|date_format:Y-m-d',
            'weekly'  => ['required', 'regex:/^\d{4}-W\d{2}$/'],
            'monthly' => 'required|date_format:Y-m',
            'yearly'  => ['required', 'digits:4', 'integer', 'min:2000', 'max:2099'],
        };

        // Prepare amount if only period-specific input was filled
        if (!$request->filled('amount')) {
            $request->merge([
                'amount' => $request->input('amount_' . $budgetType)
                    ?? $request->input('amount_monthly')
                    ?? $request->input('amount_daily')
                    ?? $request->input('amount_weekly')
                    ?? $request->input('amount_yearly')
            ]);
        }

        $request->validate([
            'category'       => 'required|string|max:100',
            'amount'         => 'required|numeric|min:1',
            'amount_daily'   => 'nullable|numeric|min:1',
            'amount_weekly'  => 'nullable|numeric|min:1',
            'amount_monthly' => 'nullable|numeric|min:1',
            'amount_yearly'  => 'nullable|numeric|min:1',
            'budget_type'    => 'required|in:daily,weekly,monthly,yearly',
            'period'         => $periodRules,
        ]);

        $period   = (string) $request->period;
        $category = $request->category;
        $syncAll  = $request->boolean('sync_all_periods', true);

        // Calculate all 4 periods
        $amounts = $this->resolvePeriodAmounts(
            $budgetType,
            (float) $request->amount,
            $request->filled('amount_daily')   ? (float) $request->amount_daily   : null,
            $request->filled('amount_weekly')  ? (float) $request->amount_weekly  : null,
            $request->filled('amount_monthly') ? (float) $request->amount_monthly : null,
            $request->filled('amount_yearly')  ? (float) $request->amount_yearly  : null
        );

        $now = Carbon::now();
        $pDaily   = (string) $request->input('period_daily',   $now->format('Y-m-d'));
        $pWeekly  = (string) $request->input('period_weekly',  $now->format('Y-\WW'));
        $pMonthly = (string) $request->input('period_monthly', $now->format('Y-m'));
        $pYearly  = (string) $request->input('period_yearly',  $now->format('Y'));

        if ($syncAll) {
            $syncConfigs = [
                'daily'   => ['period' => $pDaily,   'amount' => $amounts['daily']],
                'weekly'  => ['period' => $pWeekly,  'amount' => $amounts['weekly']],
                'monthly' => ['period' => $pMonthly, 'amount' => $amounts['monthly']],
                'yearly'  => ['period' => $pYearly,  'amount' => $amounts['yearly']],
            ];

            foreach ($syncConfigs as $t => $cfg) {
                if ($cfg['amount'] > 0 && !empty($cfg['period'])) {
                    Budget::updateOrCreate(
                        [
                            'user_id'     => Auth::id(),
                            'category'    => $category,
                            'budget_type' => $t,
                            'period'      => $cfg['period'],
                        ],
                        ['amount' => $cfg['amount']]
                    );
                }
            }
        } else {
            $budgetId = $request->input('budget_id');
            $primaryAmt = $amounts[$budgetType] ?? $request->amount;

            if ($budgetId) {
                $budget = Budget::where('user_id', Auth::id())->findOrFail($budgetId);
                $budget->update(['amount' => $primaryAmt]);
            } else {
                Budget::updateOrCreate(
                    [
                        'user_id'     => Auth::id(),
                        'category'    => $category,
                        'period'      => $period,
                        'budget_type' => $budgetType,
                    ],
                    ['amount' => $primaryAmt]
                );
            }
        }

        return redirect()
            ->route('budgets.index', $this->redirectParams($budgetType, $period))
            ->with('toast_success', $syncAll
                ? 'Anggaran ' . $category . ' berhasil disimpan dan otomatis disinkronkan ke semua periode (Harian, Mingguan, Bulanan, Tahunan)!'
                : 'Anggaran berhasil disimpan!');
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────
    public function update(Request $request, Budget $budget)
    {
        abort_unless($budget->user_id === Auth::id(), 403);

        $request->validate(['amount' => 'nullable|numeric|min:1']);
        $amount = (float) $request->input('amount', $request->input('amount_' . $budget->budget_type, $budget->amount));
        $budget->update(['amount' => $amount]);

        $syncAll = $request->boolean('sync_all_periods', true);
        if ($syncAll) {
            $amounts = $this->resolvePeriodAmounts(
                $budget->budget_type,
                $amount,
                $request->filled('amount_daily')   ? (float) $request->amount_daily   : null,
                $request->filled('amount_weekly')  ? (float) $request->amount_weekly  : null,
                $request->filled('amount_monthly') ? (float) $request->amount_monthly : null,
                $request->filled('amount_yearly')  ? (float) $request->amount_yearly  : null
            );

            $now = Carbon::now();
            $pDaily   = (string) $request->input('period_daily',   $now->format('Y-m-d'));
            $pWeekly  = (string) $request->input('period_weekly',  $now->format('Y-\WW'));
            $pMonthly = (string) $request->input('period_monthly', $now->format('Y-m'));
            $pYearly  = (string) $request->input('period_yearly',  $now->format('Y'));

            $syncConfigs = [
                'daily'   => ['period' => $pDaily,   'amount' => $amounts['daily']],
                'weekly'  => ['period' => $pWeekly,  'amount' => $amounts['weekly']],
                'monthly' => ['period' => $pMonthly, 'amount' => $amounts['monthly']],
                'yearly'  => ['period' => $pYearly,  'amount' => $amounts['yearly']],
            ];

            foreach ($syncConfigs as $t => $cfg) {
                if ($t !== $budget->budget_type && $cfg['amount'] > 0 && !empty($cfg['period'])) {
                    Budget::updateOrCreate(
                        [
                            'user_id'     => Auth::id(),
                            'category'    => $budget->category,
                            'budget_type' => $t,
                            'period'      => $cfg['period'],
                        ],
                        ['amount' => $cfg['amount']]
                    );
                }
            }
        }

        return back()->with('toast_success', 'Anggaran berhasil diperbarui dan disinkronkan ke seluruh periode!');
    }

    // ─── PERIOD AMOUNTS RESOLVER ─────────────────────────────────────────
    private function resolvePeriodAmounts(string $baseType, float $baseAmount, ?float $daily, ?float $weekly, ?float $monthly, ?float $yearly): array
    {
        // 1. If daily was explicitly supplied
        if ($daily !== null && $daily > 0 && $weekly === null && $monthly === null && $yearly === null) {
            $w = round($daily * 7);
            $m = round($daily * 30);
            $y = round($m * 12);
            return ['daily' => $daily, 'weekly' => $w, 'monthly' => $m, 'yearly' => $y];
        }

        // 2. If weekly was explicitly supplied
        if ($weekly !== null && $weekly > 0 && $daily === null && $monthly === null && $yearly === null) {
            $d = round($weekly / 7);
            $m = round($d * 30);
            $y = round($m * 12);
            return ['daily' => $d, 'weekly' => $weekly, 'monthly' => $m, 'yearly' => $y];
        }

        // 3. If monthly was explicitly supplied
        if ($monthly !== null && $monthly > 0 && $daily === null && $weekly === null && $yearly === null) {
            $d = round($monthly / 30);
            $w = round($d * 7);
            $y = round($monthly * 12);
            return ['daily' => $d, 'weekly' => $w, 'monthly' => $monthly, 'yearly' => $y];
        }

        // 4. If yearly was explicitly supplied
        if ($yearly !== null && $yearly > 0 && $daily === null && $weekly === null && $monthly === null) {
            $m = round($yearly / 12);
            $d = round($m / 30);
            $w = round($d * 7);
            return ['daily' => $d, 'weekly' => $w, 'monthly' => $m, 'yearly' => $yearly];
        }

        // 5. If all or multiple were provided (e.g. from client calculation)
        if ($daily !== null || $weekly !== null || $monthly !== null || $yearly !== null) {
            $d = $daily ?? ($monthly ? round($monthly / 30) : ($weekly ? round($weekly / 7) : round($yearly / 360)));
            $w = $weekly ?? round($d * 7);
            $m = $monthly ?? round($d * 30);
            $y = $yearly ?? round($m * 12);
            return ['daily' => (float)$d, 'weekly' => (float)$w, 'monthly' => (float)$m, 'yearly' => (float)$y];
        }

        // 6. Fallback based on $baseType & $baseAmount
        return match ($baseType) {
            'daily' => [
                'daily'   => $baseAmount,
                'weekly'  => round($baseAmount * 7),
                'monthly' => round($baseAmount * 30),
                'yearly'  => round($baseAmount * 30 * 12),
            ],
            'weekly' => [
                'daily'   => round($baseAmount / 7),
                'weekly'  => $baseAmount,
                'monthly' => round(($baseAmount / 7) * 30),
                'yearly'  => round(($baseAmount / 7) * 30 * 12),
            ],
            'yearly' => [
                'daily'   => round(($baseAmount / 12) / 30),
                'weekly'  => round((($baseAmount / 12) / 30) * 7),
                'monthly' => round($baseAmount / 12),
                'yearly'  => $baseAmount,
            ],
            default => [
                'daily'   => round($baseAmount / 30),
                'weekly'  => round(($baseAmount / 30) * 7),
                'monthly' => $baseAmount,
                'yearly'  => round($baseAmount * 12),
            ],
        };
    }

    // ─── DESTROY ─────────────────────────────────────────────────────────
    public function destroy(Request $request, Budget $budget)
    {
        abort_unless($budget->user_id === Auth::id(), 403);
        $type   = $budget->budget_type;
        $period = $budget->period;
        $budget->delete();

        return redirect()
            ->route('budgets.index', $this->redirectParams($type, $period))
            ->with('toast_success', 'Anggaran berhasil dihapus.');
    }

    // ─── REDIRECT PARAMS HELPER ───────────────────────────────────────────
    private function redirectParams(string $type, string $period): array
    {
        return match ($type) {
            'daily'   => ['tab' => 'daily',   'date'  => $period],
            'weekly'  => ['tab' => 'weekly',  'week'  => $period],
            'monthly' => ['tab' => 'monthly', 'month' => $period],
            'yearly'  => ['tab' => 'yearly',  'year'  => $period],
        };
    }
}
