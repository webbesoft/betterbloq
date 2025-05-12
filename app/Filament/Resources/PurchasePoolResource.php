<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchasePoolResource\Pages;
use App\Filament\Resources\PurchasePoolResource\RelationManagers\PurchasePoolTiersRelationManager;
use App\Models\Product;
use App\Models\PurchasePool;
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

    protected static ?string $navigationGroup = 'Order Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name'),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->required(),
                Forms\Components\DatePicker::make('target_delivery_date')
                    ->label('Calculated Target Delivery Date')
                    ->disabled()
                    ->dehydrated(true)
                    ->helperText('Calculated based on End Date + Vendor Prep Time + Product Delivery Time.'),
                Forms\Components\TextInput::make('min_orders_for_discount')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('The minimum number of orders required to apply the discount'),
                Forms\Components\TextInput::make('max_orders')
                    ->numeric()
                    ->default(0)
                    ->helperText('The maximum number of orders allowed in the purchase pool'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'closed' => 'Closed',
                    ]),
                Forms\Components\TextInput::make('target_volume')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('The target volume of the purchase pool'),
                Forms\Components\TextInput::make('current_volume')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(
                        Product::with('vendor')
                            ->get()
                            ->mapWithKeys(function (Product $product) {
                                $vendorName = $product->vendor?->name ?? 'No Vendor Assigned';

                                return [$product->id => $product->name.' - '.$vendorName];
                            })
                            ->all()
                    )
                    ->required()
                    ->searchable()
                    ->helperText('Select the product. The vendor will be associated automatically.'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_volume')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_volume')
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
                Tables\Actions\ViewAction::make(),
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
            PurchasePoolTiersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchasePools::route('/'),
            'create' => Pages\CreatePurchasePool::route('/create'),
            'view' => Pages\ViewPurchasePool::route('/{record}'),
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
