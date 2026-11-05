<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'type',
        'category',
        'description',
        'amount',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'amount' => 'float',
    ];

    public const EXPENSE_CATEGORIES = [
        'Makanan & Minuman' => [
            'icon' => 'utensils',
            'color' => '#f97316', // orange
            'bg' => 'bg-orange-50 text-orange-700 border-orange-200',
        ],
        'Transportasi' => [
            'icon' => 'car',
            'color' => '#3b82f6', // blue
            'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
        ],
        'Tagihan & Utilitas' => [
            'icon' => 'zap',
            'color' => '#eab308', // amber
            'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
        ],
        'Kebutuhan Rumah' => [
            'icon' => 'home',
            'color' => '#14b8a6', // teal
            'bg' => 'bg-teal-50 text-teal-700 border-teal-200',
        ],
        'Hiburan & Gaya Hidup' => [
            'icon' => 'film',
            'color' => '#a855f7', // purple
            'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
        ],
        'Kesehatan' => [
            'icon' => 'heart-pulse',
            'color' => '#ec4899', // pink
            'bg' => 'bg-pink-50 text-pink-700 border-pink-200',
        ],
        'Pendidikan & Karir' => [
            'icon' => 'graduation-cap',
            'color' => '#6366f1', // indigo
            'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        ],
        'Lain-lain' => [
            'icon' => 'more-horizontal',
            'color' => '#64748b', // slate
            'bg' => 'bg-slate-50 text-slate-700 border-slate-200',
        ],
    ];

    public const INCOME_CATEGORIES = [
        'Gaji Utama' => [
            'icon' => 'briefcase',
            'color' => '#10b981',
            'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ],
        'Bonus & Tunjangan' => [
            'icon' => 'award',
            'color' => '#06b6d4',
            'bg' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
        ],
        'Freelance / Bisnis' => [
            'icon' => 'laptop',
            'color' => '#8b5cf6',
            'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
        ],
        'Investasi' => [
            'icon' => 'trending-up',
            'color' => '#10b981',
            'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ],
        'Hadiah / Hibah' => [
            'icon' => 'gift',
            'color' => '#f43f5e',
            'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
        ],
        'Lain-lain' => [
            'icon' => 'dollar-sign',
            'color' => '#64748b',
            'bg' => 'bg-slate-50 text-slate-700 border-slate-200',
        ],
    ];

    public const PAYMENT_METHODS = [
        'Transfer Bank',
        'Transfer / QRIS',
        'Tunai / Cash',
        'E-Wallet (GoPay/OVO/Dana)',
        'Kartu Debit / Kredit',
        'Lainnya',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedDateAttribute(): string
    {
        return Carbon::parse($this->date)->translatedFormat('d M Y');
    }
}
