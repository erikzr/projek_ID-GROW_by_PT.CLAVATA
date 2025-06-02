<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard CRUD | Inventory App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-stat { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .card-stat-secondary { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
        .card-stat-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
        .card-stat-warning { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
        .table-responsive { max-height: 400px; overflow-y: auto; }
        .badge-masuk { background-color: #28a745; }
        .badge-keluar { background-color: #dc3545; }
        .loading { display: none; text-align: center; padding: 20px; }
        .btn-action { margin: 0 2px; }
        .alert-info-custom { background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); color: white; border: none; }
        .step-indicator { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 8px; 
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-warehouse me-2"></i>Inventory CRUD</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3" id="current-time"></span>
                <button class="btn btn-outline-light" onclick="logout()">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </button>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">
        <!-- User Welcome -->
        <div class="row mb-4">
            <div class="col-12" id="user-info"></div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card card-stat">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><h5 class="card-title mb-0">Total Produk</h5><h2 class="mb-0" id="total-produk">0</h2></div>
                            <i class="fas fa-box fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat-secondary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><h5 class="card-title mb-0">Total Lokasi</h5><h2 class="mb-0" id="total-lokasi">0</h2></div>
                            <i class="fas fa-map-marker-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><h5 class="card-title mb-0">Total Stok</h5><h2 class="mb-0" id="total-stok">0</h2></div>
                            <i class="fas fa-cubes fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stat-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><h5 class="card-title mb-0">Total Mutasi</h5><h2 class="mb-0" id="total-mutasi">0</h2></div>
                            <i class="fas fa-exchange-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Process Flow Info -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="step-indicator">
                    <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Alur Proses Inventory:</h6>
                    <div class="d-flex flex-wrap gap-3">
                        <span class="badge bg-primary px-3 py-2">1. Buat Master Produk</span>
                        <i class="fas fa-arrow-right align-self-center text-muted"></i>
                        <span class="badge bg-success px-3 py-2">2. Buat Master Lokasi</span>
                        <i class="fas fa-arrow-right align-self-center text-muted"></i>
                        <span class="badge bg-info px-3 py-2">3. Set Stok Awal</span>
                        <i class="fas fa-arrow-right align-self-center text-muted"></i>
                        <span class="badge bg-warning px-3 py-2">4. Catat Mutasi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs" id="crudTabs">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#produk-tab"><i class="fas fa-box me-1"></i>Master Produk</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#lokasi-tab"><i class="fas fa-map-marker-alt me-1"></i>Master Lokasi</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#stok-tab"><i class="fas fa-cubes me-1"></i>Stok Per Lokasi</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#mutasi-tab"><i class="fas fa-exchange-alt me-1"></i>Mutasi Stok</button></li>
                </ul>
                
                <div class="tab-content">
                    <!-- PRODUK TAB -->
                    <div class="tab-pane fade show active" id="produk-tab">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div>
                                    <h5 class="mb-0">Master Data Produk</h5>
                                    <small class="text-muted">Kelola data produk tanpa stok. Stok diatur di tab "Stok Per Lokasi"</small>
                                </div>
                                <button class="btn btn-success" onclick="showModal('produk', 'create')">
                                    <i class="fas fa-plus me-1"></i>Tambah Produk
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="loading" id="loading-produk"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Satuan</th><th>Harga</th><th>Aksi</th></tr>
                                        </thead>
                                        <tbody id="produk-table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- LOKASI TAB -->
                    <div class="tab-pane fade" id="lokasi-tab">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div>
                                    <h5 class="mb-0">Master Data Lokasi</h5>
                                    <small class="text-muted">Kelola lokasi penyimpanan barang</small>
                                </div>
                                <button class="btn btn-success" onclick="showModal('lokasi', 'create')">
                                    <i class="fas fa-plus me-1"></i>Tambah Lokasi
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="loading" id="loading-lokasi"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr><th>Kode</th><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr>
                                        </thead>
                                        <tbody id="lokasi-table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STOK TAB -->
                    <div class="tab-pane fade" id="stok-tab">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div>
                                    <h5 class="mb-0">Stok Per Lokasi</h5>
                                    <small class="text-muted">Set stok awal untuk kombinasi produk-lokasi. Stok ini akan berubah otomatis saat ada mutasi</small>
                                </div>
                                <button class="btn btn-success" onclick="showModal('stok', 'create')">
                                    <i class="fas fa-plus me-1"></i>Set Stok Awal
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Catatan:</strong> Stok di sini adalah stok aktual saat ini. Setiap perubahan stok akan otomatis mencatat mutasi masuk untuk tracking.
                                </div>
                                <div class="loading" id="loading-stok"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr><th>Produk</th><th>Lokasi</th><th>Stok Saat Ini</th><th>Status</th><th>Aksi</th></tr>
                                        </thead>
                                        <tbody id="stok-table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MUTASI TAB -->
                    <div class="tab-pane fade" id="mutasi-tab">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div>
                                    <h5 class="mb-0">Mutasi Stok</h5>
                                    <small class="text-muted">Catat pergerakan barang. <strong>Masuk:</strong> tracking saja, <strong>Keluar:</strong> mengurangi stok</small>
                                </div>
                                <button class="btn btn-success" onclick="showModal('mutasi', 'create')">
                                    <i class="fas fa-plus me-1"></i>Catat Mutasi
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Penting:</strong> Mutasi masuk hanya untuk tracking. Mutasi keluar akan mengurangi stok.
                                </div>
                                <div class="loading" id="loading-mutasi"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr><th>Tanggal</th><th>Produk</th><th>Lokasi</th><th>Jenis</th><th>Jumlah</th><th>Stok Sebelum</th><th>Stok Sesudah</th><th>Keterangan</th><th>User</th><th>Aksi</th></tr>
                                        </thead>
                                        <tbody id="mutasi-table"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FORMS -->
    <div class="modal fade" id="crudModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Dynamic form content -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveBtn" onclick="saveData()">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const token = sessionStorage.getItem('auth_token');
        if (!token) window.location.href = '/login';

        let currentData = { produk: [], lokasi: [], stok: [], mutasi: [] };
        let modalState = { type: '', mode: '', data: null };

        document.addEventListener('DOMContentLoaded', function() {
            loadUserInfo();
            loadAllData();
            updateTime();
            setInterval(updateTime, 1000);
        });

        function loadUserInfo() {
            const userData = JSON.parse(sessionStorage.getItem('user_data'));
            if (userData) {
                document.getElementById('user-info').innerHTML = 
                    `<div class="alert alert-info-custom"><i class="fas fa-user me-2"></i>Welcome, <strong>${userData.name}</strong> | Role: <strong>${userData.role || 'User'}</strong></div>`;
            }
        }

        function updateTime() {
            document.getElementById('current-time').textContent = new Date().toLocaleString('id-ID');
        }

        async function apiRequest(endpoint, method = 'GET', data = null) {
            const config = {
                method,
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            };
            if (data) config.body = JSON.stringify(data);

            try {
                const response = await fetch(`/api/${endpoint}`, config);
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || `HTTP ${response.status}`);
                }
                
                return result;
            } catch (error) {
                console.error(`API Error (${endpoint}):`, error);
                return { success: false, error: error.message };
            }
        }

        async function loadAllData() {
            await Promise.all([loadProduk(), loadLokasi(), loadStok(), loadMutasi()]);
            updateStatistics();
        }

        async function loadProduk() {
            showLoading('produk');
            const response = await apiRequest('produk');
            hideLoading('produk');
            if (response.success) {
                currentData.produk = response.data;
                renderProdukTable();
            } else {
                console.error('Error loading produk:', response.error);
            }
        }

        async function loadLokasi() {
            showLoading('lokasi');
            const response = await apiRequest('lokasi');
            hideLoading('lokasi');
            if (response.success) {
                currentData.lokasi = response.data;
                renderLokasiTable();
            } else {
                console.error('Error loading lokasi:', response.error);
            }
        }

        async function loadStok() {
            showLoading('stok');
            const response = await apiRequest('produk-lokasi');
            hideLoading('stok');
            if (response.success) {
                currentData.stok = response.data;
                renderStokTable();
            } else {
                console.error('Error loading stok:', response.error);
            }
        }

        async function loadMutasi() {
            showLoading('mutasi');
            const response = await apiRequest('mutasi');
            hideLoading('mutasi');
            if (response.success) {
                currentData.mutasi = response.data.slice(0, 50); // Load latest 50 records
                renderMutasiTable();
            } else {
                console.error('Error loading mutasi:', response.error);
            }
        }

        function renderProdukTable() {
            const tbody = document.getElementById('produk-table');
            if (currentData.produk.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Belum ada data produk</td></tr>';
                return;
            }
            
            tbody.innerHTML = currentData.produk.map(item => `
                <tr>
                    <td><code>${item.kode_produk}</code></td>
                    <td><strong>${item.nama_produk}</strong></td>
                    <td><span class="badge bg-secondary">${item.kategori || '-'}</span></td>
                    <td>${item.satuan}</td>
                    <td>Rp ${parseFloat(item.harga || 0).toLocaleString('id-ID')}</td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-action" onclick="showModal('produk', 'edit', ${item.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-action" onclick="deleteItem('produk', ${item.id})" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function renderLokasiTable() {
            const tbody = document.getElementById('lokasi-table');
            if (currentData.lokasi.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Belum ada data lokasi</td></tr>';
                return;
            }
            
            tbody.innerHTML = currentData.lokasi.map(item => `
                <tr>
                    <td><code>${item.kode_lokasi}</code></td>
                    <td><strong>${item.nama_lokasi}</strong></td>
                    <td>${item.deskripsi || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-action" onclick="showModal('lokasi', 'edit', ${item.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-action" onclick="deleteItem('lokasi', ${item.id})" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function renderStokTable() {
            const tbody = document.getElementById('stok-table');
            if (currentData.stok.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Belum ada data stok. Mulai dengan set stok awal.</td></tr>';
                return;
            }
            
            tbody.innerHTML = currentData.stok.map(item => {
                const stok = parseInt(item.stok || 0);
                const statusClass = stok <= 0 ? 'danger' : stok < 10 ? 'warning' : stok < 50 ? 'info' : 'success';
                const statusText = stok <= 0 ? 'Habis' : stok < 10 ? 'Kritis' : stok < 50 ? 'Rendah' : 'Aman';
                
                return `
                    <tr>
                        <td><strong>${item.produk?.nama_produk || 'N/A'}</strong><br><small class="text-muted">${item.produk?.kode_produk || ''}</small></td>
                        <td><strong>${item.lokasi?.nama_lokasi || 'N/A'}</strong><br><small class="text-muted">${item.lokasi?.kode_lokasi || ''}</small></td>
                        <td><span class="badge bg-dark fs-6">${stok}</span> ${item.produk?.satuan || ''}</td>
                        <td><span class="badge bg-${statusClass}">${statusText}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-action" onclick="showModal('stok', 'edit', ${item.id})" title="Edit Stok">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-action" onclick="deleteItem('produk-lokasi', ${item.id})" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderMutasiTable() {
            const tbody = document.getElementById('mutasi-table');
            if (currentData.mutasi.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Belum ada data mutasi</td></tr>';
                return;
            }
            
            tbody.innerHTML = currentData.mutasi.map(item => `
                <tr>
                    <td>${new Date(item.tanggal).toLocaleDateString('id-ID')}</td>
                    <td><strong>${item.produk_lokasi?.produk?.nama_produk || 'N/A'}</strong></td>
                    <td>${item.produk_lokasi?.lokasi?.nama_lokasi || 'N/A'}</td>
                    <td><span class="badge badge-${item.jenis_mutasi}">${item.jenis_mutasi}</span></td>
                    <td><strong>${item.jumlah}</strong></td>
                    <td>${item.stok_sebelum || '-'}</td>
                    <td>${item.stok_sesudah || '-'}</td>
                    <td><small>${item.keterangan || '-'}</small></td>
                    <td>${item.user?.name || 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-action" onclick="showModal('mutasi', 'edit', ${item.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-action" onclick="deleteItem('mutasi', ${item.id})" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function showModal(type, mode, id = null) {
            modalState = { type, mode, data: id ? getData(type, id) : null };
            
            const titles = {
                'produk': mode === 'create' ? 'Tambah Produk Baru' : 'Edit Data Produk',
                'lokasi': mode === 'create' ? 'Tambah Lokasi Baru' : 'Edit Data Lokasi', 
                'stok': mode === 'create' ? 'Set Stok Awal' : 'Edit Stok',
                'mutasi': mode === 'create' ? 'Catat Mutasi Baru' : 'Edit Mutasi'
            };
            
            document.getElementById('modalTitle').textContent = titles[type];
            document.getElementById('modalBody').innerHTML = getFormHTML(type, modalState.data);
            
            new bootstrap.Modal(document.getElementById('crudModal')).show();
        }

        function getData(type, id) {
            const dataKey = type === 'stok' ? 'stok' : type;
            return currentData[dataKey].find(item => item.id == id);
        }

        function getFormHTML(type, data) {
            switch(type) {
                case 'produk':
                    return `
                        <div class="mb-3">
                            <label class="form-label">Kode Produk *</label>
                            <input type="text" class="form-control" id="kode_produk" value="${data?.kode_produk || ''}" required>
                            <div class="form-text">Kode unik untuk produk (contoh: PRD001)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Produk *</label>
                            <input type="text" class="form-control" id="nama_produk" value="${data?.nama_produk || ''}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" class="form-control" id="kategori" value="${data?.kategori || ''}" placeholder="Elektronik, Makanan, dll">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Satuan *</label>
                            <input type="text" class="form-control" id="satuan" value="${data?.satuan || ''}" required placeholder="Pcs, Kg, Liter, dll">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga *</label>
                            <input type="number" class="form-control" id="harga" value="${data?.harga || ''}" required min="0" step="0.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" rows="3">${data?.deskripsi || ''}</textarea>
                        </div>
                    `;
                case 'lokasi':
                    return `
                        <div class="mb-3">
                            <label class="form-label">Kode Lokasi *</label>
                            <input type="text" class="form-control" id="kode_lokasi" value="${data?.kode_lokasi || ''}" required>
                            <div class="form-text">Kode unik untuk lokasi (contoh: GDG001, RAK-A1)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lokasi *</label>
                            <input type="text" class="form-control" id="nama_lokasi" value="${data?.nama_lokasi || ''}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" rows="3">${data?.deskripsi || ''}</textarea>
                        </div>
                    `;
                case 'stok':
                    return `
                        <div class="alert alert-success">
                            <i class="fas fa-magic me-2"></i>
                            <strong>Fitur Baru:</strong> ${modalState.mode === 'create' ? 'Set stok awal akan otomatis mencatat mutasi masuk' : 'Edit stok akan otomatis mencatat mutasi masuk untuk tracking'}
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Produk *</label>
                            <select class="form-control" id="produk_id" required ${modalState.mode === 'edit' ? 'disabled' : ''}>
                                <option value="">Pilih Produk</option>
                                ${currentData.produk.map(p => 
                                    `<option value="${p.id}" ${data?.produk_id == p.id ? 'selected' : ''}>${p.nama_produk} (${p.kode_produk})</option>`
                                ).join('')}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi *</label>
                            <select class="form-control" id="lokasi_id" required ${modalState.mode === 'edit' ? 'disabled' : ''}>
                                <option value="">Pilih Lokasi</option>
                                ${currentData.lokasi.map(l => 
                                    `<option value="${l.id}" ${data?.lokasi_id == l.id ? 'selected' : ''}>${l.nama_lokasi} (${l.kode_lokasi})</option>`
                                ).join('')}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok *</label>
                            <input type="number" class="form-control" id="stok" value="${data?.stok || ''}" required min="0" step="1">
                            <div class="form-text">Jumlah stok yang akan otomatis dicatat sebagai mutasi masuk</div>
                        </div>
                    `;
                case 'mutasi':
                    return `
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Mutasi Masuk:</strong> Hanya untuk tracking (jumlah otomatis sama dengan stok awal, tidak dapat diubah)<br>
                            <strong>Mutasi Keluar:</strong> Dapat diubah untuk mencatat barang keluar (akan mengurangi stok)
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Produk & Lokasi *</label>
                            <select class="form-control" id="produk_lokasi_id" required onchange="handleProdukLokasiChange()" ${modalState.mode === 'edit' ? 'disabled' : ''}>
                                <option value="">Pilih Kombinasi Produk-Lokasi</option>
                                ${currentData.stok.map(s => 
                                    `<option value="${s.id}" data-stok="${s.stok}" ${data?.produk_lokasi_id == s.id ? 'selected' : ''}>
                                        ${s.produk?.nama_produk} - ${s.lokasi?.nama_lokasi} (Stok: ${s.stok})
                                    </option>`
                                ).join('')}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Mutasi *</label>
                            <select class="form-control" id="jenis_mutasi" required onchange="handleJenisMutasiChange()" ${modalState.mode === 'edit' ? 'disabled' : ''}>
                                <option value="">Pilih Jenis</option>
                                <option value="masuk" ${data?.jenis_mutasi === 'masuk' ? 'selected' : ''}>Masuk (Tracking Only)</option>
                                <option value="keluar" ${data?.jenis_mutasi === 'keluar' ? 'selected' : ''}>Keluar (Mengurangi Stok)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah *</label>
                            <input type="number" class="form-control" id="jumlah" value="${data?.jumlah || ''}" required min="1" step="1">
                            <div class="form-text" id="jumlah-help">Masukkan jumlah mutasi</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal *</label>
                            <input type="date" class="form-control" id="tanggal" value="${data?.tanggal ? data.tanggal.split('T')[0] : new Date().toISOString().split('T')[0]}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" rows="3">${data?.keterangan || ''}</textarea>
                        </div>
                    `;
                default:
                    return '<p>Form tidak tersedia</p>';
            }
        }

        function handleProdukLokasiChange() {
            const select = document.getElementById('produk_lokasi_id');
            const jenisSelect = document.getElementById('jenis_mutasi');
            const jumlahInput = document.getElementById('jumlah');
            
            if (select.value && jenisSelect.value === 'masuk') {
                const selectedOption = select.options[select.selectedIndex];
                const stokAwal = selectedOption.getAttribute('data-stok');
                jumlahInput.value = stokAwal;
                jumlahInput.readOnly = true;
                document.getElementById('jumlah-help').textContent = 'Jumlah otomatis sama dengan stok awal (read-only untuk mutasi masuk)';
            }
        }

        function handleJenisMutasiChange() {
            const jenisSelect = document.getElementById('jenis_mutasi');
            const jumlahInput = document.getElementById('jumlah');
            const produkLokasiSelect = document.getElementById('produk_lokasi_id');
            
            if (jenisSelect.value === 'masuk') {
                if (produkLokasiSelect.value) {
                    const selectedOption = produkLokasiSelect.options[produkLokasiSelect.selectedIndex];
                    const stokAwal = selectedOption.getAttribute('data-stok');
                    jumlahInput.value = stokAwal;
                }
                jumlahInput.readOnly = true;
                document.getElementById('jumlah-help').textContent = 'Jumlah otomatis sama dengan stok awal (read-only untuk mutasi masuk)';
            } else if (jenisSelect.value === 'keluar') {
                jumlahInput.readOnly = false;
                jumlahInput.value = '';
                document.getElementById('jumlah-help').textContent = 'Masukkan jumlah barang yang keluar (akan mengurangi stok)';
            }
        }

        async function saveData() {
            const data = getFormData();
            if (!data) return;

            let endpoint, method;
            
            if (modalState.mode === 'create') {
                endpoint = getEndpoint(modalState.type);
                method = 'POST';
            } else {
                endpoint = `${getEndpoint(modalState.type)}/${modalState.data.id}`;
                method = 'PUT';
            }

            // Special handling for mutasi
            if (modalState.type === 'mutasi') {
                if (data.jenis_mutasi === 'masuk') {
                    endpoint = 'mutasi?tracking_only=true&no_stock_update=1';
                    data.stock_operation = 'none';
                } else if (data.jenis_mutasi === 'keluar') {
                    endpoint = 'mutasi?tracking_only=false&allow_stock_update=1';
                    data.stock_operation = 'subtract';
                }
                
                data.prevent_stock_addition = (data.jenis_mutasi === 'masuk');
                data.is_tracking_only = (data.jenis_mutasi === 'masuk');
            }

            console.log('Sending data:', data);
            console.log('Full endpoint:', endpoint);

            const response = await apiRequest(endpoint, method, data);
            
            if (response.success) {
                // AUTO MUTASI: Jika berhasil save stok, otomatis buat mutasi masuk
                if (modalState.type === 'stok' && response.success) {
                    await createAutoMutasi(data, modalState.mode);
                }
                
                bootstrap.Modal.getInstance(document.getElementById('crudModal')).hide();
                await loadAllData();
                showAlert('success', `Data ${modalState.type} berhasil ${modalState.mode === 'create' ? 'ditambahkan' : 'diupdate'}!`);
            } else {
                showAlert('danger', `Error: ${response.error}`);
            }
        }

        async function createAutoMutasi(stokData, mode) {
            try {
                const mutasiData = {
                    produk_lokasi_id: mode === 'create' ? 
                        await getLatestProdukLokasiId(stokData.produk_id, stokData.lokasi_id) : 
                        modalState.data.id,
                    jenis_mutasi: 'masuk',
                    jumlah: stokData.stok,
                    tanggal: new Date().toISOString().split('T')[0],
                    keterangan: mode === 'create' ? 
                        'Auto: Stok awal ditambahkan' : 
                        `Auto: Stok diubah menjadi ${stokData.stok}`,
                    stock_operation: 'none',
                    prevent_stock_addition: true,
                    is_tracking_only: true
                };

                const mutasiResponse = await apiRequest('mutasi?tracking_only=true&no_stock_update=1', 'POST', mutasiData);
                
                if (mutasiResponse.success) {
                    console.log('Auto mutasi masuk berhasil dicatat');
                } else {
                    console.warn('Gagal mencatat auto mutasi:', mutasiResponse.error);
                }
            } catch (error) {
                console.warn('Error saat membuat auto mutasi:', error);
            }
        }

        async function getLatestProdukLokasiId(produkId, lokasiId) {
            // Reload stok data untuk mendapatkan ID terbaru
            const response = await apiRequest('produk-lokasi');
            if (response.success) {
                const found = response.data.find(item => 
                    item.produk_id == produkId && item.lokasi_id == lokasiId
                );
                return found ? found.id : null;
            }
            return null;
        }

        function getFormData() {
            const form = document.getElementById('modalBody');
            const inputs = form.querySelectorAll('input, select, textarea');
            const data = {};
            
            for (const input of inputs) {
                if (input.required && !input.value.trim()) {
                    showAlert('warning', `Field ${input.previousElementSibling.textContent} harus diisi!`);
                    input.focus();
                    return null;
                }
                data[input.id] = input.value.trim();
            }
            
            return data;
        }

        function getEndpoint(type) {
            const endpoints = {
                'produk': 'produk',
                'lokasi': 'lokasi',
                'stok': 'produk-lokasi',
                'mutasi': 'mutasi'
            };
            return endpoints[type];
        }

        async function deleteItem(type, id) {
            if (!confirm('Yakin ingin menghapus data ini?')) return;
            
            const response = await apiRequest(`${getEndpoint(type)}/${id}`, 'DELETE');
            
            if (response.success) {
                await loadAllData();
                showAlert('success', `Data berhasil dihapus!`);
            } else {
                showAlert('danger', `Error: ${response.error}`);
            }
        }

        function updateStatistics() {
            document.getElementById('total-produk').textContent = currentData.produk.length;
            document.getElementById('total-lokasi').textContent = currentData.lokasi.length;
            document.getElementById('total-stok').textContent = currentData.stok.reduce((sum, item) => sum + parseInt(item.stok || 0), 0);
            document.getElementById('total-mutasi').textContent = currentData.mutasi.length;
        }

        function showLoading(type) {
            document.getElementById(`loading-${type}`).style.display = 'block';
        }

        function hideLoading(type) {
            document.getElementById(`loading-${type}`).style.display = 'none';
        }

        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }

        function logout() {
            if (confirm('Yakin ingin logout?')) {
                sessionStorage.clear();
                window.location.href = '/login';
            }
        }

        // Initialize tab change handlers
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function(e) {
                    const targetTab = e.target.getAttribute('data-bs-target');
                    // Refresh data when switching tabs if needed
                });
            });
        });
    </script>
</body>
</html>