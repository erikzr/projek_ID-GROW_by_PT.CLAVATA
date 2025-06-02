<?php
// app/Http/Controllers/Api/MutasiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mutasi;
use App\Models\ProdukLokasi;
use Illuminate\Http\Request;

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