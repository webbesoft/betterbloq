<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchasePoolTierResource\Pages;
use App\Models\PurchasePool;
use App\Models\PurchasePoolTier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchasePoolTierResource extends Resource
{
    protected static ?string $model = PurchasePoolTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The name of the tier'),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The description of the tier'),
                Forms\Components\TextInput::make('min_volume')
                    ->numeric()
                    ->default(null)
                    ->helperText('The minimum volume of the tier'),
                Forms\Components\TextInput::make('max_volume')
                    ->numeric()
                    ->default(null)
                    ->helperText('The maximum volume of the tier'),
                Forms\Components\TextInput::make('discount_percentage')
                    ->required()
                    ->numeric()
                    ->helperText('The discount percentage of the tier'),
                Forms\Components\Select::make('purchase_pool_id')
                    ->label('Purchase Pool')
                    ->required()
                    ->options(
                        PurchasePool::all()->pluck('name', 'id')
                    )
                    ->searchable()
                    ->helperText('The purchase pool the tier belongs to'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('min_volume')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_volume')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_pool_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchasePoolTiers::route('/'),
            'create' => Pages\CreatePurchasePoolTier::route('/create'),
            'edit' => Pages\EditPurchasePoolTier::route('/{record}/edit'),
        ];
    }
}
