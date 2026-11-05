<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');

        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_cleanly(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('KeuanganKu');
        $response->assertSee('Selamat Datang Kembali');
        $response->assertSee('Isi Akun Demo');
    }

    public function test_user_can_login_with_demo_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'demo@keuangan.id',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'demo@keuangan.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_dashboard_calculates_excel_sample_numbers_accurately(): void
    {
        $user = User::factory()->create();

        // 1 Income: 1276308
        $user->transactions()->create([
            'date' => '2026-11-04',
            'type' => 'Pemasukan',
            'category' => 'Gaji Utama',
            'description' => 'Gaji Pokok & Tunjangan Bulanan',
            'amount' => 1276308,
            'payment_method' => 'Transfer Bank',
        ]);

        // 3 Expenses: 10000 + 16500 + 5000 = 31500
        $user->transactions()->create([
            'date' => '2026-11-04',
            'type' => 'Pengeluaran',
            'category' => 'Makanan & Minuman',
            'description' => 'makan pagi',
            'amount' => 10000,
            'payment_method' => 'Transfer / QRIS',
        ]);
        $user->transactions()->create([
            'date' => '2026-11-04',
            'type' => 'Pengeluaran',
            'category' => 'Makanan & Minuman',
            'description' => 'makan siang',
            'amount' => 16500,
            'payment_method' => 'Transfer / QRIS',
        ]);
        $user->transactions()->create([
            'date' => '2026-11-04',
            'type' => 'Pengeluaran',
            'category' => 'Makanan & Minuman',
            'description' => 'rokok',
            'amount' => 5000,
            'payment_method' => 'Transfer / QRIS',
        ]);

        $response = $this->actingAs($user)->get('/dashboard?month=2026-11');
        $response->assertStatus(200);

        // Verify calculations match the Excel figures
        $response->assertSee('1.276.308'); // Total Pemasukan
        $response->assertSee('31.500'); // Total Pengeluaran
        $response->assertSee('1.244.808'); // Sisa Saldo
        $response->assertSee('makan pagi');
        $response->assertSee('makan siang');
        $response->assertSee('rokok');
    }

    public function test_user_can_create_new_transaction(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/transactions', [
            'date' => '2026-11-05',
            'type' => 'Pengeluaran',
            'category' => 'Transportasi',
            'description' => 'Bensin Pertamax',
            'amount' => '50.000', // Test currency formatting cleanup
            'payment_method' => 'Transfer / QRIS',
            'notes' => 'Di SPBU',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category' => 'Transportasi',
            'description' => 'Bensin Pertamax',
            'amount' => 50000,
        ]);
    }

    public function test_user_can_export_excel(): void
    {
        $user = User::factory()->create();
        $user->transactions()->create([
            'date' => '2026-11-04',
            'type' => 'Pengeluaran',
            'category' => 'Makanan & Minuman',
            'description' => 'Kopi',
            'amount' => 20000,
            'payment_method' => 'Tunai / Cash',
        ]);

        $response = $this->actingAs($user)->get('/export-excel');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'spreadsheetml.sheet'));
    }
}

