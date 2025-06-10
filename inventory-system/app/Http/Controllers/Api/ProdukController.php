<?php
// app/Http/Controllers/Api/ProdukController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::with('lokasi')->get();
        
        return response()->json([
            'success' => true,
            'data' => $produk,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'kode_produk' => 'required|string|unique:produk',
            'kategori' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'deskripsi' => 'nullable|string',
            'harga' => 'nullable|numeric|min:0',
        ]);

        $produk = Produk::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk created successfully',
            'data' => $produk,
        ], 201);
    }

    public function show($id)
    {
        $produk = Produk::with('lokasi')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $produk,
        ]);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $request->validate([
            'nama_produk' => 'sometimes|string|max:255',
            'kode_produk' => 'sometimes|string|unique:produk,kode_produk,' . $id,
            'kategori' => 'sometimes|string|max:255',
            'satuan' => 'sometimes|string|max:50',
            'deskripsi' => 'nullable|string',
            'harga' => 'nullable|numeric|min:0',
        ]);

        $produk->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk updated successfully',
            'data' => $produk->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk deleted successfully',
        ]);
    }

    public function historyMutasi($id)
    {
        $produk = Produk::with(['mutasi.user', 'mutasi.produkLokasi.lokasi'])
                       ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'produk' => $produk->only(['id', 'nama_produk', 'kode_produk', 'kategori', 'satuan']),
                'mutasi' => $produk->mutasi->map(function ($mutasi) {
                    return [
                        'id' => $mutasi->id,
                        'tanggal' => $mutasi->tanggal,
                        'jenis_mutasi' => $mutasi->jenis_mutasi,
                        'jumlah' => $mutasi->jumlah,
                        'keterangan' => $mutasi->keterangan,
                        'user' => $mutasi->user->name,
                        'lokasi' => $mutasi->produkLokasi->lokasi->nama_lokasi,
                        'kode_lokasi' => $mutasi->produkLokasi->lokasi->kode_lokasi,
                        'stok_sekarang' => $mutasi->produkLokasi->stok,
                    ];
                }),
            ],
        ]);
    }

    // Method baru untuk generate kode produk
    public function generateKodeProduk(Request $request)
    {
        $request->validate([
            'kategori_code' => 'required|string',
            'kategori_name' => 'required|string'
        ]);

        $kategoriCode = $request->kategori_code;
        $kategoriName = $request->kategori_name;

        // Cari kode produk terakhir dengan kategori yang sama
        $lastProduk = Produk::where('kode_produk', 'LIKE', $kategoriCode . '%')
                           ->orderBy('kode_produk', 'desc')
                           ->first();

        if ($lastProduk) {
            // Extract nomor dari kode produk terakhir
            $lastNumber = intval(substr($lastProduk->kode_produk, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        // Format nomor dengan leading zeros (3 digit)
        $kodeProduk = $kategoriCode . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'data' => [
                'kode_produk' => $kodeProduk,
                'kategori' => $kategoriName
            ]
        ]);
    }
}