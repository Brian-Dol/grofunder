<?php

namespace App\Filament\Resources;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Forms\Set;
use Filament\Forms\Get;
use App\Filament\Resources\RepaymentsResource\Pages;
use App\Filament\Resources\RepaymentsResource\RelationManagers;
use App\Models\Repayments;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Exports\RepaymentsExporter;
use Filament\Tables\Actions\ExportAction;

class RepaymentsResource extends Resource
{
    protected static ?string $model = Repayments::class;

    protected static ?string $navigationGroup = 'Repayments';
    protected static ?string $navigationIcon = 'fas-dollar-sign';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('loan_id')
                    ->label('Loan Number')
                    ->prefixIcon('heroicon-o-wallet')
                    ->relationship('loan_number', 'loan_number')
                    ->searchable()
                    ->preload()
                    ->live(onBlur: true)
                    ->required(function ($state, Set $set) {

                        if ($state) {
                            $balance = \App\Models\Loan::findOrFail($state)->balance;
                            $set('balance', $balance);
                        }
                        return true;
                    }),



                Forms\Components\TextInput::make('payments')
                    ->label('Repayment Amount')
                    ->prefixIcon('fas-dollar-sign')
                    ->required(),
                Forms\Components\TextInput::make('balance')
                    ->label('Current Balance')
                    ->prefixIcon('fas-dollar-sign')
                    ->readOnly(),

                Forms\Components\Select::make('payments_method')
                    ->label('Payment Method')
                    ->prefixIcon('fas-dollar-sign')
                    ->required()
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'mobile_money' => 'Mobile Money',
                        'M-Pesa' => 'M-Pesa',
                        'pemic' => 'PEMIC',
                        'cheque' => 'Cheque',
                        'cash' => 'Cash',
                    ]),
                Forms\Components\TextInput::make('reference_number')
                    ->label('Transaction Reference')
                    ->prefixIcon('fas-dollar-sign')
                    ->columnSpan(2),

                Forms\Components\DatePicker::make('repayment_date')
                    ->label('Repayment Date')
                    ->prefixIcon('heroicon-o-calendar')
                    ->nullable()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->helperText('Optional. If not entered, today\'s date will be used as the repayment date.')
                    ->columnSpan(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->exporter(RepaymentsExporter::class)
            ])
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('repayment_date')
                    ->label('Repayment Date')
                    ->date('d M Y')
                    ->sortable()
                    ->searchable()
                    ->description(fn($record) => $record->repayment_date ? null : 'Auto: ' . $record->created_at?->format('d M Y')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recorded At')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('loan_number.loan_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('loan_number.loan_status')
                    ->label('Loan Status')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('payments')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('loan_number.repayment_amount')
                // ->label('Total Repayments')
                // ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('mpesa_status')
                    ->label('M-Pesa Status')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->colors([
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'cancelled' => 'secondary',
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('mpesa_transaction_id')
                    ->label('M-Pesa Txn ID')
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payments_method')
                    ->label('Payment Method')
                    ->searchable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payments_method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'mobile_money' => 'Mobile Money',
                        'pemic' => 'PEMIC',
                        'cheque' => 'Cheque',
                        'cash' => 'Cash',


                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('initiate_mpesa')
                    ->label('Send M-Pesa')
                    ->icon('heroicon-m-phone')
                    ->color('success')
                    ->visible(fn($record) => $record->mpesa_status !== 'completed')
                    ->form([
                        Forms\Components\TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->placeholder('+256701234567 or 0701234567')
                            ->required()
                            ->hint('E.164 format or domestic format'),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $response = $record->initiateMpesaPayment($data['phone_number']);
                            
                            if (isset($response['CheckoutRequestID'])) {
                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('M-Pesa Request Sent')
                                    ->body('Payment prompt sent to customer. They have 40 seconds to enter PIN.')
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('M-Pesa Error')
                                    ->body($response['errorMessage'] ?? 'Failed to initiate payment')
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('query_status')
                    ->label('Check Status')
                    ->icon('heroicon-m-magnifying-glass')
                    ->color('info')
                    ->visible(fn($record) => $record->isMpesaPending())
                    ->action(function ($record) {
                        try {
                            $mpesaService = app(\App\Services\MpesaService::class);
                            $response = $mpesaService->queryPaymentStatus($record->mpesa_checkout_request_id);
                            
                            $statusMessage = match($response['ResultCode'] ?? 1) {
                                '0' => 'Payment Completed',
                                '1' => 'Payment Cancelled',
                                default => 'Payment Pending',
                            };

                            \Filament\Notifications\Notification::make()
                                ->info()
                                ->title('M-Pesa Status')
                                ->body($statusMessage)
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Could not query payment status')
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepayments::route('/'),
            'create' => Pages\CreateRepayments::route('/create'),
            'view' => Pages\ViewRepayments::route('/{record}'),
            'edit' => Pages\EditRepayments::route('/{record}/edit'),
        ];
    }
}
