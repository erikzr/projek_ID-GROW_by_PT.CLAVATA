<?php
// app/Models/Produk.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'nama_produk',
        'kode_produk',
        'kategori',
        'satuan',
        'deskripsi',
        'harga',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
    ];

    public function lokasi()
    {
        return $this->belongsToMany(Lokasi::class, 'produk_lokasi')
                    ->withPivot('stok')
                    ->withTimestamps();
    }

    public function produkLokasi()
    {
        return $this->hasMany(ProdukLokasi::class);
    }

    public function mutasi()
    {
        return $this->hasManyThrough(Mutasi::class, ProdukLokasi::class);
    }
}