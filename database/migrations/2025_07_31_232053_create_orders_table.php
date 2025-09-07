<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Foreign key ke users
            $table->unsignedBigInteger('user_id');

            $table->string('tipe_order')->nullable(); // Contoh: 'pickup', 'delivery'
            $table->date('tanggal_order')->nullable();
            $table->decimal('total_order', 12, 2)->default(0); // Total belanja
            $table->string('status_order')->default('pending'); // pending, selesai, cancel, dll
            $table->text('alamat_kirim')->nullable(); // Alamat pengiriman jika ada
            $table->text('catatan')->nullable(); // Catatan opsional dari customer
            $table->string('midtrans_order_id')->nullable(); // ID order dari Midtrans

            $table->timestamps();

            // Constraint foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
