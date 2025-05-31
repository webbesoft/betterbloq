<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Warehouse;
use BulkCreatePurchasePoolsAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Product Details')->tabs([
                    Forms\Components\Tabs\Tab::make('General')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(150)
                                ->columnSpanFull(),
                            Forms\Components\MarkdownEditor::make('description')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->prefix('$'),
                            Forms\Components\TextInput::make('unit')
                                ->maxLength(10)
                                ->helperText('e.g., piece, kg, m, box'),
                            Forms\Components\Select::make('category_id')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\Select::make('vendor_id')
                                ->relationship('vendor', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\TextInput::make('delivery_time')
                                ->numeric()
                                ->label('Delivery Time (days)')
                                ->integer(),
                            Forms\Components\FileUpload::make('image')
                                ->image()
                                ->directory('product-images')
                                ->columnSpanFull()
                                ->helperText('Upload a product image.'),
                        ])->columns(2),

                    Forms\Components\Tabs\Tab::make('Stripe & Identifiers')
                        ->schema([
                            Forms\Components\TextInput::make('stripe_product_id')
                                ->maxLength(255)
                                ->readOnly()
                                ->default(Str::uuid()->toString())
                                ->label('Stripe Product ID'),
                            Forms\Components\TextInput::make('stripe_price_id')
                                ->maxLength(255)
                                ->default(Str::uuid()->toString())
                                ->readOnly()
                                ->label('Stripe Price ID'),
                        ]),

                    Forms\Components\Tabs\Tab::make('Storage & Handling')
                        ->schema([
                            Forms\Components\Select::make('preferred_warehouse_id')
                                ->label('Preferred Storage Provider')
                                ->options(Warehouse::all()->pluck('name', 'id'))
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('storable')
                                ->inline(false),
                            Forms\Components\TextInput::make('storage_unit_of_measure')
                                ->maxLength(255)
                                ->label('Storage Unit of Measure'),
                            Forms\Components\Fieldset::make('Default Dimensions')
                                ->schema([
                                    Forms\Components\TextInput::make('default_length')
                                        ->numeric()->suffix('units'), // Specify units if consistent
                                    Forms\Components\TextInput::make('default_width')
                                        ->numeric()->suffix('units'),
                                    Forms\Components\TextInput::make('default_height')
                                        ->numeric()->suffix('units'),
                                ])->columns(3),
                            Forms\Components\Toggle::make('is_stackable')
                                ->inline(false),
                            Forms\Components\TextInput::make('max_stack_height_units')
                                ->numeric()
                                ->integer()
                                ->label('Max Stack Height (in storage units)'),
                            Forms\Components\CheckboxList::make('storage_conditions_required')
                                ->label('Storage Conditions Required')
                                ->options([
                                    'dry' => 'Dry Environment',
                                    'ventilated' => 'Well Ventilated',
                                    'refrigerated' => 'Refrigerated',
                                    'frozen' => 'Frozen',
                                    'controlled_temperature' => 'Controlled Temperature',
                                    'away_from_direct_sunlight' => 'Away from Direct Sunlight',
                                ])
                                ->columns(2)
                                ->helperText('Select all applicable conditions.'),
                            Forms\Components\Textarea::make('storage_handling_notes')
                                ->label('Storage & Handling Notes')
                                ->columnSpanFull(),
                        ])->columns(2),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('usd') // Or your currency
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name') // Assuming Category relationship
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.name') // Assuming Vendor relationship
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('storable')
                    ->boolean(),
                Tables\Columns\TextColumn::make('delivery_time')
                    ->numeric()
                    ->suffix(' days')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('unit')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                Tables\Filters\SelectFilter::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->label('Vendor'),
                Tables\Filters\TernaryFilter::make('storable'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    BulkCreatePurchasePoolsAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
            ImagesRelationManager::class,
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
