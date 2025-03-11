<?php

namespace App\Filament\Resources;

use App\Apps\BulkBuy\Models\PurchasePool;
use App\Filament\Resources\PurchasePoolResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchasePoolResource extends Resource
{
    protected static ?string $model = PurchasePool::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->required(),
                Forms\Components\DatePicker::make('target_delivery_date')
                    ->required(),
                Forms\Components\TextInput::make('discount_percentage')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('min_orders_for_discount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('max_orders')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('vendor_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_orders_for_discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_orders')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vendor_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListPurchasePools::route('/'),
            'create' => Pages\CreatePurchasePool::route('/create'),
            'edit' => Pages\EditPurchasePool::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
