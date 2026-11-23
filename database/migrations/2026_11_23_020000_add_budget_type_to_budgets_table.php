<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            // budget_type: 'monthly' (YYYY-MM) atau 'daily' (YYYY-MM-DD)
            $table->string('budget_type', 10)->default('monthly')->after('period');

            // period sekarang bisa YYYY-MM atau YYYY-MM-DD, ubah panjang kolom
            $table->string('period', 10)->change();

            // Drop old unique constraint dan buat yang baru (include budget_type)
            $table->dropUnique(['user_id', 'category', 'period']);
            $table->unique(['user_id', 'category', 'period', 'budget_type'], 'budgets_user_cat_period_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropUnique('budgets_user_cat_period_type_unique');
            $table->string('period', 7)->change();
            $table->unique(['user_id', 'category', 'period']);
            $table->dropColumn('budget_type');
        });
    }
};

