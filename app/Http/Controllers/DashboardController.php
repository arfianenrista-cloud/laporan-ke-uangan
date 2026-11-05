<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get distinct years from user transactions
        $userYears = $user->transactions()
            ->selectRaw("strftime('%Y', date) as yr")
            ->groupBy('yr')
            ->pluck('yr')
            ->filter()
            ->map(fn($y) => (int) $y)
            ->toArray();

        $currentCarbon = Carbon::now();
        $currentYear = (int) $currentCarbon->year;
        $currentMonthKey = $currentCarbon->format('Y-m');

        // Always include current year and neighboring years
        $allYears = array_unique(array_merge([$currentYear - 1, $currentYear, $currentYear + 1], $userYears));
        rsort($allYears); // Sort descending: e.g. 2027, 2026, 2025

        // Count of transactions per month for visual indicators
        $countsByMonth = $user->transactions()
            ->selectRaw("strftime('%Y-%m', date) as m_key, count(*) as total_count")
            ->groupBy('m_key')
            ->pluck('total_count', 'm_key')
            ->toArray();

        // Build all 12 complete months for each year
        $monthGroups = [];
        foreach ($allYears as $year) {
            $monthsInYear = [];
            for ($m = 12; $m >= 1; $m--) {
                $mKey = sprintf('%04d-%02d', $year, $m);
                $dateObj = Carbon::createFromDate($year, $m, 1);
                $count = $countsByMonth[$mKey] ?? 0;
                $monthsInYear[] = [
                    'key' => $mKey,
                    'label' => $dateObj->translatedFormat('F Y'),
                    'month_name' => $dateObj->translatedFormat('F'),
                    'count' => $count,
                ];
            }
            $monthGroups[$year] = $monthsInYear;
        }

        // Determine active filter from request: date or month
        $selectedDate = $request->get('date');
        $selectedMonth = $request->get('month');

        if ($selectedDate) {
            try {
                $carbonDate = Carbon::parse($selectedDate);
                $selectedMonth = $carbonDate->format('Y-m');
            } catch (\Exception $e) {
                $selectedDate = null;
            }
        }

        if (!$selectedMonth && !$selectedDate && $selectedMonth !== 'all') {
            $latestTrx = $user->transactions()->orderBy('date', 'desc')->first();
            if ($latestTrx) {
                $selectedMonth = Carbon::parse($latestTrx->date)->format('Y-m');
            } else {
                $selectedMonth = $currentMonthKey;
            }
        }

        // Active label for UI header
        $activeMonthLabel = 'Semua Periode';
        if ($selectedDate) {
            $activeMonthLabel = Carbon::parse($selectedDate)->translatedFormat('d F Y');
        } elseif ($selectedMonth && $selectedMonth !== 'all') {
            try {
                $activeMonthLabel = Carbon::createFromFormat('Y-m', $selectedMonth)->translatedFormat('F Y');
            } catch (\Exception $e) {
                $activeMonthLabel = $selectedMonth;
            }
        }

        // Prev and Next month navigation
        $prevMonth = $currentMonthKey;
        $nextMonth = $currentMonthKey;
        if ($selectedMonth && $selectedMonth !== 'all') {
            try {
                $carbonActive = Carbon::createFromFormat('Y-m', $selectedMonth);
                $prevMonth = $carbonActive->copy()->subMonth()->format('Y-m');
                $nextMonth = $carbonActive->copy()->addMonth()->format('Y-m');
            } catch (\Exception $e) {
                $prevMonth = $currentMonthKey;
                $nextMonth = $currentMonthKey;
            }
        }

        // Base query for stats (scoped to selected date or selected month unless 'all')
        $statsQuery = $user->transactions();
        if ($selectedDate) {
            $statsQuery->whereDate('date', $selectedDate);
        } elseif ($selectedMonth && $selectedMonth !== 'all') {
            $statsQuery->whereRaw("strftime('%Y-%m', date) = ?", [$selectedMonth]);
        }

        // Clone query for stats
        $monthTransactions = (clone $statsQuery)->get();

        $totalIncome = (float) $monthTransactions->where('type', 'Pemasukan')->sum('amount');
        $totalExpense = (float) $monthTransactions->where('type', 'Pengeluaran')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;
        $savingsRatio = $totalIncome > 0 ? max(0, round(($netBalance / $totalIncome) * 100, 1)) : 0;

        // Categories breakdown for Pengeluaran (matching Excel categories)
        $expenseCategoriesMeta = Transaction::EXPENSE_CATEGORIES;
        $categoryBreakdown = [];

        foreach ($expenseCategoriesMeta as $catName => $meta) {
            $catTotal = (float) $monthTransactions
                ->where('type', 'Pengeluaran')
                ->where('category', $catName)
                ->sum('amount');

            $percentage = $totalExpense > 0 ? round(($catTotal / $totalExpense) * 100, 1) : 0;
            $catCount = $monthTransactions
                ->where('type', 'Pengeluaran')
                ->where('category', $catName)
                ->count();

            $categoryBreakdown[$catName] = [
                'name' => $catName,
                'total' => $catTotal,
                'percentage' => $percentage,
                'count' => $catCount,
                'icon' => $meta['icon'],
                'color' => $meta['color'],
                'bg' => $meta['bg'],
            ];
        }

        // Also check if any custom categories exist in transactions that are not in defaults
        $otherExpenseCategories = $monthTransactions
            ->where('type', 'Pengeluaran')
            ->pluck('category')
            ->unique()
            ->diff(array_keys($expenseCategoriesMeta));

        foreach ($otherExpenseCategories as $customCat) {
            $catTotal = (float) $monthTransactions
                ->where('type', 'Pengeluaran')
                ->where('category', $customCat)
                ->sum('amount');
            $percentage = $totalExpense > 0 ? round(($catTotal / $totalExpense) * 100, 1) : 0;

            $categoryBreakdown[$customCat] = [
                'name' => $customCat,
                'total' => $catTotal,
                'percentage' => $percentage,
                'count' => $monthTransactions->where('type', 'Pengeluaran')->where('category', $customCat)->count(),
                'icon' => 'tag',
                'color' => '#64748b',
                'bg' => 'bg-slate-50 text-slate-700 border-slate-200',
            ];
        }

        // Transactions list with optional filtering
        $listQuery = $user->transactions();

        if ($selectedDate) {
            $listQuery->whereDate('date', $selectedDate);
        } elseif ($selectedMonth && $selectedMonth !== 'all') {
            $listQuery->whereRaw("strftime('%Y-%m', date) = ?", [$selectedMonth]);
        }

        if ($request->filled('type') && in_array($request->type, ['Pemasukan', 'Pengeluaran'])) {
            $listQuery->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $listQuery->where('category', $request->category);
        }

        if ($request->filled('payment_method')) {
            $listQuery->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $listQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                  ->orWhere('category', 'like', $search)
                  ->orWhere('payment_method', 'like', $search)
                  ->orWhere('notes', 'like', $search);
            });
        }

        // Get transactions ordered by date descending, then id descending
        $transactions = $listQuery->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        // Totals for filtered transactions (for the bottom summary table row)
        $filteredTotalIncome = $transactions->where('type', 'Pemasukan')->sum('amount');
        $filteredTotalExpense = $transactions->where('type', 'Pengeluaran')->sum('amount');

        // Transaction dates with counts for calendar dots indicator
        $transactionDatesWithCount = $user->transactions()
            ->selectRaw("strftime('%Y-%m-%d', date) as dt, count(*) as count")
            ->groupBy('dt')
            ->pluck('count', 'dt')
            ->toArray();

        return view('dashboard', [
            'user' => $user,
            'selectedDate' => $selectedDate,
            'selectedMonth' => $selectedMonth,
            'activeMonthLabel' => $activeMonthLabel,
            'monthGroups' => $monthGroups,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'currentMonthKey' => $currentMonthKey,
            'transactionDates' => $transactionDatesWithCount,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netBalance' => $netBalance,
            'savingsRatio' => $savingsRatio,
            'categoryBreakdown' => $categoryBreakdown,
            'transactions' => $transactions,
            'filteredTotalIncome' => $filteredTotalIncome,
            'filteredTotalExpense' => $filteredTotalExpense,
            'expenseCategories' => Transaction::EXPENSE_CATEGORIES,
            'incomeCategories' => Transaction::INCOME_CATEGORIES,
            'paymentMethods' => Transaction::PAYMENT_METHODS,
            'totalTransactionCount' => $user->transactions()->count(),
        ]);
    }

    /**
     * Export to a professionally formatted native Microsoft Excel (.xlsx) file.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $selectedDate = $request->get('date');
        $selectedMonth = $request->get('month', 'all');

        $query = $user->transactions();
        if ($selectedDate) {
            $query->whereDate('date', $selectedDate);
            $periodLabel = Carbon::parse($selectedDate)->translatedFormat('d F Y');
        } elseif ($selectedMonth && $selectedMonth !== 'all') {
            $query->whereRaw("strftime('%Y-%m', date) = ?", [$selectedMonth]);
            try {
                $periodLabel = Carbon::createFromFormat('Y-m', $selectedMonth)->translatedFormat('F Y');
            } catch (\Exception $e) {
                $periodLabel = $selectedMonth;
            }
        } else {
            $periodLabel = 'Semua Periode';
        }
        $transactions = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        $totalIncome = (float) $transactions->where('type', 'Pemasukan')->sum('amount');
        $totalExpense = (float) $transactions->where('type', 'Pengeluaran')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('KeuanganKu Personal')
            ->setTitle('Dashboard Ringkasan Keuangan - ' . $periodLabel)
            ->setSubject('Laporan Keuangan Bulanan');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Keuangan');
        $sheet->setShowGridLines(true);

        // Global font
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        // 1. Title & Header Banner
        $sheet->setCellValue('A1', 'Dashboard Ringkasan Keuangan Bulanan');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('0F172A');

        $sheet->setCellValue('A2', 'Pantau total pemasukan, pengeluaran, dan sisa saldo bulanan Anda.');
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('64748B');

        $sheet->setCellValue('A3', 'Periode: ' . $periodLabel . '   |   Pengguna: ' . $user->name . '   |   Dicetak: ' . Carbon::now()->translatedFormat('d F Y H:i'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('94A3B8');

        // 2. Three Metric KPI Cards (Matching Excel)
        // Card 1: TOTAL PEMASUKAN
        $sheet->mergeCells('A5:B5');
        $sheet->setCellValue('A5', 'TOTAL PEMASUKAN');
        $sheet->getStyle('A5')->getFont()->setSize(9)->setBold(true)->getColor()->setRGB('065F46');
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');

        $sheet->mergeCells('A6:B6');
        $sheet->setCellValue('A6', $totalIncome);
        $sheet->getStyle('A6')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('047857');
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('A6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDF4');

        $this->applyBoxBorder($sheet, 'A5:B6', '86EFAC');

        // Card 2: TOTAL PENGELUARAN
        $sheet->mergeCells('C5:D5');
        $sheet->setCellValue('C5', 'TOTAL PENGELUARAN');
        $sheet->getStyle('C5')->getFont()->setSize(9)->setBold(true)->getColor()->setRGB('9F1239');
        $sheet->getStyle('C5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFE4E6');

        $sheet->mergeCells('C6:D6');
        $sheet->setCellValue('C6', $totalExpense);
        $sheet->getStyle('C6')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('BE123C');
        $sheet->getStyle('C6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C6')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('C6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF1F2');

        $this->applyBoxBorder($sheet, 'C5:D6', 'FECDD3');

        // Card 3: SISA SALDO / TABUNGAN
        $sheet->mergeCells('E5:G5');
        $sheet->setCellValue('E5', 'SISA SALDO / TABUNGAN');
        $sheet->getStyle('E5')->getFont()->setSize(9)->setBold(true)->getColor()->setRGB('1E293B');
        $sheet->getStyle('E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');

        $sheet->mergeCells('E6:G6');
        $sheet->setCellValue('E6', $netBalance);
        $sheet->getStyle('E6')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB($netBalance >= 0 ? '0F172A' : 'DC2626');
        $sheet->getStyle('E6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E6')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('E6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');

        $this->applyBoxBorder($sheet, 'E5:G6', 'CBD5E1');

        // 3. Kategori Pengeluaran Table
        $sheet->setCellValue('A8', 'Kategori Pengeluaran');
        $sheet->setCellValue('B8', 'Total (Rp)');
        $sheet->setCellValue('C8', '% Dari Pengeluaran');

        $sheet->getStyle('A8:C8')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A8:C8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E293B');
        $sheet->getStyle('B8:C8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $categories = Transaction::EXPENSE_CATEGORIES;
        $catRow = 9;
        foreach ($categories as $catName => $meta) {
            $catTotal = (float) $transactions->where('type', 'Pengeluaran')->where('category', $catName)->sum('amount');
            $pct = $totalExpense > 0 ? ($catTotal / $totalExpense) : 0;

            $sheet->setCellValue('A' . $catRow, $catName);
            $sheet->setCellValue('B' . $catRow, $catTotal);
            $sheet->setCellValue('C' . $catRow, $pct);

            $sheet->getStyle('B' . $catRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('C' . $catRow)->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle('B' . $catRow . ':C' . $catRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Light gray alternate zebra
            if ($catRow % 2 === 0) {
                $sheet->getStyle('A' . $catRow . ':C' . $catRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $catRow++;
        }

        // Total Pengeluaran Summary Row
        $sheet->setCellValue('A' . $catRow, 'Total Pengeluaran');
        $sheet->setCellValue('B' . $catRow, $totalExpense);
        $sheet->setCellValue('C' . $catRow, $totalExpense > 0 ? 1 : 0);

        $sheet->getStyle('A' . $catRow . ':C' . $catRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $catRow . ':C' . $catRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet->getStyle('B' . $catRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('C' . $catRow)->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle('B' . $catRow . ':C' . $catRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $this->applyTableBorders($sheet, 'A8:C' . $catRow);

        // 4. Catatan Transaksi Harian Table
        $trxHeaderStart = $catRow + 2;
        $sheet->setCellValue('A' . $trxHeaderStart, 'Catatan Transaksi Harian (Pemasukan & Pengeluaran)');
        $sheet->getStyle('A' . $trxHeaderStart)->getFont()->setSize(12)->setBold(true)->getColor()->setRGB('0F172A');

        $trxSubtitleRow = $trxHeaderStart + 1;
        $sheet->setCellValue('A' . $trxSubtitleRow, 'Pilih Jenis Transaksi (Pemasukan / Pengeluaran) dan isi nominal pada kolom yang sesuai.');
        $sheet->getStyle('A' . $trxSubtitleRow)->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $tableHeaderRow = $trxSubtitleRow + 2;
        $headers = [
            'A' => 'Tanggal',
            'B' => 'Jenis Transaksi',
            'C' => 'Kategori',
            'D' => 'Deskripsi / Keperluan',
            'E' => 'Pemasukan (Rp)',
            'F' => 'Pengeluaran (Rp)',
            'G' => 'Metode / Akun',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $tableHeaderRow, $title);
        }

        $sheet->getStyle('A' . $tableHeaderRow . ':G' . $tableHeaderRow)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A' . $tableHeaderRow . ':G' . $tableHeaderRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
        $sheet->getStyle('A' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $tableHeaderRow . ':F' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $tableHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Transactions Rows
        $currentRow = $tableHeaderRow + 1;
        $dataStartRow = $currentRow;

        foreach ($transactions as $t) {
            $sheet->setCellValue('A' . $currentRow, $t->date->format('Y-m-d'));
            $sheet->setCellValue('B' . $currentRow, $t->type);
            $sheet->setCellValue('C' . $currentRow, $t->category);
            $sheet->setCellValue('D' . $currentRow, $t->description ?: '-');
            
            if ($t->type === 'Pemasukan') {
                $sheet->setCellValue('E' . $currentRow, $t->amount);
                $sheet->setCellValue('F' . $currentRow, '');
                $sheet->getStyle('E' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('E' . $currentRow)->getFont()->getColor()->setRGB('059669');
            } else {
                $sheet->setCellValue('E' . $currentRow, '');
                $sheet->setCellValue('F' . $currentRow, $t->amount);
                $sheet->getStyle('F' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('F' . $currentRow)->getFont()->getColor()->setRGB('E11D48');
            }

            $sheet->setCellValue('G' . $currentRow, $t->payment_method);

            // Alignments & formats
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $currentRow . ':F' . $currentRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('E' . $currentRow . ':F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Subtle color on type column
            if ($t->type === 'Pemasukan') {
                $sheet->getStyle('B' . $currentRow)->getFont()->getColor()->setRGB('047857');
            } else {
                $sheet->getStyle('B' . $currentRow)->getFont()->getColor()->setRGB('BE123C');
            }

            // Alternating zebra row shading
            if ($currentRow % 2 === 0) {
                $sheet->getStyle('A' . $currentRow . ':G' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $currentRow++;
        }

        // Empty placeholder row if no data
        if ($transactions->isEmpty()) {
            $sheet->mergeCells('A' . $currentRow . ':G' . $currentRow);
            $sheet->setCellValue('A' . $currentRow, 'Tidak ada transaksi pada periode ini.');
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $currentRow)->getFont()->setItalic(true)->getColor()->setRGB('94A3B8');
            $currentRow++;
        }

        // Bottom Summary Total Row (TOTAL KESELURUHAN)
        $totalRow = $currentRow;
        $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
        $sheet->setCellValue('A' . $totalRow, 'TOTAL KESELURUHAN:');
        $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true)->getColor()->setRGB('0F172A');

        $sheet->setCellValue('E' . $totalRow, $totalIncome);
        $sheet->setCellValue('F' . $totalRow, $totalExpense);
        $sheet->setCellValue('G' . $totalRow, 'Saldo: Rp ' . number_format($netBalance, 0, ',', '.'));

        $sheet->getStyle('E' . $totalRow)->getFont()->setBold(true)->getColor()->setRGB('047857');
        $sheet->getStyle('F' . $totalRow)->getFont()->setBold(true)->getColor()->setRGB('BE123C');
        $sheet->getStyle('G' . $totalRow)->getFont()->setBold(true)->getColor()->setRGB($netBalance >= 0 ? '0F172A' : 'DC2626');

        $sheet->getStyle('E' . $totalRow . ':F' . $totalRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('E' . $totalRow . ':F' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');

        // Apply grid borders
        $this->applyTableBorders($sheet, 'A' . $tableHeaderRow . ':G' . $totalRow);

        // Auto-fit column widths with pleasant padding
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Set row heights for visual breathing room
        $sheet->getRowDimension(1)->setRowHeight(24);
        $sheet->getRowDimension(5)->setRowHeight(18);
        $sheet->getRowDimension(6)->setRowHeight(26);
        $sheet->getRowDimension(8)->setRowHeight(22);
        $sheet->getRowDimension($tableHeaderRow)->setRowHeight(24);
        $sheet->getRowDimension($totalRow)->setRowHeight(24);

        $fileName = 'laporan-keuangan-' . ($selectedMonth ?: 'semua') . '.xlsx';

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ];

        return response()->stream(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, $headers);
    }

    private function applyBoxBorder($sheet, string $range, string $colorRgb): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($colorRgb);
    }

    private function applyTableBorders($sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
    }
}
