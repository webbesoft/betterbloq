<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchasePoolTemplateResource\Pages;
use App\Filament\Resources\PurchasePoolTemplateResource\RelationManagers\TiersRelationManager;
use App\Models\Product;
use App\Models\PurchasePoolTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchasePoolTemplateResource extends Resource
{
    protected static ?string $model = PurchasePoolTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Order Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('The name of the purchase pool template'),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->columnSpanFull()
                    ->helperText('The description of the purchase pool template'),
                Forms\Components\TextInput::make('min_orders_for_discount')
                    ->label('Minimum Orders for Discount')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('The minimum number of orders required to apply the discount'),
                Forms\Components\TextInput::make('max_orders')
                    ->label('Maximum Orders')
                    ->numeric()
                    ->default(null)
                    ->helperText('The maximum number of orders allowed in the purchase pool'),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->required()
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'closed' => 'Closed',
                    ])
                    ->helperText('The status of the purchase pool template'),
                Forms\Components\TextInput::make('target_volume')
                    ->label('Target Volume')
                    ->required()
                    ->numeric()
                    ->default(0.00)
                    ->helperText('The target volume of the purchase pool'),
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

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['product_id'])) {
            $product = Product::find($data['product_id']);
            if ($product) {
                $data['vendor_id'] = $product->vendor_id;
            } else {
            }
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_orders_for_discount')
                    ->label('Minimum Orders for Discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_orders')
                    ->label('Maximum Orders')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_volume')
                    ->label('Target Volume')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
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
            TiersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchasePoolTemplates::route('/'),
            'create' => Pages\CreatePurchasePoolTemplate::route('/create'),
            'edit' => Pages\EditPurchasePoolTemplate::route('/{record}/edit'),
        ];
    }
}
