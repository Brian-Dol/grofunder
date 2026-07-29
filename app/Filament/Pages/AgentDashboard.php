<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Payment;

class AgentDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Agent Dashboard';
    
    protected static ?int $navigationSort = -1;
    
    protected static bool $shouldRegisterNavigation = true;

    protected static string $view = 'filament.pages.agent-dashboard';
    
    /**
     * Only allow users with 'agent' role to access this page
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole('agent');
    }

    /**
     * Get agent's cooperative info
     */
    public function getCooperativeInfo(): ?array
    {
        $cooperative = auth()->user()->cooperative;
        
        if (!$cooperative) {
            return null;
        }

        return [
            'name' => $cooperative->name,
            'region' => $cooperative->region,
            'contact_email' => $cooperative->contact_email,
            'contact_phone' => $cooperative->contact_phone,
            'status' => $cooperative->status,
        ];
    }

    /**
     * Get agent's farmer statistics
     */
    public function getFarmerStats(): array
    {
        $borrowers = Borrower::where('cooperative_id', auth()->user()->cooperative_id)->get();
        $loans = Loan::whereHas('borrower', function ($q) {
            $q->where('cooperative_id', auth()->user()->cooperative_id);
        })->get();

        return [
            'total_farmers' => $borrowers->count(),
            'active_loans' => $loans->whereIn('loan_status', ['approved', 'partially_paid'])->count(),
            'total_farmers_with_loans' => $borrowers->filter(function ($b) {
                return $b->loans()->exists();
            })->count(),
            'on_time_loans' => $loans->filter(function ($loan) {
                return $loan->loan_status === 'partially_paid' || $loan->loan_status === 'fully_paid';
            })->count(),
            'overdue_loans' => $loans->filter(function ($loan) {
                return $loan->loan_status === 'defaulted';
            })->count(),
        ];
    }

    /**
     * Get farmer list with loan details
     */
    public function getFarmersList(): array
    {
        return Borrower::where('cooperative_id', auth()->user()->cooperative_id)
            ->with(['loans' => function ($q) {
                $q->whereIn('loan_status', ['approved', 'partially_paid', 'fully_paid'])
                  ->orderBy('created_at', 'desc');
            }])
            ->orderBy('first_name')
            ->get()
            ->map(function ($borrower) {
                $activeLoans = $borrower->loans->whereIn('loan_status', ['approved', 'partially_paid']);
                return [
                    'id' => $borrower->id,
                    'name' => "{$borrower->first_name} {$borrower->last_name}",
                    'mobile_number' => $borrower->mobile_number,
                    'mobile' => $borrower->mobile,
                    'active_loans' => $activeLoans->count(),
                    'total_borrowed' => $borrower->loans->sum('principal_amount'),
                    'total_repaid' => $borrower->loans->sum(function ($loan) {
                        return $loan->principal_amount - ($loan->balance ?? 0);
                    }),
                ];
            })->toArray();
    }

    /**
     * Get recent loan activity
     */
    public function getRecentActivity(): array
    {
        return Loan::whereHas('borrower', function ($q) {
            $q->where('cooperative_id', auth()->user()->cooperative_id);
        })
            ->with('borrower')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($loan) {
                return [
                    'loan_number' => $loan->loan_number,
                    'farmer_name' => "{$loan->borrower->first_name} {$loan->borrower->last_name}",
                    'amount' => $loan->principal_amount,
                    'status' => $loan->loan_status,
                    'updated_at' => $loan->updated_at->format('M j, Y'),
                ];
            })->toArray();
    }
}
