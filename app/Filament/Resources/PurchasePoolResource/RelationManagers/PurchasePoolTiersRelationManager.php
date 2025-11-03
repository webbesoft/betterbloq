<?php

namespace App\Filament\Resources\PurchasePoolResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PurchasePoolTiersRelationManager extends RelationManager
{
    protected static string $relationship = 'purchasePoolTiers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->required(),
                TextInput::make('discount_percentage')
                    ->required()
                    ->numeric(),
                TextInput::make('stripe_coupon_id')
                    ->maxLength(255)
                    ->default(Str::uuid()->toString())
                    ->readOnly()
                    ->label('Stripe Coupon ID (read only)')
                    ->helperText('Stripe coupon ID'),
                TextInput::make('min_volume')
                    ->numeric(),
                TextInput::make('max_volume')
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('discount_percentage'),
                Tables\Columns\TextColumn::make('min_volume'),
                Tables\Columns\TextColumn::make('max_volume'),
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
