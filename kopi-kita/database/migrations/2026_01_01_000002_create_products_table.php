<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('Lainnya');
            $table->unsignedInteger('price');
            $table->unsignedInteger('stock')->default(0);
            $table->string('image_path')->nullable();   // foto menu (disimpan di storage/app/public/products)
            $table->string('emoji', 8)->default('☕');   // ikon cadangan bila belum ada foto
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
