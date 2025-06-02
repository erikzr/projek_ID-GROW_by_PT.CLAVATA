<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>

    <!-- Main Dashboard -->
    <div id="dashboard" class="dashboard-hidden">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-boxes"></i> Inventory</h3>
            </div>
            <ul class="sidebar-menu">
                <li class="active">
                    <a href="#" data-page="dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="#" data-page="produk">
                        <i class="fas fa-box"></i> Produk
                    </a>
                </li>
                <li>
                    <a href="#" data-page="lokasi">
                        <i class="fas fa-map-marker-alt"></i> Lokasi
                    </a>
                </li>
                <li>
                    <a href="#" data-page="mutasi">
                        <i class="fas fa-exchange-alt"></i> Mutasi
                    </a>
                </li>
                <li>
                    <a href="#" data-page="users">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li>
                    <a href="#" data-page="reports">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <button id="logoutBtn" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </nav>

        <main class="main-content">
            <header class="main-header">
                <h1 id="pageTitle">Dashboard</h1>
                <div class="user-info">
                    <span id="userName">Welcome, User!</span>
                </div>
            </header>

            <div class="content-area">
                <!-- Dashboard Page -->
                <div id="dashboardPage" class="page active">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="totalProduk">0</h3>
                                <p>Total Produk</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="totalLokasi">0</h3>
                                <p>Total Lokasi</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="totalUser">0</h3>
                                <p>Total User</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-cubes"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="totalStok">0</h3>
                                <p>Total Stok</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-grid">
                        <div class="chart-container">
                            <h3>Grafik Mutasi Bulanan</h3>
                            <canvas id="mutasiChart"></canvas>
                        </div>
                        <div class="recent-activities">
                            <h3>Mutasi Terbaru</h3>
                            <div id="recentMutasi" class="activity-list">
                                <!-- Recent mutations will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <div class="low-stock-alert">
                        <h3><i class="fas fa-exclamation-triangle"></i> Stok Menipis</h3>
                        <div id="lowStockList" class="alert-list">
                            <!-- Low stock items will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Produk Page -->
                <div id="produkPage" class="page">
                    <div class="page-header">
                        <h2>Manajemen Produk</h2>
                        <button class="btn btn-primary" onclick="showAddProdukModal()">
                            <i class="fas fa-plus"></i> Tambah Produk
                        </button>
                    </div>
                    <div class="table-container">
                        <table id="produkTable" class="data-table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Satuan</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Products will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Lokasi Page -->
                <div id="lokasiPage" class="page">
                    <div class="page-header">
                        <h2>Manajemen Lokasi</h2>
                        <button class="btn btn-primary" onclick="showAddLokasiModal()">
                            <i class="fas fa-plus"></i> Tambah Lokasi
                        </button>
                    </div>
                    <div class="table-container">
                        <table id="lokasiTable" class="data-table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Deskripsi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Locations will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mutasi Page -->
                <div id="mutasiPage" class="page">
                    <div class="page-header">
                        <h2>Manajemen Mutasi</h2>
                        <button class="btn btn-primary" onclick="showAddMutasiModal()">
                            <i class="fas fa-plus"></i> Tambah Mutasi
                        </button>
                    </div>
                    <div class="table-container">
                        <table id="mutasiTable" class="data-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jenis</th>
                                    <th>Produk</th>
                                    <th>Lokasi</th>
                                    <th>Jumlah</th>
                                    <th>User</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Mutations will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Users Page -->
                <div id="usersPage" class="page">
                    <div class="page-header">
                        <h2>Manajemen User</h2>
                        <button class="btn btn-primary" onclick="showAddUserModal()">
                            <i class="fas fa-plus"></i> Tambah User
                        </button>
                    </div>
                    <div class="table-container">
                        <table id="usersTable" class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Bergabung</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Users will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reports Page -->
                <div id="reportsPage" class="page">
                    <div class="page-header">
                        <h2>Laporan</h2>
                        <div class="report-filters">
                            <input type="date" id="startDate" class="form-control">
                            <input type="date" id="endDate" class="form-control">
                            <select id="reportType" class="form-control">
                                <option value="all">Semua</option>
                                <option value="masuk">Masuk</option>
                                <option value="keluar">Keluar</option>
                            </select>
                            <button class="btn btn-primary" onclick="generateReport()">
                                <i class="fas fa-search"></i> Generate
                            </button>
                        </div>
                    </div>
                    <div id="reportResults" class="report-container">
                        <!-- Report results will be loaded here -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals akan ditambahkan di sini -->
    <div id="modalContainer"></div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>