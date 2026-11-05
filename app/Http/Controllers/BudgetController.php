<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));

        // Budgets for selected month
        $budgets = Budget::where('user_id', $user->id)
            ->where('period', $selectedMonth)
            ->get()
            ->keyBy('category');

        // Actual spending per category
        $spending = $user->transactions()
            ->where('type', 'Pengeluaran')
            ->whereRaw("strftime('%Y-%m', date) = ?", [$selectedMonth])
            ->get()
            ->groupBy('category');

        $expenseCategoriesMeta = Transaction::EXPENSE_CATEGORIES;
        $budgetItems = collect();

        foreach ($expenseCategoriesMeta as $catName => $meta) {
            $budget = $budgets->get($catName);
            $catTransactions = $spending->get($catName, collect());
            $usedAmount = (float) $catTransactions->sum('amount');

            if ($budget || $usedAmount > 0) {
                $budgetItems->push([
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

        // Also add categories that have budget but no spending
        foreach ($budgets as $catName => $budget) {
            if (!$budgetItems->firstWhere('name', $catName)) {
                $meta = $expenseCategoriesMeta[$catName] ?? ['color' => '#64748b', 'bg' => 'bg-slate-50 text-slate-700 border-slate-200'];
                $budgetItems->push([
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

        $totalBudget      = $budgetItems->sum('budget');
        $totalUsed        = $budgetItems->sum('used');
        $budgetUsedPercent = $totalBudget > 0 ? min(round(($totalUsed / $totalBudget) * 100, 1), 100) : 0;

        return view('budgets.index', [
            'selectedMonth'      => $selectedMonth,
            'budgetItems'        => $budgetItems->sortByDesc('used')->values(),
            'totalBudget'        => $totalBudget,
            'totalUsed'          => $totalUsed,
            'budgetUsedPercent'  => $budgetUsedPercent,
            'expenseCategories'  => $expenseCategoriesMeta,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'amount'   => 'required|numeric|min:1',
            'month'    => 'required|date_format:Y-m',
        ]);

        $budgetId = $request->input('budget_id');

        if ($budgetId) {
            $budget = Budget::where('user_id', Auth::id())->findOrFail($budgetId);
            $budget->update(['amount' => $request->amount]);
        } else {
            Budget::updateOrCreate(
                [
                    'user_id'  => Auth::id(),
                    'category' => $request->category,
                    'period'   => $request->month,
                ],
                ['amount' => $request->amount]
            );
        }

        return back()
            ->with('toast_success', 'Anggaran berhasil disimpan!')
            ->withInput(['month' => $request->month]);
    }

    public function update(Request $request, Budget $budget)
    {
        abort_unless($budget->user_id === Auth::id(), 403);

        $request->validate(['amount' => 'required|numeric|min:1']);
        $budget->update(['amount' => $request->amount]);

        return back()->with('toast_success', 'Anggaran berhasil diperbarui!');
    }

    public function destroy(Request $request, Budget $budget)
    {
        abort_unless($budget->user_id === Auth::id(), 403);
        $budget->delete();

        return redirect()
            ->route('budgets.index', ['month' => $request->month ?? Carbon::now()->format('Y-m')])
            ->with('toast_success', 'Anggaran berhasil dihapus.');
    }
}
