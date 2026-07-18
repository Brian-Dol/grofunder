<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>System Report - Growfunder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
            font-size: 12px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section h2 {
            background-color: #2c3e50;
            color: white;
            padding: 10px;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table th {
            background-color: #ecf0f1;
            border: 1px solid #bdc3c7;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            border: 1px solid #ecf0f1;
            padding: 8px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .metric-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #ecf0f1;
        }
        .metric-label {
            font-weight: bold;
            color: #2c3e50;
        }
        .metric-value {
            text-align: right;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ecf0f1;
            font-size: 11px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GROWFUNDER - System-Wide Analytics Report</h1>
        <p>Report Period: {{ $period }}</p>
        <p>Generated: {{ $generated_at }}</p>
    </div>

    {{-- System-Wide Loan Metrics --}}
    <div class="section">
        <h2>System-Wide Loan Metrics</h2>
        <div class="metric-row">
            <span class="metric-label">Total Loans:</span>
            <span class="metric-value">{{ $loan_metrics['total_loans'] }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Total Disbursed:</span>
            <span class="metric-value">ZMW {{ number_format($loan_metrics['total_disbursed'], 2) }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Active Loans:</span>
            <span class="metric-value">{{ $loan_metrics['active_loans'] }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Completed Loans:</span>
            <span class="metric-value">{{ $loan_metrics['completed_loans'] }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Defaulted Loans:</span>
            <span class="metric-value">{{ $loan_metrics['defaulted_loans'] }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Outstanding Balance:</span>
            <span class="metric-value">ZMW {{ number_format($loan_metrics['total_outstanding'], 2) }}</span>
        </div>
    </div>

    {{-- Repayment Performance --}}
    <div class="section">
        <h2>Repayment Performance</h2>
        <div class="metric-row">
            <span class="metric-label">Total Repayments:</span>
            <span class="metric-value">{{ $repayment_metrics['total_repayments'] }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Total Amount Repaid:</span>
            <span class="metric-value">ZMW {{ number_format($repayment_metrics['total_amount_repaid'], 2) }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">On-Time Rate:</span>
            <span class="metric-value"><span class="highlight">{{ $repayment_metrics['on_time_rate'] }}%</span></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Average Repayment:</span>
            <span class="metric-value">ZMW {{ number_format($repayment_metrics['average_repayment'], 2) }}</span>
        </div>
    </div>

    {{-- M-Pesa Analytics --}}
    <div class="section">
        <h2>M-Pesa Payment Analytics</h2>
        <div class="metric-row">
            <span class="metric-label">Total M-Pesa Transactions:</span>
            <span class="metric-value">{{ $mpesa_metrics['total_mpesa_transactions'] }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Total M-Pesa Amount:</span>
            <span class="metric-value">ZMW {{ number_format($mpesa_metrics['total_mpesa_amount'], 2) }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Success Rate:</span>
            <span class="metric-value"><span class="highlight">{{ $mpesa_metrics['success_rate'] }}%</span></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Completed:</span>
            <span class="metric-value">{{ $mpesa_metrics['completed_transactions'] }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Failed:</span>
            <span class="metric-value">{{ $mpesa_metrics['failed_transactions'] }}</span>
        </div>
    </div>

    {{-- Borrower Metrics --}}
    <div class="section">
        <h2>Borrower Metrics</h2>
        <div class="metric-row">
            <span class="metric-label">Total Borrowers:</span>
            <span class="metric-value">{{ $borrower_metrics['total_borrowers'] }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Active Borrowers:</span>
            <span class="metric-value">{{ $borrower_metrics['active_borrowers'] }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Inactive Borrowers:</span>
            <span class="metric-value">{{ $borrower_metrics['inactive_borrowers'] }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Repeat Borrowers:</span>
            <span class="metric-value">{{ $borrower_metrics['repeat_borrowers'] }}</span>
        </div>
    </div>

    {{-- Revenue Metrics --}}
    <div class="section">
        <h2>Revenue & Collection</h2>
        <div class="metric-row">
            <span class="metric-label">Total Interest Earned:</span>
            <span class="metric-value">ZMW {{ number_format($revenue_metrics['total_interest_earned'], 2) }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Expected Repayments:</span>
            <span class="metric-value">ZMW {{ number_format($revenue_metrics['expected_repayments'], 2) }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Actual Repayments:</span>
            <span class="metric-value">ZMW {{ number_format($revenue_metrics['actual_repayments'], 2) }}</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Collection Rate:</span>
            <span class="metric-value"><span class="highlight">{{ $revenue_metrics['collection_rate'] }}%</span></span>
        </div>
    </div>

    {{-- Cooperatives Breakdown --}}
    <div class="section">
        <h2>Cooperatives Performance Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Cooperative</th>
                    <th style="text-align: center;">Region</th>
                    <th style="text-align: right;">Borrowers</th>
                    <th style="text-align: right;">Total Loans</th>
                    <th style="text-align: right;">Disbursed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cooperatives as $coop)
                    <tr>
                        <td>{{ $coop->name }}</td>
                        <td style="text-align: center;">{{ $coop->region ?? 'N/A' }}</td>
                        <td style="text-align: right;">{{ $coop->total_borrowers ?? 0 }}</td>
                        <td style="text-align: right;">{{ $coop->total_loans ?? 0 }}</td>
                        <td style="text-align: right;">ZMW {{ number_format($coop->total_disbursed ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No cooperative data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This is an automatically generated report from Growfunder Loan Management System.</p>
        <p>For questions, please contact your administrator.</p>
    </div>
</body>
</html>
