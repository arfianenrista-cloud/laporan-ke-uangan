<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->transactions();

        if ($request->filled('month')) {
            $query->whereRaw("strftime('%Y-%m', date) = ?", [$request->month]);
        }

        if ($request->filled('type') && in_array($request->type, ['Pemasukan', 'Pengeluaran'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                  ->orWhere('category', 'like', $search)
                  ->orWhere('payment_method', 'like', $search)
                  ->orWhere('notes', 'like', $search);
            });
        }

        $transactions = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        $totalIncome   = (float) $transactions->where('type', 'Pemasukan')->sum('amount');
        $totalExpense  = (float) $transactions->where('type', 'Pengeluaran')->sum('amount');
        $netBalance    = $totalIncome - $totalExpense;

        return view('transactions.index', [
            'transactions'       => $transactions,
            'totalIncome'        => $totalIncome,
            'totalExpense'       => $totalExpense,
            'netBalance'         => $netBalance,
            'expenseCategories'  => Transaction::EXPENSE_CATEGORIES,
            'incomeCategories'   => Transaction::INCOME_CATEGORIES,
            'paymentMethods'     => Transaction::PAYMENT_METHODS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'           => 'required|in:Pemasukan,Pengeluaran',
            'date'           => 'required|date',
            'amount'         => 'required|min:1',
            'category'       => 'required|string|max:100',
            'description'    => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
        ]);

        // Normalize amount: strip thousands separators (dots) and convert comma decimals
        $validated['amount'] = (float) str_replace(['.', ','], ['', '.'], preg_replace('/[^0-9.,]/', '', $validated['amount']));

        $validated['user_id'] = Auth::id();
        Transaction::create($validated);

        return back()->with('toast_success', 'Transaksi berhasil ditambahkan!');
    }

    public function update(Request $request, Transaction $transaction)
    {
        abort_unless($transaction->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'type'           => 'required|in:Pemasukan,Pengeluaran',
            'date'           => 'required|date',
            'amount'         => 'required|min:1',
            'category'       => 'required|string|max:100',
            'description'    => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:500',
        ]);

        // Normalize amount: strip thousands separators (dots) for Indonesian format like "50.000"
        $validated['amount'] = (float) str_replace(['.', ','], ['', '.'], preg_replace('/[^0-9.,]/', '', $validated['amount']));

        $transaction->update($validated);

        return back()->with('toast_success', 'Transaksi berhasil diperbarui!');
    }

    public function destroy(Transaction $transaction)
    {
        abort_unless($transaction->user_id === Auth::id(), 403);
        $transaction->delete();
        return back()->with('toast_success', 'Transaksi berhasil dihapus.');
    }
}
