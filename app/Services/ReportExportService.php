<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate agent PDF content as string (for email attachment)
     */
    public function generateAgentPdfContent($cooperativeId, $startDate = null, $endDate = null): string
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subMonths(12);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $cooperative = \App\Models\Cooperative::find($cooperativeId);

        $data = [
            'cooperative' => $cooperative,
            'period' => $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
            'loan_metrics' => $this->reportService->getLoanMetrics($startDate, $endDate, $cooperativeId),
            'repayment_metrics' => $this->reportService->getRepaymentMetrics($startDate, $endDate, $cooperativeId),
            'mpesa_metrics' => $this->reportService->getMpesaMetrics($startDate, $endDate, $cooperativeId),
            'borrower_metrics' => $this->reportService->getBorrowerMetrics($cooperativeId),
            'revenue_metrics' => $this->reportService->getRevenueMetrics($startDate, $endDate, $cooperativeId),
            'generated_at' => now()->format('F d, Y H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.agent-report', $data);
        return $pdf->output();
    }

    /**
     * Generate system PDF content as string (for email attachment)
     */
    public function generateSystemPdfContent($startDate = null, $endDate = null): string
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subMonths(12);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $data = [
            'period' => $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
            'loan_metrics' => $this->reportService->getLoanMetrics($startDate, $endDate),
            'repayment_metrics' => $this->reportService->getRepaymentMetrics($startDate, $endDate),
            'mpesa_metrics' => $this->reportService->getMpesaMetrics($startDate, $endDate),
            'borrower_metrics' => $this->reportService->getBorrowerMetrics(),
            'revenue_metrics' => $this->reportService->getRevenueMetrics($startDate, $endDate),
            'cooperatives' => $this->reportService->getCooperativesBreakdown(),
            'generated_at' => now()->format('F d, Y H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.system-report', $data);
        return $pdf->output();
    }

    /**
     * Export agent report to PDF
     */
    public function exportAgentReportToPdf($cooperativeId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subMonths(12);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $cooperative = \App\Models\Cooperative::find($cooperativeId);

        $data = [
            'cooperative' => $cooperative,
            'period' => $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
            'loan_metrics' => $this->reportService->getLoanMetrics($startDate, $endDate, $cooperativeId),
            'repayment_metrics' => $this->reportService->getRepaymentMetrics($startDate, $endDate, $cooperativeId),
            'mpesa_metrics' => $this->reportService->getMpesaMetrics($startDate, $endDate, $cooperativeId),
            'borrower_metrics' => $this->reportService->getBorrowerMetrics($cooperativeId),
            'revenue_metrics' => $this->reportService->getRevenueMetrics($startDate, $endDate, $cooperativeId),
            'generated_at' => now()->format('F d, Y H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.agent-report', $data);
        
        $filename = 'agent-report-' . $cooperative->name . '-' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export system report to PDF
     */
    public function exportSystemReportToPdf($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subMonths(12);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $data = [
            'period' => $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
            'loan_metrics' => $this->reportService->getLoanMetrics($startDate, $endDate),
            'repayment_metrics' => $this->reportService->getRepaymentMetrics($startDate, $endDate),
            'mpesa_metrics' => $this->reportService->getMpesaMetrics($startDate, $endDate),
            'borrower_metrics' => $this->reportService->getBorrowerMetrics(),
            'revenue_metrics' => $this->reportService->getRevenueMetrics($startDate, $endDate),
            'cooperatives' => $this->reportService->getCooperativesBreakdown(),
            'generated_at' => now()->format('F d, Y H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.system-report', $data);
        
        $filename = 'system-report-' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export borrower performance to PDF
     */
    public function exportBorrowerReportToPdf($borrowerId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subMonths(24);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $borrower = \App\Models\Borrower::with('loans.repayments', 'cooperative')->find($borrowerId);

        if (!$borrower) {
            throw new \Exception('Borrower not found');
        }

        // Get loans within date range
        $loans = $borrower->loans()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('repayments')
            ->get();

        $data = [
            'borrower' => $borrower,
            'period' => $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
            'loans' => $loans,
            'total_borrowed' => $loans->sum('principal_amount'),
            'total_repaid' => $loans->sum(function ($loan) {
                return $loan->repayments()->sum('payments');
            }),
            'active_loans' => $loans->where('loan_status', 'approved')->count(),
            'completed_loans' => $loans->where('loan_status', 'completed')->count(),
            'defaulted_loans' => $loans->where('loan_status', 'defaulted')->count(),
            'generated_at' => now()->format('F d, Y H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.borrower-report', $data);
        
        $filename = 'borrower-report-' . $borrower->name . '-' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export investor portfolio to PDF
     */
    public function exportInvestorReportToPdf($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subMonths(12);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $data = [
            'period' => $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
            'portfolio_stats' => [
                'total_invested' => \App\Models\Loan::whereStatus('approved')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('principal_amount'),
                'active_loans' => \App\Models\Loan::where('loan_status', 'approved')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'completed_loans' => \App\Models\Loan::where('loan_status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'defaulted_loans' => \App\Models\Loan::where('loan_status', 'defaulted')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
            ],
            'cooperatives' => $this->reportService->getCooperativesBreakdown(),
            'generated_at' => now()->format('F d, Y H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.investor-report', $data);
        
        $filename = 'investor-portfolio-' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export agent report to Excel/CSV
     */
    public function exportAgentReportToCsv($cooperativeId, $startDate = null, $endDate = null): StreamedResponse
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subMonths(12);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $cooperative = \App\Models\Cooperative::find($cooperativeId);

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=agent-report-' . $cooperative->name . '-' . now()->format('Y-m-d') . '.csv',
        ];

        return response()->streamDownload(function () use ($startDate, $endDate, $cooperativeId, $cooperative) {
            $output = fopen('php://output', 'w');

            // Report header
            fputcsv($output, ['Agent Report: ' . $cooperative->name]);
            fputcsv($output, ['Report Period: ' . $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y')]);
            fputcsv($output, ['Generated: ' . now()->format('F d, Y H:i:s')]);
            fputcsv($output, []);

            // Loan Metrics
            $loanMetrics = $this->reportService->getLoanMetrics($startDate, $endDate, $cooperativeId);
            fputcsv($output, ['LOAN METRICS']);
            fputcsv($output, ['Total Loans', 'Total Disbursed', 'Active Loans', 'Completed', 'Defaulted', 'Outstanding Balance']);
            fputcsv($output, [
                $loanMetrics['total_loans'],
                $loanMetrics['total_disbursed'],
                $loanMetrics['active_loans'],
                $loanMetrics['completed_loans'],
                $loanMetrics['defaulted_loans'],
                $loanMetrics['total_outstanding'],
            ]);
            fputcsv($output, []);

            // Repayment Metrics
            $repaymentMetrics = $this->reportService->getRepaymentMetrics($startDate, $endDate, $cooperativeId);
            fputcsv($output, ['REPAYMENT METRICS']);
            fputcsv($output, ['Total Repayments', 'Total Amount', 'On-Time Rate', 'Overdue', 'Average Repayment']);
            fputcsv($output, [
                $repaymentMetrics['total_repayments'],
                $repaymentMetrics['total_amount_repaid'],
                $repaymentMetrics['on_time_rate'] . '%',
                $repaymentMetrics['overdue_repayments'],
                $repaymentMetrics['average_repayment'],
            ]);
            fputcsv($output, []);

            // M-Pesa Metrics
            $mpesaMetrics = $this->reportService->getMpesaMetrics($startDate, $endDate, $cooperativeId);
            fputcsv($output, ['M-PESA METRICS']);
            fputcsv($output, ['Total Transactions', 'Total Amount', 'Success Rate', 'Failed', 'Pending']);
            fputcsv($output, [
                $mpesaMetrics['total_mpesa_transactions'],
                $mpesaMetrics['total_mpesa_amount'],
                $mpesaMetrics['success_rate'] . '%',
                $mpesaMetrics['failed_transactions'],
                $mpesaMetrics['pending_transactions'],
            ]);
            fputcsv($output, []);

            // Borrower Metrics
            $borrowerMetrics = $this->reportService->getBorrowerMetrics($cooperativeId);
            fputcsv($output, ['BORROWER METRICS']);
            fputcsv($output, ['Total Borrowers', 'Active', 'Inactive', 'Repeat Borrowers']);
            fputcsv($output, [
                $borrowerMetrics['total_borrowers'],
                $borrowerMetrics['active_borrowers'],
                $borrowerMetrics['inactive_borrowers'],
                $borrowerMetrics['repeat_borrowers'],
            ]);
            fputcsv($output, []);

            // Revenue Metrics
            $revenueMetrics = $this->reportService->getRevenueMetrics($startDate, $endDate, $cooperativeId);
            fputcsv($output, ['REVENUE METRICS']);
            fputcsv($output, ['Total Interest Earned', 'Collection Rate', 'Expected Repayments', 'Actual Repayments']);
            fputcsv($output, [
                $revenueMetrics['total_interest_earned'],
                $revenueMetrics['collection_rate'] . '%',
                $revenueMetrics['expected_repayments'],
                $revenueMetrics['actual_repayments'],
            ]);

            fclose($output);
        }, 'agent-report-' . $cooperative->name . '-' . now()->format('Y-m-d') . '.csv', $headers);
    }

    /**
     * Export system report to Excel/CSV
     */
    public function exportSystemReportToCsv($startDate = null, $endDate = null): StreamedResponse
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subMonths(12);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=system-report-' . now()->format('Y-m-d') . '.csv',
        ];

        return response()->streamDownload(function () use ($startDate, $endDate) {
            $output = fopen('php://output', 'w');

            // Report header
            fputcsv($output, ['GROWFUNDER SYSTEM REPORT']);
            fputcsv($output, ['Report Period: ' . $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y')]);
            fputcsv($output, ['Generated: ' . now()->format('F d, Y H:i:s')]);
            fputcsv($output, []);

            // System Metrics
            $loanMetrics = $this->reportService->getLoanMetrics($startDate, $endDate);
            fputcsv($output, ['SYSTEM-WIDE METRICS']);
            fputcsv($output, ['Total Loans', 'Total Disbursed', 'Active Loans', 'Outstanding Balance']);
            fputcsv($output, [
                $loanMetrics['total_loans'],
                $loanMetrics['total_disbursed'],
                $loanMetrics['active_loans'],
                $loanMetrics['total_outstanding'],
            ]);
            fputcsv($output, []);

            // Cooperatives Breakdown
            fputcsv($output, ['COOPERATIVES BREAKDOWN']);
            fputcsv($output, ['Cooperative', 'Total Borrowers', 'Total Loans', 'Total Disbursed']);

            $cooperatives = $this->reportService->getCooperativesBreakdown();
            foreach ($cooperatives as $coop) {
                fputcsv($output, [
                    $coop->name,
                    $coop->total_borrowers,
                    $coop->total_loans,
                    $coop->total_disbursed,
                ]);
            }

            fclose($output);
        }, 'system-report-' . now()->format('Y-m-d') . '.csv', $headers);
    }
}
