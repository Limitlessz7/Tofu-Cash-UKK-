<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use App\Filament\Resources\TransactionResource\Pages;
use Illuminate\Support\Str;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationLabel = 'Transaksi';

    protected static array $featureIconMap = [
        'transaksi' => 'heroicon-o-currency-dollar',
        'transaction' => 'heroicon-o-currency-dollar',
        'default' => 'heroicon-o-rectangle-stack',
    ];

    public static function getNavigationIcon(): ?string
    {
        $label = static::$navigationLabel ?? (static::$model ? class_basename(static::$model) : 'default');
        $key = Str::of($label)->lower()->slug('_')->replace('-', '_')->toString();

        return static::$featureIconMap[$key] ?? static::$featureIconMap['default'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        // 🔹 Bagian kiri: data transaksi
                        Section::make('Transaction Info')
                            ->schema([
                                DateTimePicker::make('transaction_date')
                                    ->label('Tanggal')
                                    ->default(now())
                                    ->required(),

                                TextInput::make('paid_amount')
                                    ->label('PAID')
                                    ->numeric()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $total = $get('total') ?? 0;
                                        $set('change_amount', max(0, $state - $total));
                                    }),

                                TextInput::make('change_amount')
                                    ->label('Change')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(true),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'paid' => 'Paid',
                                        'unpaid' => 'Unpaid',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->required()
                                    ->default('unpaid'),
                            ])
                            ->columnSpan(1),

                        // 🔹 Bagian kanan: repeater detail produk
                        Section::make('Detail Transaction')
                            ->schema([
                                Repeater::make('items')
                                    ->relationship('items')
                                    ->label('Detail Produk')
                                    ->minItems(1)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Product')
                                            ->relationship('product', 'name')
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $price = Product::find($state)?->price ?? 0;
                                                $set('price', $price);
                                                $set('subtotal', $price); // default qty=1
                                            }),

                                        TextInput::make('quantity')
                                            ->label('Jumlah')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(1)
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                                $price = $get('price') ?? 0;
                                                $set('subtotal', $state * $price);
                                            }),

                                        TextInput::make('price')
                                            ->label('Harga/item')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(true),

                                        TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(true),
                                    ])
                                    ->columns(4),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total Harga')
                    ->money('idr', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('PAID')
                    ->money('idr', true),

                Tables\Columns\TextColumn::make('change_amount')
                    ->label('Change')
                    ->money('idr', true),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'unpaid',
                        'danger' => 'cancelled',
                    ])
                    ->label('Status'),

                Tables\Columns\TextColumn::make('items')
                    ->label('Produk')
                    ->formatStateUsing(fn ($state, $record) =>
                        collect($record->items)->map(fn ($item) =>
                            "{$item->product?->name} x{$item->quantity}"
                        )->implode(', ')
                    )
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\Filter::make('Hari Ini')
                    ->query(fn ($query) => $query->whereDate('transaction_date', today())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    /**
     * Hitung total & change sebelum disimpan
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $total = 0;

        if (!empty($data['items'])) {
            foreach ($data['items'] as &$item) {
                $product = Product::find($item['product_id']);
                $item['price'] = $product?->price ?? 0;
                $item['subtotal'] = ($item['quantity'] ?? 0) * $item['price'];
                $total += $item['subtotal'];
            }
        }

        $data['total'] = $total;
        $data['change_amount'] = max(0, ($data['paid_amount'] ?? 0) - $total);

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return static::mutateFormDataBeforeCreate($data);
    }
}
