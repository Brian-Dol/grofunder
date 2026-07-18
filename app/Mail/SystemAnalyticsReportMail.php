<?php

namespace App\Mail;

use App\Services\ReportService;
use App\Services\ReportExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SystemAnalyticsReportMail extends Mailable
{
    use Queueable, SerializesModels;

    private ReportService $reportService;
    private ReportExportService $exportService;
    private Carbon $startDate;
    private Carbon $endDate;

    /**
     * Create a new message instance.
     */
    public function __construct(?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $this->startDate = $startDate ?? Carbon::now()->subMonths(1)->startOfMonth();
        $this->endDate = $endDate ?? Carbon::now()->endOfMonth();
        $this->reportService = new ReportService();
        $this->exportService = new ReportExportService();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "System-Wide Analytics Report ({$this->startDate->format('M Y')} to {$this->endDate->format('M Y')})"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Get report data
        $reportData = [
            'period' => $this->startDate->format('M d, Y') . ' - ' . $this->endDate->format('M d, Y'),
            'generated_at' => now()->format('M d, Y H:i:s'),
            'loan_metrics' => $this->reportService->getLoanMetrics($this->startDate, $this->endDate),
            'repayment_metrics' => $this->reportService->getRepaymentMetrics($this->startDate, $this->endDate),
            'mpesa_metrics' => $this->reportService->getMpesaMetrics($this->startDate, $this->endDate),
            'borrower_metrics' => $this->reportService->getBorrowerMetrics(),
            'revenue_metrics' => $this->reportService->getRevenueMetrics($this->startDate, $this->endDate),
            'cooperatives_breakdown' => $this->reportService->getCooperativesBreakdown(),
            'monthly_trends' => $this->reportService->getMonthlyTrends(12),
        ];

        return new Content(
            view: 'mail.system-analytics-report',
            with: $reportData,
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        try {
            // Generate PDF in memory
            $pdf = $this->exportService->generateSystemPdfContent($this->startDate, $this->endDate);
            
            return [
                Attachment::fromData(
                    fn () => $pdf,
                    "system_analytics_{$this->startDate->format('Y_m_d')}.pdf"
                )->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            // Log error but don't fail email
            \Illuminate\Support\Facades\Log::error('PDF generation failed for system report', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
