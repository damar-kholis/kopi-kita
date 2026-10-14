<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- 2 akun awal ---
        $accounts = [
            ['name' => 'Kasir Kopi Kita', 'email' => 'kasir@kopikita.test',     'password' => 'kasir123',     'role' => 'kasir'],
            ['name' => 'Pelanggan Demo',  'email' => 'pelanggan@kopikita.test', 'password' => 'pelanggan123', 'role' => 'pelanggan'],
        ];

        foreach ($accounts as $a) {
            if (! User::where('email', $a['email'])->exists()) {
                User::forceCreate([
                    'name'              => $a['name'],
                    'email'             => $a['email'],
                    'password'          => Hash::make($a['password']),
                    'role'              => $a['role'],
                    'email_verified_at' => now(),
                ]);
            }
        }

        // --- menu contoh (foto bisa diunggah kasir lewat menu Produk) ---
        if (Product::count() === 0) {
            $menu = [
                ['Espresso',            'Kopi',     15000, 30, '☕'],
                ['Caffe Latte',         'Kopi',     24000, 25, '🥛'],
                ['Kopi Susu Gula Aren', 'Kopi',     22000, 40, '🧋'],
                ['Matcha Latte',        'Non-Kopi', 26000, 15, '🍵'],
                ['Coklat Panas',        'Non-Kopi', 22000, 15, '🍫'],
                ['Es Teh Lemon',        'Non-Kopi', 14000, 30, '🍋'],
                ['Croissant Butter',    'Makanan',  20000,  8, '🥐'],
                ['Nasi Goreng Kampung', 'Makanan',  28000, 12, '🍛'],
                ['Kentang Goreng',      'Camilan',  18000, 20, '🍟'],
                ['Pisang Bakar Coklat', 'Camilan',  17000,  0, '🍌'],
            ];

            foreach ($menu as [$name, $category, $price, $stock, $emoji]) {
                Product::create(compact('name', 'category', 'price', 'stock', 'emoji'));
            }
        }
    }
}
