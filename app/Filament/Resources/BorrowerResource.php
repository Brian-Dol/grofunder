<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BorrowerResource\Pages;
use App\Models\Borrower;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BorrowerResource extends Resource
{
    protected static ?string $model = Borrower::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Borrowers';
    protected static ?string $modelLabel = 'Customers';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('first_name')
                ->required()
                ->label('First Name'),
            Forms\Components\TextInput::make('last_name')
                ->required()
                ->label('Last Name'),
            Forms\Components\TextInput::make('email')
                ->email()
                ->label('Email Address'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),
                Tables\Columns\TextColumn::make('first_name')
                    ->sortable()
                    ->label('First Name'),
                Tables\Columns\TextColumn::make('last_name')
                    ->sortable()
                    ->label('Last Name'),
                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->label('Email'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBorrowers::route('/'),
            'create' => Pages\CreateBorrower::route('/create'),
            'view' => Pages\ViewBorrower::route('/{record}'),
            'edit' => Pages\EditBorrower::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
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
