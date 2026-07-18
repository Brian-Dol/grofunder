<?php

namespace App\Mail;

use App\Models\User;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class InvestorPortfolioReportMail extends Mailable
{
    use Queueable, SerializesModels;

    private ReportService $reportService;
    private User $investor;
    private Carbon $startDate;
    private Carbon $endDate;

    /**
     * Create a new message instance.
     */
    public function __construct(User $investor, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $this->investor = $investor;
        $this->startDate = $startDate ?? Carbon::now()->subMonths(1)->startOfMonth();
        $this->endDate = $endDate ?? Carbon::now()->endOfMonth();
        $this->reportService = new ReportService();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Investment Portfolio Report ({$this->startDate->format('M Y')} to {$this->endDate->format('M Y')})"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Get portfolio metrics
        $loans = \App\Models\Loan::query()
            ->with('borrower')
            ->withCount(['payments' => fn($q) => $q->whereBetween('payment_date', [$this->startDate, $this->endDate])])
            ->get();

        $reportData = [
            'investor_name' => $this->investor->name,
            'period' => $this->startDate->format('M d, Y') . ' - ' . $this->endDate->format('M d, Y'),
            'generated_at' => now()->format('M d, Y H:i:s'),
            'total_invested' => $loans->sum('principal_amount'),
            'active_loans' => $loans->where('loan_status', 'active')->count(),
            'completed_loans' => $loans->where('loan_status', 'completed')->count(),
            'defaulted_loans' => $loans->where('loan_status', 'defaulted')->count(),
            'cooperatives' => $this->reportService->getCooperativesBreakdown(),
            'total_repayments' => $loans->sum('payments_count'),
        ];

        return new Content(
            view: 'mail.investor-portfolio-report',
            with: $reportData,
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
