<?php
// app/Http/Controllers/Api/UserController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        
        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255', // Fixed: 'nama' -> 'name'
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name, // Fixed: 'nama' -> 'name'
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255', // Fixed: 'nama' -> 'name'
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
        ]);

        $updateData = $request->only(['name', 'email']); // Fixed: 'nama' -> 'name'
        
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    public function historyMutasi($id)
    {
        $user = User::with(['mutasi.produkLokasi.produk', 'mutasi.produkLokasi.lokasi'])
                   ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'email']), // Fixed: 'nama' -> 'name'
                'mutasi' => $user->mutasi->map(function ($mutasi) {
                    return [
                        'id' => $mutasi->id,
                        'tanggal' => $mutasi->tanggal,
                        'jenis_mutasi' => $mutasi->jenis_mutasi,
                        'jumlah' => $mutasi->jumlah,
                        'keterangan' => $mutasi->keterangan,
                        'produk' => $mutasi->produkLokasi->produk->nama_produk,
                        'kode_produk' => $mutasi->produkLokasi->produk->kode_produk,
                        'lokasi' => $mutasi->produkLokasi->lokasi->nama_lokasi,
                        'kode_lokasi' => $mutasi->produkLokasi->lokasi->kode_lokasi,
                    ];
                }),
            ],
        ]);
    }
}