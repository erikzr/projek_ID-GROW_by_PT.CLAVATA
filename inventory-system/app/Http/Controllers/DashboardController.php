<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Lokasi;
use App\Models\User;
use App\Models\Mutasi;
use App\Models\ProdukLokasi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display dashboard view
     */
    public function index()
    {
        // Data untuk halaman dashboard
        $stats = $this->getStatsData();
        $recentMutasi = $this->getRecentMutasi(5);
        $lowStock = $this->getLowStock();
        $chartData = $this->getMonthlyChartData();

        return view('dashboard', compact('stats', 'recentMutasi', 'lowStock', 'chartData'));
    }

    /**
     * Get dashboard overview data for AJAX
     */
    public function getOverview()
    {
        try {
            $stats = $this->getStatsData();
            $recentMutasi = $this->getRecentMutasi(10);
            $lowStock = $this->getLowStock();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_mutasi' => $recentMutasi,
                    'low_stock' => $lowStock,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data overview',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData(Request $request)
    {
        $request->validate([
            'period' => 'nullable|in:day,week,month'
        ]);

        try {
            $period = $request->get('period', 'week');
            
            switch ($period) {
                case 'day':
                    $startDate = Carbon::now()->subDays(7);
                    $groupBy = 'DATE(created_at)';
                    $format = '%Y-%m-%d';
                    break;
                case 'month':
                    $startDate = Carbon::now()->subMonths(12);
                    $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
                    $format = '%Y-%m';
                    break;
                default: // week
                    $startDate = Carbon::now()->subWeeks(8);
                    $groupBy = 'YEARWEEK(created_at)';
                    $format = 'Week %u, %Y';
                    break;
            }

            $mutasiMasuk = Mutasi::selectRaw("$groupBy as period, SUM(jumlah) as total")
                                ->where('jenis_mutasi', 'masuk')
                                ->where('created_at', '>=', $startDate)
                                ->groupByRaw($groupBy)
                                ->orderByRaw($groupBy)
                                ->get();

            $mutasiKeluar = Mutasi::selectRaw("$groupBy as period, SUM(jumlah) as total")
                                 ->where('jenis_mutasi', 'keluar')
                                 ->where('created_at', '>=', $startDate)
                                 ->groupByRaw($groupBy)
                                 ->orderByRaw($groupBy)
                                 ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'mutasi_masuk' => $mutasiMasuk,
                    'mutasi_keluar' => $mutasiKeluar,
                    'labels' => $this->generateLabels($period, $startDate),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data chart',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get stock summary by location
     */
    public function getStockSummary()
    {
        try {
            $stockByLocation = Lokasi::with(['produkLokasi.produk'])
                                    ->get()
                                    ->map(function ($lokasi) {
                                        return [
                                            'id' => $lokasi->id,
                                            'lokasi' => $lokasi->nama_lokasi,
                                            'kode_lokasi' => $lokasi->kode_lokasi,
                                            'total_produk' => $lokasi->produkLokasi->count(),
                                            'total_stok' => $lokasi->produkLokasi->sum('stok'),
                                            'nilai_stok' => $lokasi->produkLokasi->sum(function($pl) {
                                                return $pl->stok * $pl->produk->harga;
                                            }),
                                            'produk' => $lokasi->produkLokasi->map(function ($pl) {
                                                return [
                                                    'id' => $pl->produk->id,
                                                    'nama_produk' => $pl->produk->nama_produk,
                                                    'kode_produk' => $pl->produk->kode_produk,
                                                    'stok' => $pl->stok,
                                                    'satuan' => $pl->produk->satuan,
                                                    'harga' => $pl->produk->harga,
                                                    'nilai_stok' => $pl->stok * $pl->produk->harga,
                                                ];
                                            }),
                                        ];
                                    });

            return response()->json([
                'success' => true,
                'data' => $stockByLocation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data stok',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Generate reports
     */
    public function getReports(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'nullable|in:all,masuk,keluar',
            'produk_id' => 'nullable|exists:produk,id',
            'lokasi_id' => 'nullable|exists:lokasi,id',
        ]);

        try {
            $startDate = $request->get('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
            $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
            $type = $request->get('type', 'all');
            $produkId = $request->get('produk_id');
            $lokasiId = $request->get('lokasi_id');
            
            $query = Mutasi::with(['user', 'produkLokasi.produk', 'produkLokasi.lokasi'])
                          ->whereBetween('tanggal', [$startDate, $endDate]);
            
            if ($type !== 'all') {
                $query->where('jenis_mutasi', $type);
            }

            if ($produkId) {
                $query->whereHas('produkLokasi.produk', function($q) use ($produkId) {
                    $q->where('id', $produkId);
                });
            }

            if ($lokasiId) {
                $query->whereHas('produkLokasi.lokasi', function($q) use ($lokasiId) {
                    $q->where('id', $lokasiId);
                });
            }
            
            $mutasi = $query->orderBy('tanggal', 'desc')->get();
            
            // Summary
            $summary = [
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
                'total_mutasi' => $mutasi->count(),
                'total_masuk' => $mutasi->where('jenis_mutasi', 'masuk')->count(),
                'total_keluar' => $mutasi->where('jenis_mutasi', 'keluar')->count(),
                'jumlah_masuk' => $mutasi->where('jenis_mutasi', 'masuk')->sum('jumlah'),
                'jumlah_keluar' => $mutasi->where('jenis_mutasi', 'keluar')->sum('jumlah'),
                'nilai_masuk' => $mutasi->where('jenis_mutasi', 'masuk')->sum(function($m) {
                    return $m->jumlah * $m->produkLokasi->produk->harga;
                }),
                'nilai_keluar' => $mutasi->where('jenis_mutasi', 'keluar')->sum(function($m) {
                    return $m->jumlah * $m->produkLokasi->produk->harga;
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'mutasi' => $mutasi,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate laporan',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Private helper methods
     */
    private function getStatsData()
    {
        return [
            'total_produk' => Produk::count(),
            'total_lokasi' => Lokasi::count(),
            'total_user' => User::count(),
            'total_stok' => ProdukLokasi::sum('stok'),
            'nilai_stok' => ProdukLokasi::with('produk')->get()->sum(function($pl) {
                return $pl->stok * $pl->produk->harga;
            }),
        ];
    }

    private function getRecentMutasi($limit = 10)
    {
        return Mutasi::with(['user', 'produkLokasi.produk', 'produkLokasi.lokasi'])
                     ->orderBy('created_at', 'desc')
                     ->limit($limit)
                     ->get();
    }

    private function getLowStock($threshold = 10)
    {
        return ProdukLokasi::with(['produk', 'lokasi'])
                          ->where('stok', '<', $threshold)
                          ->orderBy('stok', 'asc')
                          ->get();
    }

    private function getMonthlyChartData()
    {
        $startDate = Carbon::now()->subMonths(6);
        
        $mutasiMasuk = Mutasi::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(jumlah) as total')
                            ->where('jenis_mutasi', 'masuk')
                            ->where('created_at', '>=', $startDate)
                            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
                            ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m")')
                            ->pluck('total', 'month');

        $mutasiKeluar = Mutasi::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(jumlah) as total')
                             ->where('jenis_mutasi', 'keluar')
                             ->where('created_at', '>=', $startDate)
                             ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
                             ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m")')
                             ->pluck('total', 'month');

        return [
            'labels' => $mutasiMasuk->keys()->merge($mutasiKeluar->keys())->unique()->sort()->values(),
            'masuk' => $mutasiMasuk->values(),
            'keluar' => $mutasiKeluar->values(),
        ];
    }

    private function generateLabels($period, $startDate)
    {
        $labels = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::now();

        switch ($period) {
            case 'day':
                while ($current->lte($end)) {
                    $labels[] = $current->format('d M');
                    $current->addDay();
                }
                break;
            case 'week':
                while ($current->lte($end)) {
                    $labels[] = 'Week ' . $current->week;
                    $current->addWeek();
                }
                break;
            case 'month':
                while ($current->lte($end)) {
                    $labels[] = $current->format('M Y');
                    $current->addMonth();
                }
                break;
        }

        return $labels;
    }
}