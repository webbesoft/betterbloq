<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Filament\Resources\VendorResource\RelationManagers\AddressRelationManager;
use App\Models\User;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Entity Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required(),
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
                Forms\Components\TextInput::make('prep_time')
                    ->label('Preparation Time (Days)')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->helperText('Days needed to prepare order after pool ends.'),
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->required()
                    ->options(User::all()->pluck('name', 'id'))
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prep_time')
                    ->label('Prep Time (Days)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
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
            // AddressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
