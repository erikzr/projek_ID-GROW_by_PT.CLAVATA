<?php
// app/Models/Lokasi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'lokasi';

    protected $fillable = [
        'kode_lokasi',
        'nama_lokasi',
        'deskripsi',
    ];

    public function produk()
    {
        return $this->belongsToMany(Produk::class, 'produk_lokasi')
                    ->withPivot('stok')
                    ->withTimestamps();
    }

    public function produkLokasi()
    {
        return $this->hasMany(ProdukLokasi::class);
    }
}