<?php
// database/migrations/2024_01_01_000004_create_produk_lokasi_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_lokasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->foreignId('lokasi_id')->constrained('lokasi')->onDelete('cascade');
            $table->integer('stok')->default(0);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate produk-lokasi combinations
            $table->unique(['produk_id', 'lokasi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_lokasi');
    }
};