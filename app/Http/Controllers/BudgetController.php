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

        return match ($activeTab) {
            'daily'   => $this->buildView($user, 'daily',   $selectedDate),
            'weekly'  => $this->buildView($user, 'weekly',  $selectedWeek),
            'monthly' => $this->buildView($user, 'monthly', $selectedMonth),
            'yearly'  => $this->buildView($user, 'yearly',  $selectedYear),
        };
    }

    // ─── GENERIC VIEW BUILDER ─────────────────────────────────────────────
    private function buildView($user, string $type, string $period): \Illuminate\View\View
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

        $now = Carbon::now();

        return view('budgets.index', [
            'activeTab'          => $type,
            'selectedDate'       => $now->format('Y-m-d'),
            'selectedWeek'       => $now->format('Y-\WW'),
            'selectedMonth'      => $now->format('Y-m'),
            'selectedYear'       => $now->format('Y'),
            // override selected period for the active tab
            'selected' . ucfirst($type === 'daily' ? 'Date' : ($type === 'weekly' ? 'Week' : ($type === 'monthly' ? 'Month' : 'Year'))) => $period,
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

        $request->validate([
            'category'    => 'required|string|max:100',
            'amount'      => 'required|numeric|min:1',
            'budget_type' => 'required|in:daily,weekly,monthly,yearly',
            'period'      => $periodRules,
        ]);

        $period   = (string) $request->period;
        $budgetId = $request->input('budget_id');

        if ($budgetId) {
            $budget = Budget::where('user_id', Auth::id())->findOrFail($budgetId);
            $budget->update(['amount' => $request->amount]);
        } else {
            Budget::updateOrCreate(
                [
                    'user_id'     => Auth::id(),
                    'category'    => $request->category,
                    'period'      => $period,
                    'budget_type' => $budgetType,
                ],
                ['amount' => $request->amount]
            );
        }

        return redirect()
            ->route('budgets.index', $this->redirectParams($budgetType, $period))
            ->with('toast_success', 'Anggaran berhasil disimpan!');
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────
    public function update(Request $request, Budget $budget)
    {
        abort_unless($budget->user_id === Auth::id(), 403);
        $request->validate(['amount' => 'required|numeric|min:1']);
        $budget->update(['amount' => $request->amount]);
        return back()->with('toast_success', 'Anggaran berhasil diperbarui!');
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
