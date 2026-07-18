<?php

namespace App\Filament\Pages;

use App\Services\ReportService;
use App\Services\ReportExportService;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class AgentReportPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Performance Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.agent-report-page';
    protected static ?string $title = 'Agent Performance Report';

    public ?Carbon $startDate = null;
    public ?Carbon $endDate = null;
    public array $reportData = [];

    protected ReportService $reportService;
    protected ReportExportService $exportService;

    public function mount(): void
    {
        if (!auth()->user()->hasRole('agent') || !auth()->user()->cooperative_id) {
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
            $cooperativeId = auth()->user()->cooperative_id;

            $this->reportData = [
                'cooperative' => auth()->user()->cooperative,
                'loans' => $this->reportService->getLoanMetrics($this->startDate, $this->endDate, $cooperativeId),
                'repayments' => $this->reportService->getRepaymentMetrics($this->startDate, $this->endDate, $cooperativeId),
                'mpesa' => $this->reportService->getMpesaMetrics($this->startDate, $this->endDate, $cooperativeId),
                'borrowers' => $this->reportService->getBorrowerMetrics($cooperativeId),
                'revenue' => $this->reportService->getRevenueMetrics($this->startDate, $this->endDate, $cooperativeId),
                'trends' => $this->reportService->getMonthlyTrends(12, $cooperativeId),
                'topBorrowers' => $this->reportService->getTopBorrowers(5, $cooperativeId),
                'atRiskBorrowers' => $this->reportService->getBorrowersAtRisk($cooperativeId),
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
            return $this->exportService->exportAgentReportToPdf(
                auth()->user()->cooperative_id,
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
            return $this->exportService->exportAgentReportToCsv(
                auth()->user()->cooperative_id,
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
