<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Widgets\Widget;
use App\Models\Loan;
use App\Models\Payments;
use App\Models\Borrower;

class InvestorDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'Investor Dashboard';
    
    protected static ?int $navigationSort = -2;
    
    protected static bool $shouldRegisterNavigation = true;

    protected static string $view = 'filament.pages.investor-dashboard';
    
    public function getWidgets(): array
    {
        return [
            // Define any dashboard widgets here
        ];
    }

    /**
     * Get portfolio statistics for the investor
     */
    public function getPortfolioStats(): array
    {
        $loans = Loan::query()
            ->whereIn('loan_status', ['approved', 'partially_paid', 'fully_paid'])
            ->get();

        $totalInvested = $loans->sum('principal_amount');
        $activeLoans = $loans->whereIn('loan_status', ['approved', 'partially_paid'])->count();
        $completedLoans = $loans->where('loan_status', 'fully_paid')->count();
        $defaultedLoans = Loan::where('loan_status', 'defaulted')->count();
        
        // Calculate repayment status
        $totalPayments = Payments::count();
        $completedPayments = Payments::whereNotNull('transaction_reference')->count();
        $pendingPayments = Payments::whereNull('transaction_reference')->count();
        
        return [
            'total_invested' => $totalInvested,
            'active_loans' => $activeLoans,
            'completed_loans' => $completedLoans,
            'defaulted_loans' => $defaultedLoans,
            'completed_payments' => $completedPayments,
            'pending_payments' => $pendingPayments,
            'total_payments' => $totalPayments,
            'payment_rate' => $totalPayments > 0 ? round(($completedPayments / $totalPayments) * 100, 2) : 0,
        ];
    }

    /**
     * Get portfolio breakdown by cooperative
     */
    public function getCooperativeBreakdown(): array
    {
        $cooperatives = \App\Models\Cooperative::withCount([
            'borrowers',
            'borrowers as active_loans' => function ($query) {
                $query->whereHas('loans', function ($q) {
                    $q->whereIn('loan_status', ['approved', 'partially_paid']);
                });
            },
            'borrowers as total_farmers' => function ($query) {
                $query->whereHas('loans');
            }
        ])->get();

        return $cooperatives->map(function ($coop) {
            return [
                'name' => $coop->name,
                'farmers' => $coop->total_farmers_count,
                'active_loans' => $coop->active_loans_count,
                'region' => $coop->region,
            ];
        })->toArray();
    }
}
