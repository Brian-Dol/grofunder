<?php

namespace App\Filament\Resources;

use App\Models\Borrower;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class BorrowerResource extends Resource
{
    protected static ?string $model = Borrower::class;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [];
    }
}
