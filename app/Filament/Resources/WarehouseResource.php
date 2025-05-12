<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Filament\Resources\WarehouseResource\RelationManagers\StorageOrdersRelationManager;
use App\Filament\Resources\WarehouseResource\RelationManagers\StorageTiersRelationManager;
use App\Models\User;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Entity Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('phone')
                    ->maxLength(255)
                    ->helperText('The warehouse phone number')
                    ->columnSpanFull(),
                Forms\Components\Section::make('Address')
                    ->relationship('address')
                    ->schema([
                        Forms\Components\TextInput::make('address_line_1')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_line_2')
                            ->maxLength(255)
                            ->default(null),
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('state')
                            ->options([
                                'texas' => 'Texas',
                            ])
                            ->default('texas')
                            ->required(),
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(255)
                            ->default(null),
                        Forms\Components\Select::make('country_code')
                            ->required()
                            ->options([
                                'usa' => 'USA',
                            ])
                            ->default('usa'),
                    ]),
                Forms\Components\Section::make('Capacity & Pricing')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('total_capacity')
                            ->numeric()
                            ->required()
                            ->suffix('units (e.g., sq ft, cubic meters)'),
                        Forms\Components\TextInput::make('total_capacity_unit')
                            ->helperText('Measurement units for the warehouse capacity')
                            ->required(),
                        Forms\Components\TextInput::make('available_capacity')
                            ->numeric()
                            ->helperText('This can be automatically updated via a separate process or manually maintained.')
                            ->suffix('units'),
                        Forms\Components\TextInput::make('default_storage_price_per_unit')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        Forms\Components\Select::make('default_storage_price_period')
                            ->options([
                                'days' => 'Per Day',
                                'weeks' => 'Per Week',
                                'months' => 'Per Month',
                                'quarters' => 'Per Quarter',
                                'years' => 'Per Year',
                            ])
                            ->required(),
                    ]),
                Forms\Components\TagsInput::make('supported_storage_conditions')
                    ->helperText('E.g., dry, ventilated, climate_controlled, refrigerated. Press enter to add.')
                    ->columnSpanFull(),
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->required()
                    ->options(User::all()->pluck('name', 'id'))
                    ->columnSpanFull()
                    ->searchable(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('total_capacity')->sortable(),
                Tables\Columns\TextColumn::make('available_capacity')->sortable(),
                Tables\Columns\TextColumn::make('default_storage_price_per_unit')
                    ->money('usd') // Assuming USD, adjust as needed
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_storage_price_period')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
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
            StorageTiersRelationManager::class,
            StorageOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
