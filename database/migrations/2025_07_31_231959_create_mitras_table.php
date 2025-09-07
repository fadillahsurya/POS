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
        Schema::create('mitras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // foreign key ke users
            $table->string('nama_mitra');
            $table->string('nama_usaha');
            $table->string('no_hp')->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable(); // path atau nama file foto
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitras');
    }
};
