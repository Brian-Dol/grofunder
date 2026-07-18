<?php

namespace App\Filament\Widgets;

use App\Models\Repayments;
use Carbon\Carbon;
use Filament\Widgets\LineChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class MonthlyRepaymentTrends extends LineChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Monthly Repayment Trends';
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
                        'font' => ['size' => 11],
                        'padding' => 10,
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'font' => ['size' => 10],
                    ],
                ],
                'x' => [
                    'ticks' => [
                        'font' => ['size' => 10],
                    ],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? Carbon::now()->startOfYear();
        $endDate = $this->filters['endDate'] ?? Carbon::now()->endOfYear();

        $repaymentData = [];
        $principalData = [];

        for ($month = 1; $month <= 12; $month++) {
            $repaymentData[] = Repayments::query()
                ->whereMonth('repayment_date', $month)
                ->whereYear('repayment_date', now()->year)
                ->sum('payments');

            $principalData[] = Repayments::query()
                ->whereMonth('repayment_date', $month)
                ->whereYear('repayment_date', now()->year)
                ->sum('principal');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Repayments',
                    'data' => array_map('floatval', $repaymentData),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Principal',
                    'data' => array_map('floatval', $principalData),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
}
