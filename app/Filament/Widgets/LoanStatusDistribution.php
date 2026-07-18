<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class LoanStatusDistribution extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 6;

    public function getHeading(): string
    {
        return 'Loan Status Distribution';
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
        return 'pie';
    }

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $approved = Loan::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('loan_status', 'approved')
            ->count();

        $processing = Loan::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('loan_status', 'processing')
            ->count();

        $partiallyPaid = Loan::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('loan_status', 'partially_paid')
            ->count();

        $fullPaid = Loan::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('loan_status', 'full_paid')
            ->count();

        $defaulted = Loan::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->where('loan_status', 'defaulted')
            ->count();

        return [
            'datasets' => [
                [
                    'data' => [
                        $approved,
                        $processing,
                        $partiallyPaid,
                        $fullPaid,
                        $defaulted,
                    ],
                    'backgroundColor' => [
                        '#3b82f6',
                        '#f59e0b',
                        '#8b5cf6',
                        '#10b981',
                        '#ef4444',
                    ],
                ],
            ],
            'labels' => ['Approved', 'Processing', 'Partially Paid', 'Fully Paid', 'Defaulted'],
        ];
    }
}
