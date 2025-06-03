<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
use App\Models\Address;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('model_identifier')
                    ->label('Link to')
                    ->helperText('Select a User, Vendor, or Warehouse.')
                    ->options(function () {
                        $options = [];

                        $users = User::all()->mapWithKeys(fn ($user) => ['User_'.$user->id => $user->name] // Or a more specific display attribute
                        )->all();
                        if (! empty($users)) {
                            $options['Users'] = $users;
                        }

                        $vendors = Vendor::all()->mapWithKeys(fn ($vendor) => ['Vendor_'.$vendor->id => $vendor->name]
                        )->all();
                        if (! empty($vendors)) {
                            $options['Vendors'] = $vendors;
                        }

                        $warehouses = Warehouse::all()->mapWithKeys(fn ($warehouse) => ['Warehouse_'.$warehouse->id => $warehouse->name]
                        )->all();
                        if (! empty($warehouses)) {
                            $options['Warehouses'] = $warehouses;
                        }

                        return $options;
                    })
                    ->searchable()
                    ->required()
                    ->getOptionLabelUsing(function ($value): ?string {
                        if (empty($value)) {
                            return null;
                        }
                        [$modelPrefix, $modelId] = explode('_', $value, 2);
                        $modelClass = match ($modelPrefix) {
                            'User' => User::class,
                            'Vendor' => Vendor::class,
                            'Warehouse' => Warehouse::class,
                            default => null,
                        };

                        if ($modelClass) {
                            $record = $modelClass::find($modelId);

                            // Adjust 'name' to the actual attribute you want to display
                            return $record ? $modelPrefix.': '.$record->name : null;
                        }

                        return null;
                    }),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('model.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address_line_1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_line_2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country_code')
                    ->searchable(),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
