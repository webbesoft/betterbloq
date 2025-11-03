<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchasePoolResource\Pages;
use App\Filament\Resources\PurchasePoolResource\RelationManagers\PurchasePoolTiersRelationManager;
use App\Models\Product;
use App\Models\PurchaseCycle;
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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('purchase_cycle_id')
                    ->label('Purchase Cycle')
                    ->options(
                        PurchaseCycle::whereIn('status', ['upcoming', 'active'])
                            ->get()
                            ->pluck('name', 'id')
                            ->all()
                    )
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('target_delivery_date')
                    ->label('Calculated Target Delivery Date')
                    ->disabled()
                    ->dehydrated(true)
                    ->helperText('Calculated based on Purchase Cycle End Date + Vendor Prep Time + Product Delivery Time.'),
                Forms\Components\TextInput::make('min_orders_for_discount')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('The minimum number of orders required to apply the discount'),
                Forms\Components\TextInput::make('max_orders')
                    ->numeric()
                    ->nullable()
                    ->default(null)
                    ->helperText('The maximum number of orders allowed (0 or empty for no limit)'),
                Forms\Components\Select::make('cycle_status')
                    ->options([
                        'accumulating' => 'Accumulating',
                        'finalized' => 'Finalized',
                        'failed' => 'Failed (e.g. target not met)',
                    ])
                    ->required()
                    ->default('accumulating'),
                Forms\Components\TextInput::make('target_volume')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('The target volume of the purchase pool'),
                Forms\Components\TextInput::make('current_volume')
                    ->numeric()
                    ->readOnly()
                    ->default(0)
                    ->dehydrated(true),
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
                    // ->dehydrated('edit')
                    ->helperText('Select the product. The vendor will be associated automatically.'),

                Forms\Components\Repeater::make('purchasePoolTiers')
                    ->label('Purchase Pool Tiers')
                    ->relationship('purchasePoolTiers')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The name of the tier (e.g., Gold, Silver, Early Bird)'),
                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->maxLength(255)
                            ->nullable()
                            ->helperText('A brief description of what this tier offers or means'),
                        Forms\Components\TextInput::make('min_volume')
                            ->label('Minimum Volume for this Tier')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->helperText('The cumulative volume required to reach this tier'),
                        Forms\Components\TextInput::make('max_volume')
                            ->label('Maximum Volume for this Tier (Optional)')
                            ->numeric()
                            ->nullable()
                            ->helperText('The maximum cumulative volume for this tier. Leave empty if no upper bound for this tier.'),
                        Forms\Components\TextInput::make('discount_percentage')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->helperText('The discount percentage for this tier (e.g., 5 for 5%)'),
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->defaultItems(1)
                    ->collapsible()
                    ->cloneable()
                    ->reorderable()
                    ->addActionLabel('Add Tier')
                    ->helperText('Define different discount tiers based on accumulated volume.')
                    ->required(),
            ])
            ->columns(1);
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
                Tables\Columns\TextColumn::make('final_achieved_discount_percentage')
                    ->numeric()
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('max_orders')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cycle_status')
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
