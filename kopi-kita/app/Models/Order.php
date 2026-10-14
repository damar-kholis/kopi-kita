<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use RuntimeException;

class Order extends Model
{
    public const STATUS_LABELS = [
        'baru'       => 'Baru',
        'diproses'   => 'Diproses',
        'selesai'    => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    protected $fillable = ['code', 'user_id', 'customer_name', 'note', 'total', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['baru', 'diproses'], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /** Kode 5 karakter (tanpa huruf/angka yang membingungkan seperti O, 0, I, 1). */
    public static function generateCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 5; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /** Kembalikan stok (dipakai saat pesanan dibatalkan / dihapus). Panggil di dalam DB::transaction. */
    public function restoreStock(): void
    {
        $this->loadMissing('items');
        foreach ($this->items as $item) {
            if (! $item->product_id) {
                continue;
            }
            $product = Product::lockForUpdate()->find($item->product_id);
            $product?->increment('stock', $item->qty);
        }
    }

    /** Potong stok lagi (dipakai saat pesanan batal dibuka kembali). Panggil di dalam DB::transaction. */
    public function deductStock(): void
    {
        $this->loadMissing('items');
        $locked = [];
        $problems = [];

        foreach ($this->items as $item) {
            if (! $item->product_id) {
                continue;
            }
            $product = Product::lockForUpdate()->find($item->product_id);
            if (! $product || $product->stock < $item->qty) {
                $problems[] = $item->product_name . ' (sisa ' . ($product->stock ?? 0) . ')';
                continue;
            }
            $locked[] = [$product, $item->qty];
        }

        if ($problems) {
            throw new RuntimeException('Stok tidak cukup: ' . implode(', ', $problems));
        }

        foreach ($locked as [$product, $qty]) {
            $product->decrement('stock', $qty);
        }
    }
}
