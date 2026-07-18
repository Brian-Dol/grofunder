<x-mail::message>
# Your Investment Portfolio Report

Hello {{ $investor_name }},

Please find your investment portfolio summary for **{{ $period }}** below.

---

## Portfolio Summary

| Metric | Value |
|--------|-------|
| Total Invested | ZMW {{ number_format($total_invested, 2) }} |
| Active Loans | {{ $active_loans }} |
| Completed Loans | {{ $completed_loans }} |
| Defaulted Loans | {{ $defaulted_loans }} |
| Total Repayments Received | {{ $total_repayments }} |

## Cooperatives in Your Portfolio

@if (!empty($cooperatives))
| Cooperative | Borrowers | Total Loans | Disbursed |
|-------------|-----------|-------------|-----------|
@foreach ($cooperatives as $coop)
| {{ $coop->name }} | {{ $coop->total_borrowers ?? 0 }} | {{ $coop->total_loans ?? 0 }} | ZMW {{ number_format($coop->total_disbursed ?? 0, 2) }} |
@endforeach
@else
No cooperative data available.
@endif

---

**Report Generated:** {{ $generated_at }}

We appreciate your investment in Growfunder. For more details about your portfolio performance, please log in to your investor dashboard.

<x-mail::button :url="config('app.url')">
View Your Portfolio
</x-mail::button>

Best regards,  
**Growfunder Team**

---

*This is an automated report. Please do not reply to this email.*
