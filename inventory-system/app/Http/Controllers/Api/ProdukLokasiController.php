<?php
// app/Http/Controllers/Api/ProdukLokasiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProdukLokasi;
use App\Models\Produk;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class ProdukLokasiController extends Controller
{
    public function index()
    {
        $produkLokasi = ProdukLokasi::with(['produk', 'lokasi'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $produkLokasi,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'lokasi_id' => 'required|exists:lokasi,id',
            'stok' => 'required|integer|min:0',
        ]);

        // Check if combination already exists
        $exists = ProdukLokasi::where('produk_id', $request->produk_id)
                             ->where('lokasi_id', $request->lokasi_id)
                             ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Produk sudah ada di lokasi ini',
            ], 422);
        }

        $produkLokasi = ProdukLokasi::create($request->all());
        $produkLokasi->load(['produk', 'lokasi']);

        return response()->json([
            'success' => true,
            'message' => 'Produk lokasi created successfully',
            'data' => $produkLokasi,
        ], 201);
    }

    public function show($id)
    {
        $produkLokasi = ProdukLokasi::with(['produk', 'lokasi'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $produkLokasi,
        ]);
    }

    public function update(Request $request, $id)
    {
        $produkLokasi = ProdukLokasi::findOrFail($id);

        $request->validate([
            'produk_id' => 'sometimes|exists:produk,id',
            'lokasi_id' => 'sometimes|exists:lokasi,id',
            'stok' => 'sometimes|integer|min:0',
        ]);

        // Check if new combination already exists (if changing produk_id or lokasi_id)
        if ($request->has('produk_id') || $request->has('lokasi_id')) {
            $produkId = $request->produk_id ?? $produkLokasi->produk_id;
            $lokasiId = $request->lokasi_id ?? $produkLokasi->lokasi_id;
            
            $exists = ProdukLokasi::where('produk_id', $produkId)
                                 ->where('lokasi_id', $lokasiId)
                                 ->where('id', '!=', $id)
                                 ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kombinasi produk dan lokasi sudah ada',
                ], 422);
            }
        }

        $produkLokasi->update($request->all());
        $produkLokasi->load(['produk', 'lokasi']);

        return response()->json([
            'success' => true,
            'message' => 'Produk lokasi updated successfully',
            'data' => $produkLokasi,
        ]);
    }

    public function destroy($id)
    {
        $produkLokasi = ProdukLokasi::findOrFail($id);
        $produkLokasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk lokasi deleted successfully',
        ]);
    }

    public function getByProduk($produkId)
    {
        $produk = Produk::findOrFail($produkId);
        $produkLokasi = ProdukLokasi::with('lokasi')
                                   ->where('produk_id', $produkId)
                                   ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'produk' => $produk,
                'lokasi' => $produkLokasi,
            ],
        ]);
    }

    

    public function getByLokasi($lokasiId)
    {
        $lokasi = Lokasi::findOrFail($lokasiId);
        $produkLokasi = ProdukLokasi::with('produk')
                                   ->where('lokasi_id', $lokasiId)
                                   ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'lokasi' => $lokasi,
                'produk' => $produkLokasi,
            ],
        ]);
    }
}