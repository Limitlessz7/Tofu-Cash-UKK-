<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $table = 'transaction_items';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'float',
        'subtotal' => 'float',
    ];

    /**
     * 🔹 Update stok otomatis saat create/update/delete
     */
    protected static function booted()
    {
        // Kurangi stok saat item dibuat
        static::created(function ($item) {
            $product = $item->product;
            if ($product) {
                $product->decrement('stock', $item->quantity);
            }
        });

        // Tambah stok kembali saat item dihapus
        static::deleted(function ($item) {
            $product = $item->product;
            if ($product) {
                $product->increment('stock', $item->quantity);
            }
        });

        // Update stok saat item diubah
        static::updating(function ($item) {
            $product = $item->product;
            if ($product) {
                $originalQty = $item->getOriginal('quantity');
                $diff = $item->quantity - $originalQty;
                if ($diff !== 0) {
                    $product->decrement('stock', $diff);
                }
            }
        });
    }

    /**
     * 🔹 Relasi ke Transaction
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * 🔹 Relasi ke Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
