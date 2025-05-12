<?php

// app/Filament/Resources/WarehouseResource/RelationManagers/StorageTiersRelationManager.php

namespace App\Filament\Resources\WarehouseResource\RelationManagers;

use App\Models\StorageTier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table; // Ensure this is imported

class StorageTiersRelationManager extends RelationManager
{
    protected static string $relationship = 'storageTiers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                // warehouse_id is implicitly set by the relation manager
                Forms\Components\Section::make('Tier Limits & Pricing')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('min_space_units')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->helperText('Minimum space units for this tier.'),
                        Forms\Components\TextInput::make('max_space_units')
                            ->numeric()
                            ->nullable()
                            ->helperText('Maximum space units. Blank for no limit.'),
                        Forms\Components\TextInput::make('price_per_space_unit')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->helperText('Price per space unit.'),
                        Forms\Components\Select::make('billing_period')
                            ->options(StorageTier::getBillingPeriodOptions())
                            ->required(),
                    ]),
                Forms\Components\Section::make('Duration')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('min_duration')
                            ->numeric()
                            ->default(1),
                        Forms\Components\Select::make('duration_unit')
                            ->options(StorageTier::getDurationUnitOptions())
                            ->default('month'),
                    ]),
                Forms\Components\KeyValue::make('conditions')
                    ->columnSpanFull()
                    ->reorderable(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                // Forms\Components\Toggle::make('is_active')
                //     ->default(true)
                //     ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('price_per_space_unit')->money('usd')->sortable(),
                Tables\Columns\TextColumn::make('min_duration')->sortable(),
                Tables\Columns\TextColumn::make('duration_unit'),
                Tables\Columns\TextColumn::make('billing_unit'),
                Tables\Columns\TextColumn::make('billing_period'),
                Tables\Columns\TextColumn::make('min_space_units'),
                Tables\Columns\TextColumn::make('max_space_units'),
                Tables\Columns\TextColumn::make('price_per_space_unit'),
                Tables\Columns\TextColumn::make('notes'),
                Tables\Columns\TextColumn::make('billing_period')->sortable(),
                // Tables\Columns\IconColumn::make('is_active')->boolean(),
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
