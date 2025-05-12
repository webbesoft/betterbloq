<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StorageOrderResource\Pages;
use App\Models\StorageOrder;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StorageOrderResource extends Resource
{
    protected static ?string $model = StorageOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Storage Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Basic Information')
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
                                ->helperText('Link to the original purchase order.'),
                            Forms\Components\Select::make('warehouse_id')
                                ->relationship('warehouse', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->label('Storage Warehouse'),
                            Forms\Components\DatePicker::make('requested_storage_start_date')
                                ->required(),
                            Forms\Components\TextInput::make('requested_storage_duration_estimate')
                                ->maxLength(255)
                                ->helperText('E.g., "3 months", "approx 60 days"'),
                            Forms\Components\TextInput::make('preliminary_storage_cost_estimate')
                                ->numeric()
                                ->prefix('$')
                                ->helperText('Initial estimate shown to user, if any.'),
                        ]),
                    Step::make('Actual Storage Details (Post-Arrival)')
                        ->schema([
                            Forms\Components\DatePicker::make('actual_storage_start_date')->nullable(),
                            Forms\Components\DatePicker::make('actual_storage_end_date')->nullable(),
                            Forms\Components\TextInput::make('manually_entered_total_space_units')
                                ->numeric()
                                ->nullable()
                                ->helperText('Actual total space units used by the order (e.g., 120 for 120 sq ft). Entered by admin upon goods arrival.'),
                            Forms\Components\Select::make('calculated_space_unit_type')
                                ->options([
                                    'sq_ft_floor' => 'Square Feet (Floor)',
                                    'cubic_meter' => 'Cubic Meter',
                                    'pallet_space' => 'Pallet Space',
                                ])
                                ->nullable()
                                ->helperText('The unit type used for billing this order.'),
                            Forms\Components\Select::make('applied_storage_tier_id')
                                ->relationship('appliedStorageTier', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Storage tier applied to this order.'),
                            Forms\Components\TextInput::make('actual_rate_per_unit_per_period')
                                ->numeric()
                                ->prefix('$')
                                ->nullable(),
                            Forms\Components\Select::make('billing_period_for_actuals')
                                ->options([
                                    'day' => 'Per Day',
                                    'week' => 'Per Week',
                                    'month' => 'Per Month',
                                ])
                                ->nullable(),
                            Forms\Components\DatePicker::make('next_billing_date')->nullable(),
                            Forms\Components\TextInput::make('total_actual_storage_cost_to_date')
                                ->numeric()
                                ->prefix('$')
                                ->disabled() // Typically calculated
                                ->helperText('Updated via billing cycles.'),
                        ]),
                    Step::make('Status & Notes')
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'pending_arrival' => 'Pending Arrival',
                                    'stored' => 'Stored',
                                    'partially_retrieved' => 'Partially Retrieved',
                                    'retrieved' => 'Retrieved',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required()
                                ->default('pending_arrival'),
                            Forms\Components\Textarea::make('notes')
                                ->columnSpanFull(),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('user.name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('order_id')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('warehouse.name')->sortable()->searchable(),
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
                Tables\Columns\TextColumn::make('manually_entered_total_space_units')->sortable(),
                Tables\Columns\TextColumn::make('appliedStorageTier.name')->sortable()->placeholder('N/A'),
                Tables\Columns\TextColumn::make('next_billing_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_arrival' => 'Pending Arrival',
                        'stored' => 'Stored',
                        'partially_retrieved' => 'Partially Retrieved',
                        'retrieved' => 'Retrieved',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->label('Warehouse'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorageOrders::route('/'),
            'create' => Pages\CreateStorageOrder::route('/create'),
            'view' => Pages\ViewStorageOrder::route('/{record}'),
            'edit' => Pages\EditStorageOrder::route('/{record}/edit'),
        ];
    }
}
