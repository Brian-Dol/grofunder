<?php

namespace App\Mail;

use App\Models\Cooperative;
use App\Services\ReportService;
use App\Services\ReportExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class AgentPerformanceReportMail extends Mailable
{
    use Queueable, SerializesModels;

    private ReportService $reportService;
    private ReportExportService $exportService;
    private Cooperative $cooperative;
    private Carbon $startDate;
    private Carbon $endDate;

    /**
     * Create a new message instance.
     */
    public function __construct(Cooperative $cooperative, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $this->cooperative = $cooperative;
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
            subject: "Performance Report - {$this->cooperative->name} ({$this->startDate->format('M Y')} to {$this->endDate->format('M Y')})"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Get report data
        $reportData = [
            'cooperative_name' => $this->cooperative->name,
            'period' => $this->startDate->format('M d, Y') . ' - ' . $this->endDate->format('M d, Y'),
            'generated_at' => now()->format('M d, Y H:i:s'),
            'loan_metrics' => $this->reportService->getLoanMetrics($this->startDate, $this->endDate, $this->cooperative->id),
            'repayment_metrics' => $this->reportService->getRepaymentMetrics($this->startDate, $this->endDate, $this->cooperative->id),
            'mpesa_metrics' => $this->reportService->getMpesaMetrics($this->startDate, $this->endDate, $this->cooperative->id),
            'borrower_metrics' => $this->reportService->getBorrowerMetrics($this->cooperative->id),
            'revenue_metrics' => $this->reportService->getRevenueMetrics($this->startDate, $this->endDate, $this->cooperative->id),
            'top_borrowers' => $this->reportService->getTopBorrowers(10, $this->cooperative->id),
        ];

        return new Content(
            view: 'mail.agent-performance-report',
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
            $pdf = $this->exportService->generateAgentPdfContent($this->cooperative->id, $this->startDate, $this->endDate);
            
            return [
                Attachment::fromData(
                    fn () => $pdf,
                    "performance_report_{$this->cooperative->name}_{$this->startDate->format('Y_m_d')}.pdf"
                )->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            // Log error but don't fail email
            \Illuminate\Support\Facades\Log::error('PDF generation failed for agent report', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
