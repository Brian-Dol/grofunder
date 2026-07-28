<?php

namespace App\Filament\Pages;

use App\Services\BulkImportService;
use Filament\Pages\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LoanBulkImportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.loan-bulk-import-page';
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationLabel = 'Bulk Import Loans';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Bulk Import Loans';

    public ?array $data = [];
    public array $importReport = [];
    public bool $showReport = false;
    public ?string $sampleCsvContent = null;

    public function mount(): void
    {
        $this->authorize('bulkImport', \App\Models\Loan::class);
        $this->form->fill();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        
        // Super admin and admins can access
        if ($user && $user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // Agents can access if they have a cooperative assigned
        if ($user && $user->hasRole('agent') && $user->cooperative_id) {
            return true;
        }

        return false;
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Import Loans from CSV')
                ->description('Upload a CSV file with loan data to import multiple loans at once.')
                ->schema([
                    Select::make('cooperative_id')
                        ->label('Cooperative')
                        ->relationship('cooperative', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->visible(fn () => Auth::user()->hasRole(['super_admin', 'admin']))
                        ->default(fn () => Auth::user()->hasRole('agent') ? Auth::user()->cooperative_id : null),

                    FileUpload::make('csv_file')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->maxSize(10240) // 10MB
                        ->required()
                        ->hint('Format: CSV with borrower_mobile_number, loan_number, principal_amount, interest_rate, loan_term_months, loan_status, disbursement_date'),

                    \Filament\Forms\Components\Placeholder::make('template_hint')
                        ->label('Sample Format')
                        ->content('borrower_mobile_number,loan_number,principal_amount,interest_rate,loan_term_months,loan_status,disbursement_date' . "\n" .
                                 '+256701234567,LN-001,1000000,15,12,approved,2026-01-15' . "\n" .
                                 '+256702345678,LN-002,1500000,15,18,approved,2026-01-20')
                        ->helperText('Download sample template below'),
                ]),
        ];
    }

    public function downloadSampleCsv(): void
    {
        $service = new BulkImportService();
        $content = $service->generateSampleCsv('loan');
        
        \Illuminate\Support\Facades\Response::streamDownload(
            function () use ($content) {
                echo $content;
            },
            'loans_template.csv',
            ['Content-Type' => 'text/csv']
        )->send();
    }

    public function import(): void
    {
        try {
            $data = $this->form->getState();

            // Get file path
            if (empty($data['csv_file']) || is_string($data['csv_file'])) {
                Notification::make()
                    ->title('Error')
                    ->body('Please upload a CSV file')
                    ->danger()
                    ->send();
                return;
            }

            $filePath = Storage::disk('local')->path($data['csv_file']);

            // Validate CSV structure
            $service = new BulkImportService();
            $validation = $service->validateCsvStructure($filePath, 'loan');

            if (!$validation['valid']) {
                Notification::make()
                    ->title('Validation Error')
                    ->body($validation['message'])
                    ->danger()
                    ->send();
                return;
            }

            // Import loans
            $cooperativeId = $data['cooperative_id'] ?? Auth::user()->cooperative_id;
            $this->importReport = $service->importLoans($filePath, $cooperativeId);
            $this->showReport = true;

            // Notify user
            if ($this->importReport['failed'] === 0) {
                Notification::make()
                    ->title('Success')
                    ->body("Successfully imported {$this->importReport['successful']} loans")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Partial Success')
                    ->body("Imported {$this->importReport['successful']} loans, {$this->importReport['failed']} failed")
                    ->warning()
                    ->send();
            }

            // Clear form
            $this->form->fill();
            Storage::delete($data['csv_file']);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
