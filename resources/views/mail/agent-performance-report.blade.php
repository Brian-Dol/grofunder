<x-mail::message>
# Performance Report - {{ $cooperative_name }}

Dear Agent,

Please find below your cooperative's performance summary for **{{ $period }}**.

---

## Loan Metrics

| Metric | Value |
|--------|-------|
| Total Loans | {{ $loan_metrics['total_loans'] }} |
| Total Disbursed | ZMW {{ number_format($loan_metrics['total_disbursed'], 2) }} |
| Active Loans | {{ $loan_metrics['active_loans'] }} |
| Completed Loans | {{ $loan_metrics['completed_loans'] }} |
| Defaulted Loans | {{ $loan_metrics['defaulted_loans'] }} |
| Outstanding Balance | ZMW {{ number_format($loan_metrics['total_outstanding'], 2) }} |

## Repayment Performance

| Metric | Value |
|--------|-------|
| Total Repayments | {{ $repayment_metrics['total_repayments'] }} |
| Total Amount Repaid | ZMW {{ number_format($repayment_metrics['total_amount_repaid'], 2) }} |
| On-Time Rate | **{{ $repayment_metrics['on_time_rate'] }}%** |
| Overdue Repayments | {{ $repayment_metrics['overdue_count'] }} |
| M-Pesa Payments | {{ $repayment_metrics['mpesa_payments'] }} |

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
| Inactive Borrowers | {{ $borrower_metrics['inactive_borrowers'] }} |

## Revenue & Collection

| Metric | Value |
|--------|-------|
| Total Interest Earned | ZMW {{ number_format($revenue_metrics['total_interest_earned'], 2) }} |
| Expected Repayments | ZMW {{ number_format($revenue_metrics['expected_repayments'], 2) }} |
| Actual Repayments | ZMW {{ number_format($revenue_metrics['actual_repayments'], 2) }} |
| Collection Rate | **{{ $revenue_metrics['collection_rate'] }}%** |

## Top 10 Performing Borrowers

@if (!empty($top_borrowers))
| Borrower | Completed Loans | Total Borrowed |
|----------|-----------------|-----------------|
@foreach ($top_borrowers as $borrower)
| {{ $borrower->name }} | {{ $borrower->completed_loans_count }} | ZMW {{ number_format($borrower->total_borrowed, 2) }} |
@endforeach
@else
No borrower data available.
@endif

---

**Report Generated:** {{ $generated_at }}

For more detailed information, please log in to your Growfunder dashboard or see the attached PDF report.

<x-mail::button :url="config('app.url')">
View Dashboard
</x-mail::button>

Best regards,  
**Growfunder Team**

---

*This is an automated report. Please do not reply to this email.*
