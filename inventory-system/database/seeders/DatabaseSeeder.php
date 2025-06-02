<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Lokasi;
use App\Models\Produk;
use App\Models\ProdukLokasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users
        $admin = User::create([
            'nama' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user1 = User::create([
            'nama' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::create([
            'nama' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Create test locations
        $gudangA = Lokasi::create([
            'kode_lokasi' => 'GDA',
            'nama_lokasi' => 'Gudang A',
            'deskripsi' => 'Gudang utama untuk produk elektronik',
        ]);

        $gudangB = Lokasi::create([
            'kode_lokasi' => 'GDB',
            'nama_lokasi' => 'Gudang B',
            'deskripsi' => 'Gudang untuk produk fashion',
        ]);

        $toko1 = Lokasi::create([
            'kode_lokasi' => 'TK1',
            'nama_lokasi' => 'Toko Cabang 1',
            'deskripsi' => 'Toko cabang di Jakarta',
        ]);

        // Create test products
        $laptop = Produk::create([
            'nama_produk' => 'Laptop ASUS ROG',
            'kode_produk' => 'LP001',
            'kategori' => 'Elektronik',
            'satuan' => 'Unit',
            'deskripsi' => 'Laptop gaming ASUS ROG Strix',
            'harga' => 15000000.00,
        ]);

        $mouse = Produk::create([
            'nama_produk' => 'Mouse Wireless',
            'kode_produk' => 'MS001',
            'kategori' => 'Elektronik',
            'satuan' => 'Unit',
            'deskripsi' => 'Mouse wireless Logitech',
            'harga' => 250000.00,
        ]);

        $kaos = Produk::create([
            'nama_produk' => 'Kaos Polo',
            'kode_produk' => 'KS001',
            'kategori' => 'Fashion',
            'satuan' => 'Pcs',
            'deskripsi' => 'Kaos polo cotton combed',
            'harga' => 85000.00,
        ]);

        $celana = Produk::create([
            'nama_produk' => 'Celana Jeans',
            'kode_produk' => 'CL001',
            'kategori' => 'Fashion',
            'satuan' => 'Pcs',
            'deskripsi' => 'Celana jeans premium',
            'harga' => 150000.00,
        ]);

        // Create product-location relationships with initial stock
        ProdukLokasi::create([
            'produk_id' => $laptop->id,
            'lokasi_id' => $gudangA->id,
            'stok' => 10,
        ]);

        ProdukLokasi::create([
            'produk_id' => $laptop->id,
            'lokasi_id' => $toko1->id,
            'stok' => 2,
        ]);

        ProdukLokasi::create([
            'produk_id' => $mouse->id,
            'lokasi_id' => $gudangA->id,
            'stok' => 50,
        ]);

        ProdukLokasi::create([
            'produk_id' => $mouse->id,
            'lokasi_id' => $toko1->id,
            'stok' => 15,
        ]);

        ProdukLokasi::create([
            'produk_id' => $kaos->id,
            'lokasi_id' => $gudangB->id,
            'stok' => 100,
        ]);

        ProdukLokasi::create([
            'produk_id' => $kaos->id,
            'lokasi_id' => $toko1->id,
            'stok' => 25,
        ]);

        ProdukLokasi::create([
            'produk_id' => $celana->id,
            'lokasi_id' => $gudangB->id,
            'stok' => 75,
        ]);

        ProdukLokasi::create([
            'produk_id' => $celana->id,
            'lokasi_id' => $toko1->id,
            'stok' => 20,
        ]);
    }
}