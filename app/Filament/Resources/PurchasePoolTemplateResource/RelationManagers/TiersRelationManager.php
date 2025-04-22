<?php

namespace App\Filament\Resources\PurchasePoolTemplateResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TiersRelationManager extends RelationManager
{
    protected static string $relationship = 'tiers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('discount_percentage')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->suffix('%'),
                TextInput::make('min_volume')
                    ->numeric()
                    ->nullable(),
                TextInput::make('max_volume')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('min_volume')
                    ->numeric(decimalPlaces: 2),
                Tables\Columns\TextColumn::make('max_volume')
                    ->numeric(decimalPlaces: 2),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
