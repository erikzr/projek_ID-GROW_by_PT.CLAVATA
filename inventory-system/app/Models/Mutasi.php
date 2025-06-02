<?php
// app/Models/Mutasi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mutasi extends Model
{
    use HasFactory;

    protected $table = 'mutasi';

    protected $fillable = [
        'tanggal',
        'jenis_mutasi',
        'jumlah',
        'keterangan',
        'user_id',
        'produk_lokasi_id',
        'stok_sebelum',
        'stok_sesudah',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'integer',
        'stok_sebelum' => 'integer',
        'stok_sesudah' => 'integer',
    ];

    // Flag untuk mengontrol apakah model event harus dijalankan
    public $skipStockUpdate = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produkLokasi()
    {
        return $this->belongsTo(ProdukLokasi::class);
    }

    /**
     * Check if this mutation is tracking only
     * (when stok_sebelum equals stok_sesudah)
     */
    public function isTrackingOnly()
    {
        return $this->stok_sebelum === $this->stok_sesudah;
    }

    /**
     * Boot method dengan kondisi untuk tracking only
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($mutasi) {
            // Skip stock update jika flag diset atau jika tracking only
            if ($mutasi->skipStockUpdate || $mutasi->isTrackingOnly()) {
                return;
            }

            $produkLokasi = $mutasi->produkLokasi;
            
            if ($mutasi->jenis_mutasi === 'masuk') {
                $produkLokasi->stok += $mutasi->jumlah;
            } else {
                $produkLokasi->stok -= $mutasi->jumlah;
            }
            
            $produkLokasi->save();
        });

        static::updated(function ($mutasi) {
            // Skip stock update jika flag diset
            if ($mutasi->skipStockUpdate) {
                return;
            }

            $produkLokasi = $mutasi->produkLokasi;
            $original = $mutasi->getOriginal();
            
            // Check if original was tracking only
            $originalWasTrackingOnly = ($original['stok_sebelum'] ?? null) === ($original['stok_sesudah'] ?? null);
            
            // Rollback perubahan lama hanya jika original bukan tracking only
            if (!$originalWasTrackingOnly) {
                if ($original['jenis_mutasi'] === 'masuk') {
                    $produkLokasi->stok -= $original['jumlah'];
                } else {
                    $produkLokasi->stok += $original['jumlah'];
                }
            }
            
            // Apply perubahan baru hanya jika current bukan tracking only
            if (!$mutasi->isTrackingOnly()) {
                if ($mutasi->jenis_mutasi === 'masuk') {
                    $produkLokasi->stok += $mutasi->jumlah;
                } else {
                    $produkLokasi->stok -= $mutasi->jumlah;
                }
            }
            
            $produkLokasi->save();
        });

        static::deleted(function ($mutasi) {
            // Skip stock update jika flag diset atau jika tracking only
            if ($mutasi->skipStockUpdate || $mutasi->isTrackingOnly()) {
                return;
            }

            $produkLokasi = $mutasi->produkLokasi;
            
            // Rollback stok
            if ($mutasi->jenis_mutasi === 'masuk') {
                $produkLokasi->stok -= $mutasi->jumlah;
            } else {
                $produkLokasi->stok += $mutasi->jumlah;
            }
            
            $produkLokasi->save();
        });
    }
}