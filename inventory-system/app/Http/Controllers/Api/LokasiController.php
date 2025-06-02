<?php
// app/Http/Controllers/Api/LokasiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index()
    {
        $lokasi = Lokasi::with('produk')->get();
        
        return response()->json([
            'success' => true,
            'data' => $lokasi,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_lokasi' => 'required|string|unique:lokasi',
            'nama_lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $lokasi = Lokasi::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lokasi created successfully',
            'data' => $lokasi,
        ], 201);
    }

    public function show($id)
    {
        $lokasi = Lokasi::with('produk')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $lokasi,
        ]);
    }

    public function update(Request $request, $id)
    {
        $lokasi = Lokasi::findOrFail($id);

        $request->validate([
            'kode_lokasi' => 'sometimes|string|unique:lokasi,kode_lokasi,' . $id,
            'nama_lokasi' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $lokasi->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lokasi updated successfully',
            'data' => $lokasi->fresh(),
        ]);
    }

    

    public function destroy($id)
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lokasi deleted successfully',
        ]);
    }
}