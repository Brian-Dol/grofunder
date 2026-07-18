<x-mail::message>
# System-Wide Analytics Report

Dear Administrator,

Please find below the system-wide performance summary for **{{ $period }}**.

---

## System-Wide Loan Metrics

| Metric | Value |
|--------|-------|
| Total Loans | {{ $loan_metrics['total_loans'] }} |
| Total Disbursed | ZMW {{ number_format($loan_metrics['total_disbursed'], 2) }} |
| Active Loans | {{ $loan_metrics['active_loans'] }} |
| Completed Loans | {{ $loan_metrics['completed_loans'] }} |
| Defaulted Loans | {{ $loan_metrics['defaulted_loans'] }} |

## Repayment Performance

| Metric | Value |
|--------|-------|
| Total Repayments | {{ $repayment_metrics['total_repayments'] }} |
| Total Amount Repaid | ZMW {{ number_format($repayment_metrics['total_amount_repaid'], 2) }} |
| On-Time Rate | **{{ $repayment_metrics['on_time_rate'] }}%** |
| Average Repayment | ZMW {{ number_format($repayment_metrics['average_repayment'], 2) }} |

## M-Pesa Analytics

| Metric | Value |
|--------|-------|
| Total Transactions | {{ $mpesa_metrics['total_mpesa_transactions'] }} |
| Total Amount | ZMW {{ number_format($mpesa_metrics['total_mpesa_amount'], 2) }} |
| Success Rate | **{{ $mpesa_metrics['success_rate'] }}%** |
| Completed | {{ $mpesa_metrics['completed_transactions'] }} |
| Failed | {{ $mpesa_metrics['failed_transactions'] }} |

## Borrower Metrics

| Metric | Value |
|--------|-------|
| Total Borrowers | {{ $borrower_metrics['total_borrowers'] }} |
| Active Borrowers | {{ $borrower_metrics['active_borrowers'] }} |
| Repeat Borrowers | {{ $borrower_metrics['repeat_borrowers'] }} |

## Revenue & Collection

| Metric | Value |
|--------|-------|
| Total Interest Earned | ZMW {{ number_format($revenue_metrics['total_interest_earned'], 2) }} |
| Expected Repayments | ZMW {{ number_format($revenue_metrics['expected_repayments'], 2) }} |
| Actual Repayments | ZMW {{ number_format($revenue_metrics['actual_repayments'], 2) }} |
| Collection Rate | **{{ $revenue_metrics['collection_rate'] }}%** |

## Cooperatives Performance

@if (!empty($cooperatives_breakdown))
| Cooperative | Region | Borrowers | Loans | Disbursed |
|-------------|--------|-----------|-------|-----------|
@foreach ($cooperatives_breakdown as $coop)
| {{ $coop->name }} | {{ $coop->region ?? 'N/A' }} | {{ $coop->total_borrowers ?? 0 }} | {{ $coop->total_loans ?? 0 }} | ZMW {{ number_format($coop->total_disbursed ?? 0, 2) }} |
@endforeach
@else
No cooperative data available.
@endif

---

**Report Generated:** {{ $generated_at }}

For more detailed information, please log in to your Growfunder admin dashboard or see the attached PDF report.

<x-mail::button :url="config('app.url')">
View Admin Dashboard
</x-mail::button>

Best regards,  
**Growfunder Team**

---

*This is an automated report. Please do not reply to this email.*
