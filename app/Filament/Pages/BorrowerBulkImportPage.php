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

class BorrowerBulkImportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.borrower-bulk-import-page';
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';
    protected static ?string $navigationLabel = 'Bulk Import Borrowers';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Bulk Import Borrowers';

    public ?array $data = [];
    public array $importReport = [];
    public bool $showReport = false;
    public ?string $sampleCsvContent = null;

    public function mount(): void
    {
        $this->authorize('bulkImport', \App\Models\Borrower::class);
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
            Section::make('Import Borrowers from CSV')
                ->description('Upload a CSV file with borrower data to import multiple borrowers at once.')
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
                        ->hint('Format: CSV with columns: name, mobile_number, email'),

                    \Filament\Forms\Components\Placeholder::make('template_hint')
                        ->label('Sample Format')
                        ->content('name,mobile_number,email' . "\n" .
                                 'John Doe,+256701234567,john@example.com' . "\n" .
                                 'Jane Smith,+256702345678,jane@example.com')
                        ->helperText('Download sample template below'),
                ]),
        ];
    }

    public function downloadSampleCsv(): void
    {
        $service = new BulkImportService();
        $content = $service->generateSampleCsv('borrower');
        
        \Illuminate\Support\Facades\Response::streamDownload(
            function () use ($content) {
                echo $content;
            },
            'borrowers_template.csv',
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
            $validation = $service->validateCsvStructure($filePath, 'borrower');

            if (!$validation['valid']) {
                Notification::make()
                    ->title('Validation Error')
                    ->body($validation['message'])
                    ->danger()
                    ->send();
                return;
            }

            // Import borrowers
            $cooperativeId = $data['cooperative_id'] ?? Auth::user()->cooperative_id;
            $this->importReport = $service->importBorrowers($filePath, $cooperativeId);
            $this->showReport = true;

            // Notify user
            if ($this->importReport['failed'] === 0) {
                Notification::make()
                    ->title('Success')
                    ->body("Successfully imported {$this->importReport['successful']} borrowers")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Partial Success')
                    ->body("Imported {$this->importReport['successful']} borrowers, {$this->importReport['failed']} failed")
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
