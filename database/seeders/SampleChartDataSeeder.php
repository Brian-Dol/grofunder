<?php

namespace Database\Seeders;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Repayments;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SampleChartDataSeeder extends Seeder
{
    public function run(): void
    {
        // Skip borrower seeding, use existing data
        $this->seedRepayments();
        $this->seedExpenses();

        $this->command->info('Sample chart data seeded successfully!');
    }

    private function seedRepayments()
    {
        $loans = Loan::all();
        
        if ($loans->isEmpty()) {
            $this->command->warn('No loans found in database. Skipping repayment seeding.');
            return;
        }

        foreach ($loans as $index => $loan) {
            // Ensure loan has loan_number
            if (!$loan->loan_number) {
                $loan->loan_number = "LOAN" . str_pad($loan->id, 5, '0', STR_PAD_LEFT);
                $loan->save();
            }

            $repaymentCount = rand(1, 6);

            for ($j = 0; $j < $repaymentCount; $j++) {
                $repaymentDate = Carbon::now()->subMonths(6 - $j)->startOfMonth()->addDays(rand(0, 20));

                Repayments::firstOrCreate(
                    [
                        'loan_id' => $loan->id,
                        'repayment_date' => $repaymentDate->toDateString(),
                    ],
                    [
                        'loan_number' => $loan->loan_number,
                        'payments' => rand(10000, 50000),
                        'principal' => rand(5000, 25000),
                        'balance' => rand(20000, 200000),
                        'payments_method' => ['cash', 'bank_transfer', 'mpesa'][rand(0, 2)],
                    ]
                );
            }
        }
    }

    private function seedExpenses()
    {
        $expenseNames = ['Office Rent', 'Utilities', 'Salaries', 'Office Supplies', 'IT Maintenance', 'Marketing', 'Transport'];

        for ($month = 1; $month <= 12; $month++) {
            for ($i = 0; $i < rand(3, 6); $i++) {
                $expenseDate = Carbon::now()->subMonths(12 - $month)->startOfMonth()->addDays(rand(1, 25))->toDateString();

                Expense::firstOrCreate(
                    [
                        'expense_name' => $expenseNames[rand(0, count($expenseNames) - 1)],
                        'expense_date' => $expenseDate,
                    ],
                    [
                        'expense_amount' => (string) rand(5000, 50000),
                        'expense_vendor' => 'Vendor ' . rand(1, 10),
                        'expense_attachment' => null,
                        'category_id' => rand(1, 5),  // Use categories 1-5 only
                        'from_this_account' => 'Main Account',
                    ]
                );
            }
        }
    }
}
