<?php
// app/Http/Controllers/Api/MutasiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mutasi;
use App\Models\ProdukLokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class MutasiController extends Controller
{
    public function index()
    {
        $mutasi = Mutasi::with(['user', 'produkLokasi.produk', 'produkLokasi.lokasi'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $mutasi,
        ]);
    }

    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'tanggal' => 'required|date',
            'jenis_mutasi' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
            'produk_lokasi_id' => 'required|exists:produk_lokasi,id',
            'is_tracking_only' => 'nullable|boolean',
        ]);

        // Cek apakah ini tracking only dari berbagai sumber
        $isTrackingOnly = $request->query('tracking_only') === 'true' || 
                          $request->query('no_stock_update') === '1' ||
                          $request->input('is_tracking_only') === true;
        
        // Get produk lokasi
        $produkLokasi = ProdukLokasi::findOrFail($request->produk_lokasi_id);
        
        // Check stock availability for 'keluar' mutation (only if not tracking only)
        if (!$isTrackingOnly && $request->jenis_mutasi === 'keluar') {
            if ($produkLokasi->stok < $request->jumlah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi. Stok tersedia: ' . $produkLokasi->stok,
                ], 422);
            }
        }

        // Prepare mutasi data
        $mutasiData = [
            'tanggal' => $request->tanggal,
            'jenis_mutasi' => $request->jenis_mutasi,
            'jumlah' => $request->jumlah,
            'keterangan' => $request->keterangan,
            'user_id' => $request->user()->id,
            'produk_lokasi_id' => $request->produk_lokasi_id,
            'stok_sebelum' => $produkLokasi->stok,
        ];

        // Set stok_sesudah berdasarkan tracking only atau tidak
        if ($isTrackingOnly) {
            // Untuk tracking only, stok tidak berubah
            $mutasiData['stok_sesudah'] = $produkLokasi->stok;
        } else {
            // Untuk mutasi normal, hitung stok sesudah
            if ($request->jenis_mutasi === 'masuk') {
                $mutasiData['stok_sesudah'] = $produkLokasi->stok + $request->jumlah;
            } else {
                $mutasiData['stok_sesudah'] = $produkLokasi->stok - $request->jumlah;
            }
        }

        // Create mutasi - boot method akan otomatis handle stock update
        $mutasi = Mutasi::create($mutasiData);

        // Load relationships for response
        $mutasi->load(['user', 'produkLokasi.produk', 'produkLokasi.lokasi']);

        return response()->json([
            'success' => true,
            'message' => 'Mutasi berhasil disimpan' . ($isTrackingOnly ? ' (tracking only)' : ''),
            'data' => $mutasi,
        ], 201);
    }

    public function show($id)
    {
        $mutasi = Mutasi::with(['user', 'produkLokasi.produk', 'produkLokasi.lokasi'])
                       ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $mutasi,
        ]);
    }

    public function update(Request $request, $id)
    {
        $mutasi = Mutasi::findOrFail($id);

        $request->validate([
            'tanggal' => 'sometimes|date',
            'jenis_mutasi' => 'sometimes|in:masuk,keluar',
            'jumlah' => 'sometimes|integer|min:1',
            'keterangan' => 'nullable|string',
            'produk_lokasi_id' => 'sometimes|exists:produk_lokasi,id',
            'is_tracking_only' => 'nullable|boolean',
        ]);

        // Cek apakah ini tracking only update
        $isTrackingOnly = $request->query('tracking_only') === 'true' || 
                          $request->query('no_stock_update') === '1' ||
                          $request->input('is_tracking_only') === true;

        // Get current and new produk lokasi
        $currentProdukLokasi = $mutasi->produkLokasi;
        $newProdukLokasiId = $request->produk_lokasi_id ?? $mutasi->produk_lokasi_id;
        $newProdukLokasi = ProdukLokasi::findOrFail($newProdukLokasiId);

        // Validate stock if not tracking only
        if (!$isTrackingOnly && ($request->has('jenis_mutasi') || $request->has('jumlah') || $request->has('produk_lokasi_id'))) {
            $newJenis = $request->jenis_mutasi ?? $mutasi->jenis_mutasi;
            $newJumlah = $request->jumlah ?? $mutasi->jumlah;
            
            if ($newJenis === 'keluar') {
                // Calculate available stock after considering the rollback of current mutation
                $availableStock = $newProdukLokasi->stok;
                
                // If current mutation was not tracking only, add back its effect
                if (!$mutasi->isTrackingOnly()) {
                    if ($mutasi->jenis_mutasi === 'masuk') {
                        $availableStock -= $mutasi->jumlah;
                    } else {
                        $availableStock += $mutasi->jumlah;
                    }
                }
                
                if ($availableStock < $newJumlah) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok tidak mencukupi untuk perubahan ini. Stok yang akan tersedia: ' . $availableStock,
                    ], 422);
                }
            }
        }

        // Prepare update data
        $updateData = $request->except(['is_tracking_only']);
        
        // Update stok_sebelum and stok_sesudah if needed
        if ($request->has('produk_lokasi_id') || $request->has('jenis_mutasi') || $request->has('jumlah')) {
            $updateData['stok_sebelum'] = $newProdukLokasi->stok;
            
            if ($isTrackingOnly) {
                $updateData['stok_sesudah'] = $newProdukLokasi->stok;
            } else {
                $newJenis = $request->jenis_mutasi ?? $mutasi->jenis_mutasi;
                $newJumlah = $request->jumlah ?? $mutasi->jumlah;
                
                if ($newJenis === 'masuk') {
                    $updateData['stok_sesudah'] = $newProdukLokasi->stok + $newJumlah;
                } else {
                    $updateData['stok_sesudah'] = $newProdukLokasi->stok - $newJumlah;
                }
            }
        }

        // Update mutasi - boot method akan otomatis handle stock update
        $mutasi->update($updateData);
        $mutasi->load(['user', 'produkLokasi.produk', 'produkLokasi.lokasi']);

        return response()->json([
            'success' => true,
            'message' => 'Mutasi updated successfully' . ($isTrackingOnly ? ' (tracking only)' : ''),
            'data' => $mutasi,
        ]);
    }

    // Tambahkan method ini ke MutasiController.php

public function pindahLokasi(Request $request)
{
    // Validate input
    $request->validate([
        'produk_id' => 'required|exists:produk,id',
        'lokasi_asal_id' => 'required|exists:lokasi,id',
        'lokasi_tujuan_id' => 'required|exists:lokasi,id|different:lokasi_asal_id',
        'jumlah' => 'required|integer|min:1',
        'tanggal' => 'required|date',
        'keterangan' => 'nullable|string',
    ]);

    // Get lokasi names for keterangan
    $lokasiAsal = \App\Models\Lokasi::findOrFail($request->lokasi_asal_id);
    $lokasiTujuan = \App\Models\Lokasi::findOrFail($request->lokasi_tujuan_id);
    $produk = \App\Models\Produk::findOrFail($request->produk_id);

    // Find produk_lokasi asal
    $produkLokasiAsal = ProdukLokasi::where('produk_id', $request->produk_id)
                                   ->where('lokasi_id', $request->lokasi_asal_id)
                                   ->first();

    if (!$produkLokasiAsal) {
        return response()->json([
            'success' => false,
            'message' => 'Produk tidak ditemukan di lokasi asal',
        ], 422);
    }

    // Check stock availability
    if ($produkLokasiAsal->stok < $request->jumlah) {
        return response()->json([
            'success' => false,
            'message' => 'Stok tidak mencukupi di lokasi asal. Stok tersedia: ' . $produkLokasiAsal->stok,
        ], 422);
    }

    // Find or create produk_lokasi tujuan
    $produkLokasiTujuan = ProdukLokasi::firstOrCreate([
        'produk_id' => $request->produk_id,
        'lokasi_id' => $request->lokasi_tujuan_id,
    ], [
        'stok' => 0
    ]);

    // Start database transaction
    DB::beginTransaction();

    try {
        // Create mutasi keluar (dari lokasi asal)
        $mutasiKeluar = Mutasi::create([
            'tanggal' => $request->tanggal,
            'jenis_mutasi' => 'keluar',
            'jumlah' => $request->jumlah,
            'keterangan' => ($request->keterangan ?? '') . " - Pindah ke {$lokasiTujuan->nama_lokasi}",
            'user_id' => $request->user()->id,
            'produk_lokasi_id' => $produkLokasiAsal->id,
            'stok_sebelum' => $produkLokasiAsal->stok,
            'stok_sesudah' => $produkLokasiAsal->stok - $request->jumlah,
        ]);

        // Create mutasi masuk (ke lokasi tujuan)
        $mutasiMasuk = Mutasi::create([
            'tanggal' => $request->tanggal,
            'jenis_mutasi' => 'masuk',
            'jumlah' => $request->jumlah,
            'keterangan' => ($request->keterangan ?? '') . " - Pindah dari {$lokasiAsal->nama_lokasi}",
            'user_id' => $request->user()->id,
            'produk_lokasi_id' => $produkLokasiTujuan->id,
            'stok_sebelum' => $produkLokasiTujuan->stok,
            'stok_sesudah' => $produkLokasiTujuan->stok + $request->jumlah,
        ]);

        // Commit transaction
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Berhasil memindahkan {$request->jumlah} {$produk->nama_produk} dari {$lokasiAsal->nama_lokasi} ke {$lokasiTujuan->nama_lokasi}",
        ], 201);

    } catch (\Exception $e) {
        // Rollback transaction
        DB::rollback();
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat memindahkan produk: ' . $e->getMessage(),
        ], 500);
    }
}

    public function destroy($id)
    {
        $mutasi = Mutasi::findOrFail($id);
        
        // Check if deleting this mutation would result in negative stock
        // Boot method akan handle ini, tapi kita validasi dulu
        if (!$mutasi->isTrackingOnly()) {
            $produkLokasi = $mutasi->produkLokasi;
            $futureStock = $produkLokasi->stok;
            
            if ($mutasi->jenis_mutasi === 'masuk') {
                $futureStock -= $mutasi->jumlah;
            } else {
                $futureStock += $mutasi->jumlah;
            }
            
            if ($futureStock < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus mutasi ini karena akan mengakibatkan stok negatif',
                ], 422);
            }
        }
        
        // Delete mutasi - boot method akan otomatis handle stock rollback
        $mutasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mutasi deleted successfully',
        ]);
    }
}