<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StorageOrderLineItemResource\Pages;
use App\Models\StorageOrderLineItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StorageOrderLineItemResource extends Resource
{
    protected static ?string $model = StorageOrderLineItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Storage Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('storage_order_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity_stored')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('manual_dimensions_length')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('manual_dimensions_width')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('manual_dimensions_height')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('calculated_space_for_item')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('entry_date'),
                Forms\Components\DatePicker::make('retrieval_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('storage_order_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_stored')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manual_dimensions_length')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manual_dimensions_width')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manual_dimensions_height')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('calculated_space_for_item')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('entry_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('retrieval_date')
                    ->date()
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorageOrderLineItems::route('/'),
            'create' => Pages\CreateStorageOrderLineItem::route('/create'),
            'view' => Pages\ViewStorageOrderLineItem::route('/{record}'),
            'edit' => Pages\EditStorageOrderLineItem::route('/{record}/edit'),
        ];
    }
}
