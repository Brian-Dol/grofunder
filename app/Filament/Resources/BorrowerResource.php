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
    protected static ?string \ = Borrower::class;
    protected static ?string \ = 'heroicon-o-users';
    protected static ?string \ = 'Borrowers';
    protected static ?string \ = 'Customers';

    public static function form(Form \): Form
    {
        return \->schema([
            Forms\Components\TextInput::make('first_name')->required(),
            Forms\Components\TextInput::make('last_name')->required(),
        ]);
    }

    public static function table(Table \): Table
    {
        return \
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('first_name')->sortable(),
                Tables\Columns\TextColumn::make('last_name')->sortable(),
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
}
