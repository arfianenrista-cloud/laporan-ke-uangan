<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Default to current month
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));

        try {
            $carbonMonth = Carbon::createFromFormat('Y-m', $selectedMonth);
            $periodLabel = $carbonMonth->translatedFormat('F Y');
        } catch (\Exception $e) {
            $selectedMonth = Carbon::now()->format('Y-m');
            $carbonMonth   = Carbon::now();
            $periodLabel   = $carbonMonth->translatedFormat('F Y');
        }

        // Transactions for the selected month
        $monthTransactions = $user->transactions()
            ->whereRaw("strftime('%Y-%m', date) = ?", [$selectedMonth])
            ->get();

        $totalIncome  = (float) $monthTransactions->where('type', 'Pemasukan')->sum('amount');
        $totalExpense = (float) $monthTransactions->where('type', 'Pengeluaran')->sum('amount');
        $netBalance   = $totalIncome - $totalExpense;
        $savingsRatio = $totalIncome > 0 ? max(0, round(($netBalance / $totalIncome) * 100, 1)) : 0;
        $incomeCount  = $monthTransactions->where('type', 'Pemasukan')->count();
        $expenseCount = $monthTransactions->where('type', 'Pengeluaran')->count();

        // Category breakdown for expenses
        $expenseCategoriesMeta = Transaction::EXPENSE_CATEGORIES;
        $categoryBreakdown = [];

        foreach ($expenseCategoriesMeta as $catName => $meta) {
            $catTotal = (float) $monthTransactions
                ->where('type', 'Pengeluaran')
                ->where('category', $catName)
                ->sum('amount');

            $percentage = $totalExpense > 0 ? round(($catTotal / $totalExpense) * 100, 1) : 0;
            $catCount   = $monthTransactions->where('type', 'Pengeluaran')->where('category', $catName)->count();

            $categoryBreakdown[$catName] = [
                'name'       => $catName,
                'total'      => $catTotal,
                'percentage' => $percentage,
                'count'      => $catCount,
                'icon'       => $meta['icon'],
                'color'      => $meta['color'],
                'bg'         => $meta['bg'],
            ];
        }

        // 6-month trend data
        $trendLabels  = [];
        $trendIncome  = [];
        $trendExpense = [];

        for ($i = 5; $i >= 0; $i--) {
            $m    = $carbonMonth->copy()->subMonths($i);
            $mKey = $m->format('Y-m');

            $mTransactions = $user->transactions()
                ->whereRaw("strftime('%Y-%m', date) = ?", [$mKey])
                ->get();

            $trendLabels[]  = $m->translatedFormat('M Y');
            $trendIncome[]  = (float) $mTransactions->where('type', 'Pemasukan')->sum('amount');
            $trendExpense[] = (float) $mTransactions->where('type', 'Pengeluaran')->sum('amount');
        }

        return view('reports.index', [
            'selectedMonth'     => $selectedMonth,
            'periodLabel'       => $periodLabel,
            'totalIncome'       => $totalIncome,
            'totalExpense'      => $totalExpense,
            'netBalance'        => $netBalance,
            'savingsRatio'      => $savingsRatio,
            'incomeCount'       => $incomeCount,
            'expenseCount'      => $expenseCount,
            'categoryBreakdown' => array_values($categoryBreakdown),
            'trendLabels'       => $trendLabels,
            'trendIncome'       => $trendIncome,
            'trendExpense'      => $trendExpense,
        ]);
    }
}
