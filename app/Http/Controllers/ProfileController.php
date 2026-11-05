<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        $totalTransactions = $user->transactions()->count();
        $totalIncome       = $user->transactions()->where('type', 'Pemasukan')->count();
        $totalExpense      = $user->transactions()->where('type', 'Pengeluaran')->count();

        return view('profile.edit', [
            'totalTransactions' => $totalTransactions,
            'totalIncome'       => $totalIncome,
            'totalExpense'      => $totalExpense,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return back()->with('toast_success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, Auth::user()->password)) {
                    $fail('Password lama tidak sesuai.');
                }
            }],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('toast_success', 'Password berhasil diperbarui!');
    }
}
