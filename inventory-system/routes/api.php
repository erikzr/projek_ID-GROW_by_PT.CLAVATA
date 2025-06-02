<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LokasiController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\ProdukLokasiController;
use App\Http\Controllers\Api\MutasiController;
use App\Http\Controllers\DashboardController;

Route::middleware('api')->group(function () {

    // Test route tanpa autentikasi
    Route::get('test', function () {
        return response()->json([
            'message' => 'Laravel Inventory API is working!',
            'timestamp' => now(),
            'version' => '1.0.0'
        ]);
    });

    // Public routes (login dan register)
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
    });

    // Protected routes (harus autentikasi token)
    Route::middleware('auth:sanctum')->group(function () {

        // Auth routes untuk user info dan logout
        Route::prefix('auth')->group(function () {
            Route::get('user', [AuthController::class, 'user']);
            Route::post('logout', [AuthController::class, 'logout']);  // logout endpoint
        });

        // Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('overview', [DashboardController::class, 'overview']);
            Route::get('chart-data', [DashboardController::class, 'chartData']);
            Route::get('stock-summary', [DashboardController::class, 'stockSummary']);
            Route::get('reports', [DashboardController::class, 'reports']);
        });

        // CRUD Users, Lokasi, Produk, ProdukLokasi, Mutasi
        Route::apiResource('users', UserController::class);
        Route::get('users/{id}/mutasi', [UserController::class, 'historyMutasi']);
        Route::apiResource('lokasi', LokasiController::class);
        Route::apiResource('produk', ProdukController::class);
        Route::get('produk/{id}/mutasi', [ProdukController::class, 'historyMutasi']);
        Route::apiResource('produk-lokasi', ProdukLokasiController::class);
        Route::get('produk/{id}/lokasi', [ProdukLokasiController::class, 'getByProduk']);
        Route::get('lokasi/{id}/produk', [ProdukLokasiController::class, 'getByLokasi']);
        Route::apiResource('mutasi', MutasiController::class);

        // Search utility
        Route::get('search/produk', function (Request $request) {
            $query = $request->get('q');
            $produk = \App\Models\Produk::where('nama_produk', 'like', "%{$query}%")
                ->orWhere('kode_produk', 'like', "%{$query}%")
                ->limit(10)
                ->get();
            return response()->json(['success' => true, 'data' => $produk]);
        });

        Route::get('search/lokasi', function (Request $request) {
            $query = $request->get('q');
            $lokasi = \App\Models\Lokasi::where('nama_lokasi', 'like', "%{$query}%")
                ->orWhere('kode_lokasi', 'like', "%{$query}%")
                ->limit(10)
                ->get();
            return response()->json(['success' => true, 'data' => $lokasi]);
        });

    });

});

// Fallback route kalau endpoint tidak ketemu
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'POST /api/auth/login',
            'POST /api/auth/register',
            'GET /api/dashboard/overview',
            'GET /api/produk',
            'GET /api/lokasi',
            'GET /api/mutasi',
        ]
    ], 404);
});
