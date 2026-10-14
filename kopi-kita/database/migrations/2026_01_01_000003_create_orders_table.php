<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();                       // kode pesanan yang mudah dibaca
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');                            // "pesanan atas nama"
            $table->text('note')->nullable();
            $table->unsignedInteger('total');
            $table->string('status', 20)->default('baru');              // baru | diproses | selesai | dibatalkan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
