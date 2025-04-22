<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->helperText('The name of the product'),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->required()
                    ->helperText('The image of the product'),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull()
                    ->helperText('The description of the product'),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$')
                    ->helperText('The price of the product in dollars'),
                Forms\Components\TextInput::make('unit')
                    ->required()
                    ->helperText('The unit of measurement for the product. E.g. kg, lb, pcs'),
                Forms\Components\TextInput::make('delivery_time')
                    ->label('Delivery Time (Days)')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->helperText('The number of days needed for delivery after vendor preparation'),
                Forms\Components\Select::make('category_id')
                    ->options(Category::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->helperText('The category the product belongs to'),
                Forms\Components\Select::make('vendor_id')
                    ->options(Vendor::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->helperText('The vendor who supplies the product'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->helperText('The ID of the product'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->helperText('The name of the product'),
                Tables\Columns\TextColumn::make('stripe_product_id')
                    ->searchable()
                    ->helperText('The Stripe product ID'),
                Tables\Columns\TextColumn::make('stripe_price_id')
                    ->searchable()
                    ->helperText('The Stripe price ID'),
                Tables\Columns\ImageColumn::make('image')
                    ->helperText('The image of the product'),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable()
                    ->helperText('The price of the product in dollars'),
                Tables\Columns\TextColumn::make('unit')
                    ->searchable()
                    ->helperText('The unit of measurement for the product'),
                Tables\Columns\TextColumn::make('delivery_time')
                    ->label('Delivery Time (Days)')
                    ->sortable()
                    ->helperText('The number of days needed for delivery after vendor preparation'),
                Tables\Columns\TextColumn::make('category_id')
                    ->numeric()
                    ->sortable()
                    ->helperText('The category the product belongs to'),
                Tables\Columns\TextColumn::make('vendor_id')
                    ->numeric()
                    ->sortable()
                    ->helperText('The vendor who supplies the product'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->helperText('The date and time the product was deleted'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->helperText('The date and time the product was created'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->helperText('The date and time the product was last updated'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
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
