<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@keuangan.id'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Delete existing transactions for this user so re-seeding is clean
        $user->transactions()->delete();

        $transactions = [
            [
                'date' => '2026-11-04',
                'type' => 'Pemasukan',
                'category' => 'Gaji Utama',
                'description' => 'Gaji Pokok & Tunjangan Bulanan',
                'amount' => 1276308,
                'payment_method' => 'Transfer Bank',
                'notes' => 'Masuk ke rekening utama',
            ],
            [
                'date' => '2026-11-04',
                'type' => 'Pengeluaran',
                'category' => 'Makanan & Minuman',
                'description' => 'makan pagi',
                'amount' => 10000,
                'payment_method' => 'Transfer / QRIS',
                'notes' => null,
            ],
            [
                'date' => '2026-11-04',
                'type' => 'Pengeluaran',
                'category' => 'Makanan & Minuman',
                'description' => 'makan siang',
                'amount' => 16500,
                'payment_method' => 'Transfer / QRIS',
                'notes' => null,
            ],
            [
                'date' => '2026-11-04',
                'type' => 'Pengeluaran',
                'category' => 'Makanan & Minuman',
                'description' => 'rokok',
                'amount' => 5000,
                'payment_method' => 'Transfer / QRIS',
                'notes' => null,
            ],
        ];

        foreach ($transactions as $data) {
            $user->transactions()->create($data);
        }
    }
}
