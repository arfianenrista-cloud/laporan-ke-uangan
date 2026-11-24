<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_daily_budget_syncs_all_periods_accurately(): void
    {
        $user = User::factory()->create();
        $today = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($user)->post('/budgets', [
            'category'          => 'Makanan & Minuman',
            'amount_daily'      => 50000,
            'budget_type'       => 'daily',
            'period'            => $today,
            'sync_all_periods'  => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('toast_success');

        // Check daily budget: 50.000
        $this->assertDatabaseHas('budgets', [
            'user_id'     => $user->id,
            'category'    => 'Makanan & Minuman',
            'budget_type' => 'daily',
            'amount'      => 50000,
        ]);

        // Check weekly budget: 50.000 * 7 = 350.000
        $this->assertDatabaseHas('budgets', [
            'user_id'     => $user->id,
            'category'    => 'Makanan & Minuman',
            'budget_type' => 'weekly',
            'amount'      => 350000,
        ]);

        // Check monthly budget: 50.000 * 30 = 1.500.000
        $this->assertDatabaseHas('budgets', [
            'user_id'     => $user->id,
            'category'    => 'Makanan & Minuman',
            'budget_type' => 'monthly',
            'amount'      => 1500000,
        ]);

        // Check yearly budget: 1.500.000 * 12 = 18.000.000
        $this->assertDatabaseHas('budgets', [
            'user_id'     => $user->id,
            'category'    => 'Makanan & Minuman',
            'budget_type' => 'yearly',
            'amount'      => 18000000,
        ]);
    }

    public function test_submitting_monthly_budget_syncs_all_periods_accurately(): void
    {
        $user = User::factory()->create();
        $month = Carbon::now()->format('Y-m');

        $response = $this->actingAs($user)->post('/budgets', [
            'category'          => 'Transportasi',
            'amount_monthly'    => 1500000,
            'budget_type'       => 'monthly',
            'period'            => $month,
            'sync_all_periods'  => 1,
        ]);

        $response->assertRedirect();

        // 1.500.000 / 30 = 50.000
        $this->assertDatabaseHas('budgets', [
            'user_id'     => $user->id,
            'category'    => 'Transportasi',
            'budget_type' => 'daily',
            'amount'      => 50000,
        ]);

        // 50.000 * 7 = 350.000
        $this->assertDatabaseHas('budgets', [
            'user_id'     => $user->id,
            'category'    => 'Transportasi',
            'budget_type' => 'weekly',
            'amount'      => 350000,
        ]);

        $this->assertDatabaseHas('budgets', [
            'user_id'     => $user->id,
            'category'    => 'Transportasi',
            'budget_type' => 'monthly',
            'amount'      => 1500000,
        ]);

        $this->assertDatabaseHas('budgets', [
            'user_id'     => $user->id,
            'category'    => 'Transportasi',
            'budget_type' => 'yearly',
            'amount'      => 18000000,
        ]);
    }
}
