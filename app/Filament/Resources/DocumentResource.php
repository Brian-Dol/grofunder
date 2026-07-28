<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationLabel = 'Documents';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Document Information')
                    ->schema([
                        Select::make('document_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required(),

                        Select::make('borrower_id')
                            ->label('Borrower')
                            ->relationship('borrower', 'first_name')
                            ->searchable()
                            ->nullable(),

                        Select::make('loan_id')
                            ->label('Loan')
                            ->relationship('loan', 'loan_number')
                            ->searchable()
                            ->nullable(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('File Upload')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Document File')
                            ->disk('documents')
                            ->directory('uploads')
                            ->preserveFilenames()
                            ->maxSize(10240) // 10MB
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->required(fn (string $operation) => $operation === 'create'),
                    ]),

                Forms\Components\Section::make('Expiration')
                    ->schema([
                        Forms\Components\DateTimePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->nullable()
                            ->hint('Leave empty for documents that never expire'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'archived' => 'Archived',
                                'expired' => 'Expired',
                            ])
                            ->default('active')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_name')
                    ->label('File Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('borrower.first_name')
                    ->label('Borrower')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('loan.loan_number')
                    ->label('Loan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('readable_file_size')
                    ->label('File Size')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'archived' => 'gray',
                        'expired' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('uploadedBy.name')
                    ->label('Uploaded By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'archived' => 'Archived',
                        'expired' => 'Expired',
                    ]),

                Tables\Filters\SelectFilter::make('borrower_id')
                    ->label('Borrower')
                    ->relationship('borrower', 'first_name'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Document $record) => static::downloadDocument($record)),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Agents only see documents for their cooperative
        $user = Auth::user();
        if ($user && $user->hasRole('agent') && $user->cooperative_id) {
            $query->whereHas('borrower', function ($q) use ($user) {
                $q->where('cooperative_id', $user->cooperative_id);
            })->orWhereHas('loan.borrower', function ($q) use ($user) {
                $q->where('cooperative_id', $user->cooperative_id);
            });
        }

        return $query;
    }

    public static function downloadDocument(Document $document)
    {
        if (!Storage::disk('documents')->exists($document->file_path)) {
            return response()->error('File not found');
        }

        return Storage::disk('documents')->download($document->file_path, $document->file_name);
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Super admin and admin can access
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // Agents can access if they have a cooperative
        if ($user->hasRole('agent') && $user->cooperative_id) {
            return true;
        }

        return false;
    }
}
