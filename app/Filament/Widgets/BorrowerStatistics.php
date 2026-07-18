<?php

namespace App\Filament\Widgets;

use App\Models\Borrower;
use Carbon\Carbon;
use Filament\Widgets\BarChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class BorrowerStatistics extends BarChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 4;

    public function getHeading(): string
    {
        return 'Borrower Statistics';
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
                    'beginAtZero' => true,
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
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        // Count total borrowers
        $totalBorrowers = Borrower::query()
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->count();

        // Get borrower count by month
        $borrowersByMonth = [];
        $monthLabels = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::now()->subMonths(12 - $month)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            
            $count = Borrower::query()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            
            $borrowersByMonth[] = $count;
            $monthLabels[] = $monthStart->format('M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Borrowers Added',
                    'data' => $borrowersByMonth,
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#1e40af',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $monthLabels,
        ];
    }
}
