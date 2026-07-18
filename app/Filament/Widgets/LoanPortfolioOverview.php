<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class LoanPortfolioOverview extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        return 'Loan Portfolio Overview';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'boxWidth' => 12,
                        'font' => ['size' => 10],
                        'padding' => 8,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $approved = Loan::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('loan_status', 'approved')
            ->sum('principal_amount');

        $partiallyPaid = Loan::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('loan_status', 'partially_paid')
            ->sum('principal_amount');

        $fullPaid = Loan::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('loan_status', 'full_paid')
            ->sum('principal_amount');

        $defaulted = Loan::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('loan_status', 'defaulted')
            ->sum('principal_amount');

        return [
            'datasets' => [
                [
                    'data' => [
                        $approved,
                        $partiallyPaid,
                        $fullPaid,
                        $defaulted,
                    ],
                    'backgroundColor' => [
                        '#3b82f6',
                        '#f59e0b',
                        '#10b981',
                        '#ef4444',
                    ],
                ],
            ],
            'labels' => ['Active', 'Partially Paid', 'Fully Paid', 'Defaulted'],
        ];
    }
}
