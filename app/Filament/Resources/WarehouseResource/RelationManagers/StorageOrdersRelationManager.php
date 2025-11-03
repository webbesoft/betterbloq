<?php

// app/Filament/Resources/WarehouseResource/RelationManagers/StorageOrdersRelationManager.php

namespace App\Filament\Resources\WarehouseResource\RelationManagers;

use App\Models\Order;
use App\Models\StorageOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StorageOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'storageOrders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'id')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Original Order'),
                // warehouse_id is implicitly set
                Forms\Components\DatePicker::make('requested_storage_start_date')
                    ->required(),
                Forms\Components\TextInput::make('requested_storage_duration_estimate')
                    ->maxLength(255),
                Forms\Components\TextInput::make('preliminary_storage_cost_estimate')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Select::make('status')
                    ->options(StorageOrder::getStatusOptions())
                    ->required()
                    ->default('pending_arrival'),
                Forms\Components\DatePicker::make('actual_storage_start_date')->nullable(),
                Forms\Components\TextInput::make('manually_entered_total_space_units')->numeric()->nullable(),
                Forms\Components\Select::make('calculated_space_unit_type')
                    ->options(StorageOrder::getSpaceUnitTypeOptions()) // Assuming static method
                    ->nullable(),
                Forms\Components\Select::make('applied_storage_tier_id')
                    ->relationship('appliedStorageTier', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id') // Or another meaningful attribute
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('order.id')->label('Order ID')->searchable()->sortable(), // <<<< Order ID
                Tables\Columns\TextColumn::make('status')->badge()->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_arrival' => 'warning',
                        'stored' => 'success',
                        'partially_retrieved' => 'info',
                        'retrieved' => 'gray',
                        'cancelled' => 'danger',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('actual_storage_start_date')->date()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make() // To navigate to the full StorageOrderResource view
                    ->url(fn (StorageOrder $record): string => \App\Filament\Resources\StorageOrderResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
