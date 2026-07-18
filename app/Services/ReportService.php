<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Repayments;
use App\Models\Borrower;
use App\Models\Cooperative;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Get loan metrics for a date range
     */
    public function getLoanMetrics($startDate = null, $endDate = null, $cooperativeId = null): array
    {
        $startDate = $startDate ?? now()->subMonths(12);
        $endDate = $endDate ?? now();

        $query = Loan::whereBetween('created_at', [$startDate, $endDate]);

        if ($cooperativeId) {
            $query->whereHas('borrower', function ($q) use ($cooperativeId) {
                $q->where('cooperative_id', $cooperativeId);
            });
        }

        $loans = $query->get();

        return [
            'total_loans' => $loans->count(),
            'total_disbursed' => $loans->sum('principal_amount'),
            'active_loans' => $loans->where('loan_status', 'approved')->count(),
            'completed_loans' => $loans->where('loan_status', 'completed')->count(),
            'defaulted_loans' => $loans->where('loan_status', 'defaulted')->count(),
            'pending_loans' => $loans->where('loan_status', 'pending')->count(),
            'total_outstanding' => $loans->sum('balance'),
            'average_loan_amount' => $loans->count() > 0 ? $loans->avg('principal_amount') : 0,
        ];
    }

    /**
     * Get repayment metrics for a date range
     */
    public function getRepaymentMetrics($startDate = null, $endDate = null, $cooperativeId = null): array
    {
        $startDate = $startDate ?? now()->subMonths(12);
        $endDate = $endDate ?? now();

        $query = Repayments::whereBetween('created_at', [$startDate, $endDate]);

        if ($cooperativeId) {
            $query->whereHas('loan.borrower', function ($q) use ($cooperativeId) {
                $q->where('cooperative_id', $cooperativeId);
            });
        }

        $repayments = $query->get();
        $totalRepayments = $repayments->count();

        // On-time payments (repaid on or before due date)
        $onTimePayments = $repayments->filter(function ($r) {
            return $r->repayment_date && $r->loan->due_date && 
                   $r->repayment_date <= $r->loan->due_date;
        })->count();

        // Overdue payments (repaid after due date)
        $overduePayments = $repayments->filter(function ($r) {
            return $r->repayment_date && $r->loan->due_date && 
                   $r->repayment_date > $r->loan->due_date;
        })->count();

        return [
            'total_repayments' => $totalRepayments,
            'total_amount_repaid' => $repayments->sum('payments'),
            'on_time_repayments' => $onTimePayments,
            'overdue_repayments' => $overduePayments,
            'on_time_rate' => $totalRepayments > 0 ? round(($onTimePayments / $totalRepayments) * 100, 2) : 0,
            'average_repayment' => $totalRepayments > 0 ? $repayments->avg('payments') : 0,
            'mpesa_payments' => $repayments->where('payments_method', 'M-Pesa')->count(),
            'mpesa_success_rate' => $this->getMpesaSuccessRate($cooperativeId, $startDate, $endDate),
        ];
    }

    /**
     * Get M-Pesa payment metrics
     */
    public function getMpesaMetrics($startDate = null, $endDate = null, $cooperativeId = null): array
    {
        $startDate = $startDate ?? now()->subMonths(12);
        $endDate = $endDate ?? now();

        $query = Repayments::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('mpesa_transaction_id');

        if ($cooperativeId) {
            $query->whereHas('loan.borrower', function ($q) use ($cooperativeId) {
                $q->where('cooperative_id', $cooperativeId);
            });
        }

        $mpesaPayments = $query->get();
        $totalMpesa = $mpesaPayments->count();

        return [
            'total_mpesa_transactions' => $totalMpesa,
            'total_mpesa_amount' => $mpesaPayments->sum('payments'),
            'completed_transactions' => $mpesaPayments->where('mpesa_status', 'completed')->count(),
            'failed_transactions' => $mpesaPayments->where('mpesa_status', 'failed')->count(),
            'pending_transactions' => $mpesaPayments->where('mpesa_status', 'pending')->count(),
            'success_rate' => $totalMpesa > 0 ? round(($mpesaPayments->where('mpesa_status', 'completed')->count() / $totalMpesa) * 100, 2) : 0,
            'average_mpesa_transaction' => $totalMpesa > 0 ? $mpesaPayments->avg('payments') : 0,
        ];
    }

    /**
     * Get borrower/customer metrics
     */
    public function getBorrowerMetrics($cooperativeId = null): array
    {
        $query = Borrower::query();

        if ($cooperativeId) {
            $query->where('cooperative_id', $cooperativeId);
        }

        $borrowers = $query->get();
        $totalBorrowers = $borrowers->count();

        // Active = has active loans
        $activeBorrowers = $borrowers->filter(function ($b) {
            return $b->loans()->where('loan_status', 'approved')->exists();
        })->count();

        return [
            'total_borrowers' => $totalBorrowers,
            'active_borrowers' => $activeBorrowers,
            'inactive_borrowers' => $totalBorrowers - $activeBorrowers,
            'new_borrowers_this_month' => Borrower::where('cooperative_id', $cooperativeId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'repeat_borrowers' => $borrowers->filter(function ($b) {
                return $b->loans()->count() > 1;
            })->count(),
        ];
    }

    /**
     * Get revenue metrics (interest and fees)
     */
    public function getRevenueMetrics($startDate = null, $endDate = null, $cooperativeId = null): array
    {
        $startDate = $startDate ?? now()->subMonths(12);
        $endDate = $endDate ?? now();

        $query = Loan::whereBetween('created_at', [$startDate, $endDate]);

        if ($cooperativeId) {
            $query->whereHas('borrower', function ($q) use ($cooperativeId) {
                $q->where('cooperative_id', $cooperativeId);
            });
        }

        $loans = $query->get();

        // Calculate total interest (principal * interest_rate)
        $totalInterest = $loans->sum(function ($loan) {
            return ($loan->principal_amount * ($loan->interest_rate / 100));
        });

        // Calculate expected vs actual repayments
        $expectedRepayments = $loans->sum(function ($loan) {
            return $loan->principal_amount + ($loan->principal_amount * ($loan->interest_rate / 100));
        });

        $actualRepayments = Repayments::whereBetween('created_at', [$startDate, $endDate])
            ->when($cooperativeId, function ($q) use ($cooperativeId) {
                return $q->whereHas('loan.borrower', function ($subQ) use ($cooperativeId) {
                    $subQ->where('cooperative_id', $cooperativeId);
                });
            })
            ->sum('payments');

        return [
            'total_interest_earned' => $totalInterest,
            'total_fees_collected' => 0, // Add if you have fee tracking
            'total_revenue' => $totalInterest,
            'expected_repayments' => $expectedRepayments,
            'actual_repayments' => $actualRepayments,
            'collection_rate' => $expectedRepayments > 0 ? round(($actualRepayments / $expectedRepayments) * 100, 2) : 0,
        ];
    }

    /**
     * Get loans by status breakdown
     */
    public function getLoansByStatus($cooperativeId = null): Collection
    {
        $query = Loan::query();

        if ($cooperativeId) {
            $query->whereHas('borrower', function ($q) use ($cooperativeId) {
                $q->where('cooperative_id', $cooperativeId);
            });
        }

        return $query->groupBy('loan_status')
            ->selectRaw('loan_status, COUNT(*) as count, SUM(principal_amount) as total_amount')
            ->get();
    }

    /**
     * Get cooperatives breakdown
     */
    public function getCooperativesBreakdown(): Collection
    {
        return Cooperative::with([
            'borrowers' => function ($q) {
                $q->withCount('loans');
            }
        ])
        ->selectRaw('cooperatives.*, 
                     COUNT(DISTINCT borrowers.id) as total_borrowers,
                     COUNT(DISTINCT loans.id) as total_loans,
                     SUM(loans.principal_amount) as total_disbursed')
        ->leftJoin('borrowers', 'borrowers.cooperative_id', '=', 'cooperatives.id')
        ->leftJoin('loans', 'loans.id', '=', 'borrowers.id')
        ->groupBy('cooperatives.id')
        ->get();
    }

    /**
     * Get top performing borrowers
     */
    public function getTopBorrowers($limit = 10, $cooperativeId = null): Collection
    {
        return Borrower::query()
            ->when($cooperativeId, function ($q) {
                return $q->where('cooperative_id', $cooperativeId);
            })
            ->withCount(['loans' => function ($q) {
                $q->where('loan_status', 'completed');
            }])
            ->withSum(['loans' => function ($q) {
                $q->where('loan_status', 'completed');
            }], 'principal_amount')
            ->orderByDesc('loans_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get borrowers at risk (overdue payments)
     */
    public function getBorrowersAtRisk($cooperativeId = null): Collection
    {
        return Borrower::query()
            ->when($cooperativeId, function ($q) {
                return $q->where('cooperative_id', $cooperativeId);
            })
            ->whereHas('loans', function ($q) {
                $q->where('loan_status', 'approved')
                  ->where('due_date', '<', now());
            })
            ->with(['loans' => function ($q) {
                $q->where('loan_status', 'approved')
                  ->where('due_date', '<', now());
            }])
            ->get();
    }

    /**
     * Get M-Pesa success rate
     */
    private function getMpesaSuccessRate($cooperativeId = null, $startDate = null, $endDate = null): float
    {
        $query = Repayments::whereNotNull('mpesa_transaction_id')
            ->whereBetween('created_at', [$startDate ?? now()->subMonths(12), $endDate ?? now()]);

        if ($cooperativeId) {
            $query->whereHas('loan.borrower', function ($q) use ($cooperativeId) {
                $q->where('cooperative_id', $cooperativeId);
            });
        }

        $total = $query->count();
        if ($total === 0) return 0;

        $successful = $query->where('mpesa_status', 'completed')->count();
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get monthly trends for loans and repayments
     */
    public function getMonthlyTrends($months = 12, $cooperativeId = null): array
    {
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $startDate = $month->clone()->startOfMonth();
            $endDate = $month->clone()->endOfMonth();

            $loanQuery = Loan::whereBetween('created_at', [$startDate, $endDate]);
            $repaymentQuery = Repayments::whereBetween('created_at', [$startDate, $endDate]);

            if ($cooperativeId) {
                $loanQuery->whereHas('borrower', function ($q) use ($cooperativeId) {
                    $q->where('cooperative_id', $cooperativeId);
                });
                $repaymentQuery->whereHas('loan.borrower', function ($q) use ($cooperativeId) {
                    $q->where('cooperative_id', $cooperativeId);
                });
            }

            $trends[$month->format('Y-m')] = [
                'month' => $month->format('M Y'),
                'loans_created' => $loanQuery->count(),
                'loans_amount' => $loanQuery->sum('principal_amount'),
                'repayments' => $repaymentQuery->count(),
                'repayments_amount' => $repaymentQuery->sum('payments'),
            ];
        }

        return $trends;
    }
}
