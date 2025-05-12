<?php

// app/Filament/Resources/StorageOrderResource/RelationManagers/StorageOrderLineItemsRelationManager.php

namespace App\Filament\Resources\StorageOrderResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StorageOrderLineItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'storageOrderLineItems'; // Matches relationship on StorageOrder model

    public function form(Form $form): Form
    {
        // Form for creating/editing line items
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $product = Product::find($state);
                        if ($product) {
                            $set('manual_dimensions_length', $product->default_length);
                        }
                    }),
                Forms\Components\TextInput::make('quantity_stored')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                Forms\Components\Section::make('Manual Dimensions (Optional Override)')
                    ->description('Enter if actual stored dimensions differ from product defaults, or for items without predefined dimensions.')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('manual_dimensions_length')
                            ->numeric()->nullable()->suffix('units'),
                        Forms\Components\TextInput::make('manual_dimensions_width')
                            ->numeric()->nullable()->suffix('units'),
                        Forms\Components\TextInput::make('manual_dimensions_height')
                            ->numeric()->nullable()->suffix('units'),
                    ]),
                Forms\Components\TextInput::make('calculated_space_for_item')
                    ->numeric()
                    ->disabled() // This would likely be calculated
                    ->helperText('Space calculated for this line item (e.g., based on dimensions or product type).'),
                Forms\Components\DatePicker::make('entry_date')
                    ->default(now()),
                Forms\Components\DatePicker::make('retrieval_date')
                    ->nullable(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name') // Or another meaningful attribute
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('quantity_stored')->sortable(),
                Tables\Columns\TextColumn::make('calculated_space_for_item')->sortable()->placeholder('N/A'),
                Tables\Columns\TextColumn::make('entry_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('retrieval_date')->date()->sortable()->placeholder('Still Stored'),
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
