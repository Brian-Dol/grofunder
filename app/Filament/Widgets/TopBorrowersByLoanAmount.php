<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Models\Borrower;
use Filament\Widgets\BarChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopBorrowersByLoanAmount extends BarChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 5;

    public function getHeading(): string
    {
        return 'Top 10 Borrowers by Loan Amount';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'font' => ['size' => 9],
                    ],
                ],
                'y' => [
                    'ticks' => [
                        'font' => ['size' => 9],
                    ],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $topBorrowers = Loan::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->select('borrower_id')
            ->selectRaw('SUM(principal_amount) as total_amount')
            ->groupBy('borrower_id')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->with('borrower')
            ->get();

        $labels = $topBorrowers->map(function ($loan) {
            $borrower = $loan->borrower;
            $name = $borrower ? trim($borrower->first_name . ' ' . ($borrower->last_name ?? '')) : 'Unknown';
            return substr($name, 0, 15); // Truncate long names
        })->toArray();

        $amounts = $topBorrowers->pluck('total_amount')->map('floatval')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Loan Amount',
                    'data' => $amounts,
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
