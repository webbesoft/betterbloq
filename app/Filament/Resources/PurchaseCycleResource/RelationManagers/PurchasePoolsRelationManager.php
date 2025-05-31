<?php

namespace App\Filament\Resources\PurchaseCycleResource\RelationManagers;

use App\Models\PurchasePool;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PurchasePoolsRelationManager extends RelationManager
{
    protected static string $relationship = 'purchasePools';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id')
                    ->label('Select Pool')
                    ->options(PurchasePool::whereIn('cycle_status', [PurchasePool::STATUS_ACCUMULATING, PurchasePool::STATUS_ACTIVE])->pluck('name', 'id')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
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
