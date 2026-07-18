<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Loan Agreement',
                'slug' => 'loan-agreement',
                'description' => 'Signed loan agreement document between borrower and lender',
                'icon' => 'heroicon-o-document-text',
            ],
            [
                'name' => 'KYC Document',
                'slug' => 'kyc-document',
                'description' => 'Know Your Customer documentation for borrower verification',
                'icon' => 'heroicon-o-identification',
            ],
            [
                'name' => 'ID Document',
                'slug' => 'id-document',
                'description' => 'Borrower identification document (passport, national ID, etc)',
                'icon' => 'heroicon-o-document',
            ],
            [
                'name' => 'Proof of Address',
                'slug' => 'proof-of-address',
                'description' => 'Document proving borrower residence address',
                'icon' => 'heroicon-o-home',
            ],
            [
                'name' => 'Income Statement',
                'slug' => 'income-statement',
                'description' => 'Borrower income and financial statement',
                'icon' => 'heroicon-o-chart-bar',
            ],
            [
                'name' => 'Collateral Document',
                'slug' => 'collateral-document',
                'description' => 'Documentation of collateral pledged for loan',
                'icon' => 'heroicon-o-lock-closed',
            ],
            [
                'name' => 'Guarantor Document',
                'slug' => 'guarantor-document',
                'description' => 'Guarantor identification and pledge document',
                'icon' => 'heroicon-o-hand-raised',
            ],
            [
                'name' => 'Repayment Schedule',
                'slug' => 'repayment-schedule',
                'description' => 'Loan repayment schedule and terms',
                'icon' => 'heroicon-o-calendar',
            ],
            [
                'name' => 'Loan Settlement Form',
                'slug' => 'loan-settlement-form',
                'description' => 'Loan settlement and completion document',
                'icon' => 'heroicon-o-check-circle',
            ],
            [
                'name' => 'Bank Statement',
                'slug' => 'bank-statement',
                'description' => 'Borrower bank statement for verification',
                'icon' => 'heroicon-o-building-library',
            ],
            [
                'name' => 'Business License',
                'slug' => 'business-license',
                'description' => 'Business registration and license documentation',
                'icon' => 'heroicon-o-briefcase',
            ],
            [
                'name' => 'Other Document',
                'slug' => 'other-document',
                'description' => 'Other supporting documents',
                'icon' => 'heroicon-o-document-duplicate',
            ],
        ];

        foreach ($categories as $category) {
            DocumentCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
