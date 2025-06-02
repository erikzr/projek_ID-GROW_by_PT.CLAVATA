<?php
// database/migrations/2024_01_01_000005_create_mutasi_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('jenis_mutasi', ['masuk', 'keluar']);
            $table->integer('jumlah');
            $table->text('keterangan')->nullable();
            $table->integer('stok_sebelum')->nullable();    // TAMBAHAN BARU
            $table->integer('stok_sesudah')->nullable();    // TAMBAHAN BARU
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('produk_lokasi_id')->constrained('produk_lokasi')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi');
    }
};