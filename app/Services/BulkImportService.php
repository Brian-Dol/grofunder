<?php

namespace App\Services;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Cooperative;
use App\Models\BulkImportLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class BulkImportService
{
    private array $errors = [];
    private array $warnings = [];
    private int $successCount = 0;
    private int $failedCount = 0;

    /**
     * Import borrowers from CSV file
     */
    public function importBorrowers($filePath, $cooperativeId = null): array
    {
        try {
            $this->resetCounters();
            $rows = $this->readCsvFile($filePath);
            
            if ($rows->isEmpty()) {
                throw new Exception('CSV file is empty');
            }

            $cooperativeId = $cooperativeId ?? auth()->user()->cooperative_id;

            // Validate cooperative exists
            $cooperative = Cooperative::find($cooperativeId);
            if (!$cooperative && auth()->user()->hasRole('agent')) {
                throw new Exception('Invalid cooperative assignment for agent');
            }

            foreach ($rows as $index => $row) {
                try {
                    $this->importBorrowerRow($row, $cooperativeId, $index + 2); // +2 for header and 0-index
                } catch (Exception $e) {
                    $this->failedCount++;
                    $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            return $this->generateImportReport('Borrowers');

        } catch (Exception $e) {
            Log::error('Bulk borrower import failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Import loans from CSV file
     */
    public function importLoans($filePath, $cooperativeId = null): array
    {
        try {
            $this->resetCounters();
            $rows = $this->readCsvFile($filePath);
            
            if ($rows->isEmpty()) {
                throw new Exception('CSV file is empty');
            }

            $cooperativeId = $cooperativeId ?? auth()->user()->cooperative_id;

            foreach ($rows as $index => $row) {
                try {
                    $this->importLoanRow($row, $cooperativeId, $index + 2);
                } catch (Exception $e) {
                    $this->failedCount++;
                    $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            return $this->generateImportReport('Loans');

        } catch (Exception $e) {
            Log::error('Bulk loan import failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Read CSV file and return rows as collection
     */
    private function readCsvFile($filePath): Collection
    {
        if (!file_exists($filePath)) {
            throw new Exception('File not found: ' . $filePath);
        }

        $rows = collect();
        $handle = fopen($filePath, 'r');
        $headers = null;

        while (($row = fgetcsv($handle)) !== false) {
            // First row is headers
            if ($headers === null) {
                $headers = array_map('strtolower', $row);
                continue;
            }

            // Skip empty rows
            if (count(array_filter($row)) === 0) {
                continue;
            }

            // Combine headers with values
            $rowData = array_combine($headers, $row);
            $rows->push($rowData);
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Import single borrower row
     */
    private function importBorrowerRow(array $row, $cooperativeId, $rowNumber): void
    {
        // Validate required fields
        $validator = Validator::make($row, [
            'name' => 'required|string',
            'mobile_number' => 'required|string',
            'email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        // Check for duplicate mobile number
        $existing = Borrower::where('mobile_number', $row['mobile_number'])
            ->where('cooperative_id', $cooperativeId)
            ->first();

        if ($existing) {
            $this->warnings[] = "Row $rowNumber: Borrower with mobile number {$row['mobile_number']} already exists (skipped)";
            return;
        }

        // Create borrower
        $borrower = Borrower::create([
            'name' => $row['name'],
            'mobile_number' => $row['mobile_number'],
            'email' => $row['email'] ?? null,
            'cooperative_id' => $cooperativeId,
            'organization_id' => auth()->user()->organization_id,
            'branch_id' => auth()->user()->branch_id,
            'created_by' => auth()->id(),
        ]);

        $this->successCount++;
        Log::info('Borrower imported', ['borrower_id' => $borrower->id, 'name' => $borrower->name]);
    }

    /**
     * Import single loan row
     */
    private function importLoanRow(array $row, $cooperativeId, $rowNumber): void
    {
        // Validate required fields
        $validator = Validator::make($row, [
            'borrower_mobile_number' => 'required|string',
            'loan_number' => 'required|string',
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'loan_term_months' => 'required|integer|min:1',
            'loan_status' => 'required|in:pending,approved,completed,defaulted',
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        // Find borrower by mobile number
        $borrower = Borrower::where('mobile_number', $row['borrower_mobile_number'])
            ->where('cooperative_id', $cooperativeId)
            ->first();

        if (!$borrower) {
            throw new Exception("Borrower with mobile number {$row['borrower_mobile_number']} not found");
        }

        // Check for duplicate loan number
        $existing = Loan::where('loan_number', $row['loan_number'])
            ->where('borrower_id', $borrower->id)
            ->first();

        if ($existing) {
            $this->warnings[] = "Row $rowNumber: Loan {$row['loan_number']} already exists for this borrower (skipped)";
            return;
        }

        // Calculate dates
        $disbursementDate = $this->parseDate($row['disbursement_date'] ?? now()->toDateString());
        $termMonths = (int)$row['loan_term_months'];
        $dueDate = $disbursementDate->clone()->addMonths($termMonths);

        // Create loan
        $loan = Loan::create([
            'borrower_id' => $borrower->id,
            'loan_number' => $row['loan_number'],
            'principal_amount' => $row['principal_amount'],
            'interest_rate' => $row['interest_rate'],
            'loan_term_months' => $termMonths,
            'balance' => $row['principal_amount'],
            'loan_status' => $row['loan_status'],
            'disbursement_date' => $disbursementDate,
            'due_date' => $dueDate,
            'organization_id' => auth()->user()->organization_id,
            'branch_id' => auth()->user()->branch_id,
            'created_by' => auth()->id(),
        ]);

        $this->successCount++;
        Log::info('Loan imported', ['loan_id' => $loan->id, 'loan_number' => $loan->loan_number]);
    }

    /**
     * Parse date string to Carbon instance
     */
    private function parseDate($dateString)
    {
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y'];
        
        foreach ($formats as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, $dateString);
            } catch (\Exception $e) {
                continue;
            }
        }

        throw new Exception("Invalid date format: $dateString");
    }

    /**
     * Generate import report
     */
    private function generateImportReport($type): array
    {
        $report = [
            'type' => $type,
            'total_rows' => $this->successCount + $this->failedCount,
            'successful' => $this->successCount,
            'failed' => $this->failedCount,
            'success_rate' => $this->getSuccessRate(),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];

        // Log to database
        try {
            BulkImportLog::create([
                'user_id' => Auth::id(),
                'import_type' => strtolower($type),
                'file_name' => 'bulk_import_' . strtolower($type),
                'total_rows' => $report['total_rows'],
                'successful_imports' => $this->successCount,
                'failed_imports' => $this->failedCount,
                'success_rate' => $report['success_rate'],
                'errors' => !empty($this->errors) ? $this->errors : null,
                'warnings' => !empty($this->warnings) ? $this->warnings : null,
                'status' => $this->failedCount === 0 ? 'completed' : 'partial',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log bulk import', ['error' => $e->getMessage()]);
        }

        return $report;
    }

    /**
     * Get success rate percentage
     */
    private function getSuccessRate(): float
    {
        $total = $this->successCount + $this->failedCount;
        if ($total === 0) return 0;
        return round(($this->successCount / $total) * 100, 2);
    }

    /**
     * Reset counters
     */
    private function resetCounters(): void
    {
        $this->errors = [];
        $this->warnings = [];
        $this->successCount = 0;
        $this->failedCount = 0;
    }

    /**
     * Validate CSV structure before import
     */
    public function validateCsvStructure($filePath, $type = 'borrower'): array
    {
        try {
            $handle = fopen($filePath, 'r');
            $headers = array_map('strtolower', fgetcsv($handle));
            fclose($handle);

            $requiredFields = $type === 'borrower' 
                ? ['name', 'mobile_number']
                : ['borrower_mobile_number', 'loan_number', 'principal_amount', 'interest_rate', 'loan_term_months', 'loan_status'];

            $missingFields = array_diff($requiredFields, $headers);

            if (!empty($missingFields)) {
                return [
                    'valid' => false,
                    'message' => 'Missing required columns: ' . implode(', ', $missingFields),
                ];
            }

            return ['valid' => true, 'message' => 'CSV structure is valid'];

        } catch (Exception $e) {
            return ['valid' => false, 'message' => 'Error reading file: ' . $e->getMessage()];
        }
    }

    /**
     * Generate sample CSV template
     */
    public function generateSampleCsv($type = 'borrower'): string
    {
        if ($type === 'borrower') {
            return "name,mobile_number,email\n" .
                   "John Doe,+256701234567,john@example.com\n" .
                   "Jane Smith,+256702345678,jane@example.com\n" .
                   "Peter Johnson,+256703456789,peter@example.com";
        } else {
            return "borrower_mobile_number,loan_number,principal_amount,interest_rate,loan_term_months,loan_status,disbursement_date\n" .
                   "+256701234567,LN-001,1000000,15,12,approved,2026-01-15\n" .
                   "+256702345678,LN-002,1500000,15,18,approved,2026-01-20\n" .
                   "+256703456789,LN-003,2000000,18,24,pending,2026-02-01";
        }
    }
}
