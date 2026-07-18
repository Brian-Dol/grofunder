<?php

namespace App\Filament\Widgets;

use App\Models\Repayments;
use App\Models\Expense;
use Filament\Widgets\BarChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class RevenueVsExpenses extends BarChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 7;

    public function getHeading(): string
    {
        return 'Revenue vs Expenses';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'labels' => [
                        'boxWidth' => 12,
                        'font' => ['size' => 10],
                        'padding' => 8,
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'font' => ['size' => 9],
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'font' => ['size' => 9],
                    ],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $revenueData = [];
        $expenseData = [];

        // Calculate monthly revenue (repayments) and expenses
        for ($month = 1; $month <= 12; $month++) {
            $revenue = Repayments::query()
                ->whereMonth('repayment_date', $month)
                ->whereYear('repayment_date', now()->year)
                ->sum('payments');

            // Get all expenses for the month and sum them
            $expenses = Expense::all();
            $expense = 0;
            foreach ($expenses as $exp) {
                $expDate = \Carbon\Carbon::parse($exp->expense_date);
                if ($expDate->month == $month && $expDate->year == now()->year) {
                    $expense += (float) $exp->expense_amount;
                }
            }

            $revenueData[] = $revenue;
            $expenseData[] = $expense;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Repayments)',
                    'data' => array_map('floatval', $revenueData),
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => 'Expenses',
                    'data' => array_map('floatval', $expenseData),
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
