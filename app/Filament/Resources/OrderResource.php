<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchasePool;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Order Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Select Vendor & Products')
                        ->schema([
                            Forms\Components\Select::make('selected_vendor_id')
                                ->label('Select Vendor')
                                ->options(
                                    Vendor::all()->pluck('name', 'id')->toArray()
                                )
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set) {
                                    $set('products', []);
                                }),
                            Forms\Components\Repeater::make('products')
                                ->label('Order Products')
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Product')
                                        ->options(function (Get $get) {
                                            $vendorId = $get('../../selected_vendor_id');
                                            if (! $vendorId) {
                                                return [];
                                            }

                                            return Product::where('vendor_id', $vendorId)
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                $set('price_per_unit', $product->price);
                                            } else {
                                                $set('price_per_unit', 0);
                                            }
                                        }),
                                    Forms\Components\TextInput::make('quantity')
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->minValue(1)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                            $pricePerUnit = $get('./price_per_unit');
                                            $currentQuantity = $state;

                                            info([
                                                'price_per_unit_retrieved' => $pricePerUnit,
                                                'quantity_state' => $currentQuantity,
                                                'type_of_price' => gettype($pricePerUnit),
                                                'type_of_quantity' => gettype($currentQuantity),
                                            ]);
                                            $totalPrice = $pricePerUnit * $currentQuantity;
                                            $set('total_price', $totalPrice);
                                            $set('final_line_price', $totalPrice);
                                        }),
                                    Forms\Components\TextInput::make('price_per_unit')
                                        ->label('Price per Unit')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('$')
                                        ->readOnly()
                                        ->dehydrated(true), // Ensure it's saved to DB
                                    Forms\Components\TextInput::make('total_price')
                                        ->label('Subtotal')
                                        ->numeric()
                                        ->prefix('$')
                                        ->readOnly()
                                        ->dehydrated(true),
                                    Forms\Components\Select::make('purchase_pool_id')
                                        ->label('Purchase Pool')
                                        ->options(PurchasePool::all()->pluck('name', 'id'))
                                        ->nullable(),
                                    Forms\Components\Hidden::make('applied_discount_percentage')
                                        ->default(0),
                                    Forms\Components\TextInput::make('final_line_price')
                                        ->label('Final Line Price')
                                        ->numeric()
                                        ->prefix('$')
                                        ->readOnly() // Calculated field
                                        ->dehydrated(true), // Ensure it's saved to DB
                                ])
                                ->columns(3)
                                ->defaultItems(1)
                                ->reorderable(true)
                                ->collapsible()
                                ->cloneable()
                                ->helperText(new HtmlString('Select products from the chosen vendor. Quantities will determine line item totals.')),
                        ]),

                    Step::make('Customer & Address Information')
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText('The customer associated with this order.'),
                            Forms\Components\TextInput::make('phone')
                                ->tel()
                                ->maxLength(20)
                                ->nullable(),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->maxLength(100)
                                ->required()
                                ->helperText('The customer\'s email where the checkout link will be sent.'),
                            Forms\Components\Textarea::make('address')
                                ->label('General Contact Address')
                                ->maxLength(65535)
                                ->columnSpanFull()
                                ->nullable(),
                            Forms\Components\Textarea::make('billing_address')
                                ->maxLength(65535)
                                ->columnSpanFull()
                                ->nullable(),
                            Forms\Components\Textarea::make('shipping_address')
                                ->maxLength(65535)
                                ->columnSpanFull()
                                ->nullable(),
                        ]),
                    Step::make('Review & Confirm')
                        ->schema([
                            Forms\Components\Placeholder::make('order_summary')
                                ->label('Order Summary')
                                ->content(function (Get $get) {
                                    $products = $get('products');
                                    $totalOrderAmount = 0;
                                    $summaryHtml = '<ul>';
                                    foreach ($products as $item) {
                                        $productName = Product::find($item['product_id'])->name ?? 'Unknown Product';
                                        $quantity = $item['quantity'];
                                        $finalLinePrice = number_format($item['final_line_price'] ?? 0, 2);
                                        $totalOrderAmount += ($item['final_line_price'] ?? 0);
                                        $summaryHtml .= "<li>{$productName} (x{$quantity}) - \${$finalLinePrice}</li>";
                                    }
                                    $summaryHtml .= '</ul>';
                                    $summaryHtml .= '<p><strong>Total Order Amount: $'.number_format($totalOrderAmount, 2).'</strong></p>';

                                    return new HtmlString($summaryHtml);
                                }),
                            Forms\Components\Hidden::make('total_amount')
                                ->dehydrated(false), // Do not dehydrate initially, set in mutate
                            Forms\Components\Hidden::make('final_amount')
                                ->dehydrated(false), // Do not dehydrate initially, set in mutate
                            Forms\Components\Hidden::make('purchase_cycle_id') // If a purchase cycle is selected
                                ->dehydrated(false),
                            Forms\Components\Hidden::make('vendor_id')
                                ->dehydrated(false),
                            Forms\Components\Hidden::make('quantity')
                                ->dehydrated(false),
                            Forms\Components\Hidden::make('stripe_session_id')
                                ->dehydrated(false),
                            Forms\Components\Hidden::make('payment_intent_id')
                                ->dehydrated(false),
                            Forms\Components\Hidden::make('status')
                                ->default('created')
                                ->dehydrated(true),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchaseCycle.name')
                    ->label('Purchase Cycle')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_amount')
                    ->money()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'created' => 'Created',
                        'payment_authorized' => 'Payment Authorized',
                        'pending_finalization' => 'Pending Finalization',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
