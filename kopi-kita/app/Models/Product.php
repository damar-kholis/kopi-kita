<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'category', 'price', 'stock', 'image_path', 'emoji'];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** URL foto menu, atau null bila belum ada. */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('uploads/' . $this->image_path) : null;
    }

    /** Label stok untuk pelanggan: [kelas css, teks]. */
    public function stockBadge(): array
    {
        if ($this->stock <= 0) {
            return ['out', 'Habis'];
        }
        if ($this->stock <= 5) {
            return ['low', 'Sisa ' . $this->stock];
        }
        return ['', 'Stok ' . $this->stock];
    }
}
