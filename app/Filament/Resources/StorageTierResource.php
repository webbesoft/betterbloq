<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StorageTierResource\Pages;
use App\Models\StorageTier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StorageTierResource extends Resource
{
    protected static ?string $model = StorageTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Storage Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Optional. If this tier is specific to a warehouse.'),
                Forms\Components\Section::make('Tier Limits & Pricing')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('min_space_units')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->helperText('Minimum space units (e.g., sq ft, pallets) for this tier.'),
                        Forms\Components\TextInput::make('max_space_units')
                            ->numeric()
                            ->nullable()
                            ->helperText('Maximum space units. Leave blank for no upper limit.'),
                        Forms\Components\TextInput::make('price_per_space_unit')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->helperText('Price per the defined space unit for this tier.'),
                        Forms\Components\Select::make('billing_period')
                            ->options([
                                'day' => 'Per Day',
                                'week' => 'Per Week',
                                'month' => 'Per Month',
                                'quarter' => 'Per Quarter',
                                'year' => 'Per Year',
                            ])
                            ->required(),
                    ]),
                Forms\Components\Section::make('Duration')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('min_duration')
                            ->numeric()
                            ->default(1)
                            ->helperText('Minimum storage duration for this tier.'),
                        Forms\Components\Select::make('duration_unit')
                            ->options([
                                'day' => 'Day(s)',
                                'week' => 'Week(s)',
                                'month' => 'Month(s)',
                            ])
                            ->default('month'),
                    ]),
                Forms\Components\KeyValue::make('conditions')
                    ->helperText('JSON conditions. E.g., key: "product_category", value: "lumber" or key: "requires_climate_control", value: true. The `Product.storage_unit_of_measure` will typically define the unit for space calculations.')
                    ->columnSpanFull()
                    ->reorderable(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                // Forms\Components\Toggle::make('is_active')
                //     ->default(true)
                //     ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')->sortable()->searchable()->placeholder('Global Tier'),
                Tables\Columns\TextColumn::make('price_per_space_unit')->money('usd')->sortable(),
                Tables\Columns\TextColumn::make('billing_period')->sortable(),
                Tables\Columns\TextColumn::make('min_space_units')->sortable(),
                Tables\Columns\TextColumn::make('max_space_units')->sortable()->placeholder('No Max'),
                // Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse'),
                // Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListStorageTiers::route('/'),
            'create' => Pages\CreateStorageTier::route('/create'),
            'edit' => Pages\EditStorageTier::route('/{record}/edit'),
            'view' => Pages\ViewStorageTier::route('/{record}'),
        ];
    }
}
