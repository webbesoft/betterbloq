<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseCycleResource\Pages;
use App\Filament\Resources\PurchaseCycleResource\RelationManagers;
use App\Models\PurchaseCycle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseCycleResource extends Resource
{
    protected static ?string $model = PurchaseCycle::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Order Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->native(false),
                Forms\Components\DatePicker::make('end_date')
                    ->required()
                    ->native(false)
                    ->afterOrEqual('stard_date'),
                Forms\Components\Select::make('status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'active' => 'Active',
                        'calculating_discounts' => 'Calculating Discounts',
                        'processing_payments' => 'Processing Payments',
                        'closed' => 'Closed',
                    ])
                    ->required()
                    ->default('upcoming'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming' => 'gray',
                        'active' => 'success',
                        'calculating_discounts' => 'warning',
                        'processing_payments' => 'info',
                        'closed' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'active' => 'Active',
                        'calculating_discounts' => 'Calculating Discounts',
                        'processing_payments' => 'Processing Payments',
                        'closed' => 'Closed',
                    ]),
            ])
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
        return [
            RelationManagers\PurchasePoolsRelationManager::class, // Shows pools linked to this cycle
            RelationManagers\OrdersRelationManager::class,       // Shows orders linked to this cycle
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseCycles::route('/'),
            'create' => Pages\CreatePurchaseCycle::route('/create'),
            'view' => Pages\ViewPurchaseCycle::route('/{record}'),
            'edit' => Pages\EditPurchaseCycle::route('/{record}/edit'),
        ];
    }
}
