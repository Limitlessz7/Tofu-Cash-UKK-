<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Product;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)
                ->schema([
                    // ================= KIRI =================
                    Section::make('Informasi Transaksi')
                        ->schema([
                            DateTimePicker::make('transaction_date')
                                ->label('Tanggal Transaksi')
                                ->default(now())
                                ->required(),

                            TextInput::make('total')
                                ->label('Total')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(true)
                                ->default(0)
                                ->reactive()
                                ->afterStateHydrated(function ($set, $record) {
                                    if ($record && $record->items) {
                                        $set('total', $record->items->sum('subtotal'));
                                    }
                                }),

                            TextInput::make('paid_amount')
                                ->label('Dibayar (PAID)')
                                ->numeric()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $total = (float) ($get('total') ?? 0);
                                    $change = max(0, $state - $total);
                                    $set('change_amount', $change);
                                    $set('status', $state >= $total ? 'paid' : 'unpaid');
                                })
                                ->afterStateHydrated(function ($set, $record) {
                                    if ($record) {
                                        $total = $record->items->sum('subtotal');
                                        $paid = $record->paid_amount ?? 0;
                                        $change = max(0, $paid - $total);
                                        $set('change_amount', $change);
                                        $set('status', $paid >= $total ? 'paid' : 'unpaid');
                                    }
                                }),

                            TextInput::make('change_amount')
                                ->label('Kembalian (Change)')
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
                                ->default('unpaid')
                                ->required(),
                        ])
                        ->columnSpan(1),

                    // ================= KANAN =================
                    Section::make('Detail Produk')
                        ->schema([
                            Repeater::make('items')
                                ->relationship('items')
                                ->label('Produk Dibeli')
                                ->minItems(1)
                                ->schema([
                                    Select::make('product_id')
                                        ->label('Produk')
                                        ->relationship('product', 'name')
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $product = Product::find($state);
                                            $price = $product?->price ?? 0;

                                            // Set harga awal
                                            $set('price', $price);

                                            // Ambil quantity
                                            $qty = (float) ($get('quantity') ?? 1);

                                            // Cek stok produk
                                            if ($product && $qty > $product->stock) {
                                                $qty = $product->stock;
                                                $set('quantity', $qty);

                                                Notification::make()
                                                    ->title('Jumlah melebihi stok!')
                                                    ->body("Stok tersisa: {$product->stock}")
                                                    ->warning()
                                                    ->send();
                                            }

                                            // Set subtotal
                                            $set('subtotal', $price * $qty);

                                            self::recalculateParent($set, $get);
                                        }),

                                    TextInput::make('quantity')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $productId = $get('product_id');
                                            $product = Product::find($productId);

                                            if ($product) {
                                                if ($state > $product->stock) {
                                                    $set('quantity', $product->stock);

                                                    Notification::make()
                                                        ->title('Jumlah melebihi stok!')
                                                        ->body("Stok tersedia hanya {$product->stock}")
                                                        ->warning()
                                                        ->send();

                                                    $state = $product->stock;
                                                }
                                            }

                                            $price = (float) ($get('price') ?? 0);
                                            $set('subtotal', $price * $state);

                                            self::recalculateParent($set, $get);
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
                                ->columns(4)
                                ->defaultItems(1)
                                ->addActionLabel('Tambah Produk'),
                        ])
                        ->columnSpan(1),
                ]),
        ]);
    }

    /**
     * Recalculate total, change, and status dynamically
     */
    private static function recalculateParent(callable $set, callable $get): void
    {
        $items = $get('../../items') ?? [];
        $total = 0;
        foreach ($items as $item) {
            $total += (float) ($item['subtotal'] ?? 0);
        }

        $set('../../total', $total);

        $paid = (float) ($get('../../paid_amount') ?? 0);
        $change = max(0, $paid - $total);
        $set('../../change_amount', $change);
        $set('../../status', $paid >= $total ? 'paid' : 'unpaid');
    }

    // ================= TABEL =================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i'),

                Tables\Columns\TextColumn::make('items_list')
                    ->label('Produk & Jumlah')
                    ->getStateUsing(fn($record) =>
                        $record->items->map(fn($item) =>
                            $item->product?->name . ' ×' . $item->quantity
                        )->join(', ')
                    )
                    ->limit(50)
                    ->tooltip(fn($record) =>
                        $record->items->map(fn($item) =>
                            $item->product?->name . ' ×' . $item->quantity
                        )->join(', ')
                    ),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('change_amount')
                    ->label('Kembalian')
                    ->money('IDR'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'unpaid',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    // ================= MUTASI DATA =================
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return self::calculateTotals($data);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return self::calculateTotals($data);
    }

    private static function calculateTotals(array $data): array
    {
        $total = 0;

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as &$item) {
                $product = Product::find($item['product_id']);
                $price = $product?->price ?? 0;
                $stock = $product?->stock ?? 0;

                // Batasi quantity agar tidak lebih dari stok
                if (($item['quantity'] ?? 1) > $stock) {
                    $item['quantity'] = $stock;
                }

                $subtotal = $price * ($item['quantity'] ?? 1);
                $item['price'] = $price;
                $item['subtotal'] = $subtotal;
                $total += $subtotal;
            }
            $data['total'] = $total;
        }

        $paid = $data['paid_amount'] ?? 0;
        $data['change_amount'] = max(0, $paid - $total);
        $data['status'] = $paid >= $total ? 'paid' : 'unpaid';

        return $data;
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
}
