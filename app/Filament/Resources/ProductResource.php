<?php

namespace App\Filament\Resources;

use App\Models\Product;
use App\Filament\Resources\ProductResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Str;

class ProductResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $navigationGroup = 'Manajemen Data';
    protected static ?string $slug = 'produk';

    /**
     * Otomatis pilih icon berdasarkan label
     */
    protected static array $featureIconMap = [
        'produk' => 'heroicon-o-cube',
        'product' => 'heroicon-o-cube',
        'default' => 'heroicon-o-rectangle-stack',
    ];

    public static function getNavigationIcon(): ?string
    {
        $label = static::$navigationLabel ?? (static::$model ? class_basename(static::$model) : 'default');
        $key = Str::of($label)->lower()->slug('_')->replace('-', '_')->toString();

        return static::$featureIconMap[$key] ?? static::$featureIconMap['default'];
    }

    /**
     * FORM — untuk Create & Edit Produk
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->description('Isi detail produk dengan lengkap.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: baso tahu'),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Tambahkan deskripsi singkat produk...')
                            ->rows(3),

                        TextInput::make('price')
                            ->label('Harga (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),

                        TextInput::make('stock')
                            ->label('Stok')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('unit'),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * TABLE — untuk List Produk
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', true)
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y - H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada produk.')
            ->emptyStateDescription('Tambahkan produk baru untuk memulai penjualan.');
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Halaman (Pages)
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
