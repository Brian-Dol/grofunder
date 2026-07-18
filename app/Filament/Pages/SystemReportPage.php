<?php

namespace App\Filament\Pages;

use App\Services\ReportService;
use App\Services\ReportExportService;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class SystemReportPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'System Analytics';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.system-report-page';
    protected static ?string $title = 'System-Wide Analytics';

    public ?Carbon $startDate = null;
    public ?Carbon $endDate = null;
    public array $reportData = [];

    protected ReportService $reportService;
    protected ReportExportService $exportService;

    public function mount(): void
    {
        // Only super admins and admins can view
        if (!auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('admin')) {
            redirect()->route('filament.admin.pages.dashboard');
            return;
        }

        // Default to last 12 months
        $this->startDate = now()->subMonths(12);
        $this->endDate = now();

        $this->reportService = app(ReportService::class);
        $this->exportService = app(ReportExportService::class);

        $this->generateReport();
    }

    public function generateReport(): void
    {
        try {
            $this->reportData = [
                'loans' => $this->reportService->getLoanMetrics($this->startDate, $this->endDate),
                'repayments' => $this->reportService->getRepaymentMetrics($this->startDate, $this->endDate),
                'mpesa' => $this->reportService->getMpesaMetrics($this->startDate, $this->endDate),
                'borrowers' => $this->reportService->getBorrowerMetrics(),
                'revenue' => $this->reportService->getRevenueMetrics($this->startDate, $this->endDate),
                'cooperatives' => $this->reportService->getCooperativesBreakdown(),
                'trends' => $this->reportService->getMonthlyTrends(12),
                'topBorrowers' => $this->reportService->getTopBorrowers(10),
                'loansByStatus' => $this->reportService->getLoansByStatus(),
                'period' => $this->startDate->format('M d, Y') . ' - ' . $this->endDate->format('M d, Y'),
            ];
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Report Generation Failed')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function exportPdf(): mixed
    {
        try {
            return $this->exportService->exportSystemReportToPdf(
                $this->startDate,
                $this->endDate
            );
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Export Failed')
                ->body($e->getMessage())
                ->send();
            return null;
        }
    }

    public function exportCsv(): mixed
    {
        try {
            return $this->exportService->exportSystemReportToCsv(
                $this->startDate,
                $this->endDate
            );
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Export Failed')
                ->body($e->getMessage())
                ->send();
            return null;
        }
    }
}
