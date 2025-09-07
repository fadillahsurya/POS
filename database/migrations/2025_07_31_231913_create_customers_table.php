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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key ke tabel users
            $table->string('nama_customer');
            $table->text('alamat')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('foto')->nullable(); // path foto atau nama file
            $table->timestamps();

            // Tambahkan constraint foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
