// Global Variables
let authToken = null;
let currentUser = null;
let chart = null;

// API Base URL - Sesuaikan dengan URL Laravel API Anda
const API_BASE_URL = 'http://localhost:8000/api';

// Initialize Application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Check if user is already logged in
    const token = localStorage.getItem('auth_token');
    if (token) {
        authToken = token;
        showDashboard();
        loadDashboardData();
    } else {
        showLoginModal();
    }
    
    // Setup event listeners
    setupEventListeners();
}

function setupEventListeners() {
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Close modal
    const closeModal = document.querySelector('.close');
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            document.getElementById('loginModal').style.display = 'none';
        });
    }
    
    // Sidebar navigation
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', handleNavigation);
    });
    
    // Logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
}

// Authentication Functions
async function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    try {
        showLoading('Logging in...');
        
        const response = await fetch(`${API_BASE_URL}/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            authToken = data.data.token;
            currentUser = data.data.user;
            localStorage.setItem('auth_token', authToken);
            
            hideLoading();
            document.getElementById('loginModal').style.display = 'none';
            showDashboard();
            loadDashboardData();
        } else {
            throw new Error(data.message || 'Login failed');
        }
    } catch (error) {
        hideLoading();
        showError('Login failed: ' + error.message);
    }
}

async function handleLogout() {
    try {
        await fetch(`${API_BASE_URL}/auth/logout`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            }
        });
    } catch (error) {
        console.log('Logout error:', error);
    }
    
    authToken = null;
    currentUser = null;
    localStorage.removeItem('auth_token');
    
    document.getElementById('dashboard').classList.add('dashboard-hidden');
    showLoginModal();
}

// Navigation Functions
function handleNavigation(e) {
    e.preventDefault();
    
    const page = e.target.closest('a').dataset.page;
    if (!page) return;
    
    // Update active menu item
    document.querySelectorAll('.sidebar-menu li').forEach(li => {
        li.classList.remove('active');
    });
    e.target.closest('li').classList.add('active');
    
    // Show corresponding page
    showPage(page);
    
    // Load page data
    loadPageData(page);
}

function showPage(pageName) {
    // Hide all pages
    document.querySelectorAll('.page').forEach(page => {
        page.classList.remove('active');
    });
    
    // Show selected page
    const targetPage = document.getElementById(pageName + 'Page');
    if (targetPage) {
        targetPage.classList.add('active');
    }
    
    // Update page title
    const pageTitle = document.getElementById('pageTitle');
    const titles = {
        'dashboard': 'Dashboard',
        'produk': 'Manajemen Produk',
        'lokasi': 'Manajemen Lokasi',
        'mutasi': 'Manajemen Mutasi',
        'users': 'Manajemen User',
        'reports': 'Laporan'
    };
    
    if (pageTitle && titles[pageName]) {
        pageTitle.textContent = titles[pageName];
    }
}

// UI Helper Functions
function showLoginModal() {
    document.getElementById('loginModal').style.display = 'block';
}

function showDashboard() {
    document.getElementById('dashboard').classList.remove('dashboard-hidden');
    if (currentUser) {
        document.getElementById('userName').textContent = `Welcome, ${currentUser.name}!`;
    }
}

function showLoading(message = 'Loading...') {
    // Create loading overlay if it doesn't exist
    let loadingDiv = document.getElementById('loadingOverlay');
    if (!loadingDiv) {
        loadingDiv = document.createElement('div');
        loadingDiv.id = 'loadingOverlay';
        loadingDiv.innerHTML = `
            <div class="loading-content">
                <div class="spinner"></div>
                <p>${message}</p>
            </div>
        `;
        document.body.appendChild(loadingDiv);
    }
    loadingDiv.style.display = 'flex';
}

function hideLoading() {
    const loadingDiv = document.getElementById('loadingOverlay');
    if (loadingDiv) {
        loadingDiv.style.display = 'none';
    }
}

function showError(message) {
    alert('Error: ' + message);
}

function showSuccess(message) {
    alert('Success: ' + message);
}

// Data Loading Functions
async function loadPageData(page) {
    switch (page) {
        case 'dashboard':
            await loadDashboardData();
            break;
        case 'produk':
            await loadProdukData();
            break;
        case 'lokasi':
            await loadLokasiData();
            break;
        case 'mutasi':
            await loadMutasiData();
            break;
        case 'users':
            await loadUsersData();
            break;
        case 'reports':
            // Reports will be loaded on demand
            break;
    }
}

async function loadDashboardData() {
    try {
        showLoading('Loading dashboard...');
        
        // Load statistics
        const statsResponse = await apiRequest('/dashboard/stats');
        if (statsResponse.success) {
            updateDashboardStats(statsResponse.data);
        }
        
        // Load recent mutations
        const mutasiResponse = await apiRequest('/mutasi?limit=5');
        if (mutasiResponse.success) {
            updateRecentMutasi(mutasiResponse.data);
        }
        
        // Load low stock items
        const lowStockResponse = await apiRequest('/produk/low-stock');
        if (lowStockResponse.success) {
            updateLowStockAlert(lowStockResponse.data);
        }
        
        // Load chart data
        const chartResponse = await apiRequest('/dashboard/chart');
        if (chartResponse.success) {
            updateMutasiChart(chartResponse.data);
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to load dashboard data: ' + error.message);
    }
}

function updateDashboardStats(stats) {
    document.getElementById('totalProduk').textContent = stats.total_produk || 0;
    document.getElementById('totalLokasi').textContent = stats.total_lokasi || 0;
    document.getElementById('totalUser').textContent = stats.total_user || 0;
    document.getElementById('totalStok').textContent = stats.total_stok || 0;
}

function updateRecentMutasi(mutasiData) {
    const container = document.getElementById('recentMutasi');
    container.innerHTML = '';
    
    if (mutasiData.length === 0) {
        container.innerHTML = '<p>Tidak ada mutasi terbaru</p>';
        return;
    }
    
    mutasiData.forEach(mutasi => {
        const div = document.createElement('div');
        div.className = 'activity-item';
        div.innerHTML = `
            <div class="activity-info">
                <strong>${mutasi.jenis}</strong> - ${mutasi.produk?.nama}
                <small>${formatDate(mutasi.tanggal)}</small>
            </div>
            <div class="activity-amount ${mutasi.jenis === 'masuk' ? 'positive' : 'negative'}">
                ${mutasi.jenis === 'masuk' ? '+' : '-'}${mutasi.jumlah}
            </div>
        `;
        container.appendChild(div);
    });
}

function updateLowStockAlert(lowStockItems) {
    const container = document.getElementById('lowStockList');
    container.innerHTML = '';
    
    if (lowStockItems.length === 0) {
        container.innerHTML = '<p>Semua produk memiliki stok yang cukup</p>';
        return;
    }
    
    lowStockItems.forEach(item => {
        const div = document.createElement('div');
        div.className = 'alert-item';
        div.innerHTML = `
            <div class="alert-info">
                <strong>${item.nama}</strong>
                <small>Lokasi: ${item.lokasi?.nama}</small>
            </div>
            <div class="alert-stock">
                Stok: ${item.stok}
            </div>
        `;
        container.appendChild(div);
    });
}

function updateMutasiChart(chartData) {
    const ctx = document.getElementById('mutasiChart').getContext('2d');
    
    if (chart) {
        chart.destroy();
    }
    
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels || [],
            datasets: [{
                label: 'Mutasi Masuk',
                data: chartData.masuk || [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Mutasi Keluar',
                data: chartData.keluar || [],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Product Management Functions
async function loadProdukData() {
    try {
        showLoading('Loading products...');
        const response = await apiRequest('/produk');
        
        if (response.success) {
            updateProdukTable(response.data);
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to load products: ' + error.message);
    }
}

function updateProdukTable(produkData) {
    const tbody = document.querySelector('#produkTable tbody');
    tbody.innerHTML = '';
    
    produkData.forEach(produk => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${produk.kode}</td>
            <td>${produk.nama}</td>
            <td>${produk.kategori}</td>
            <td>${produk.satuan}</td>
            <td>Rp ${formatNumber(produk.harga)}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editProduk(${produk.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteProduk(${produk.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function showAddProdukModal() {
    const modalHtml = `
        <div class="modal" id="produkModal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('produkModal')">&times;</span>
                <h2>Tambah Produk</h2>
                <form id="produkForm">
                    <div class="form-group">
                        <label for="produkKode">Kode:</label>
                        <input type="text" id="produkKode" name="kode" required>
                    </div>
                    <div class="form-group">
                        <label for="produkNama">Nama:</label>
                        <input type="text" id="produkNama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="produkKategori">Kategori:</label>
                        <input type="text" id="produkKategori" name="kategori" required>
                    </div>
                    <div class="form-group">
                        <label for="produkSatuan">Satuan:</label>
                        <input type="text" id="produkSatuan" name="satuan" required>
                    </div>
                    <div class="form-group">
                        <label for="produkHarga">Harga:</label>
                        <input type="number" id="produkHarga" name="harga" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modalHtml;
    document.getElementById('produkModal').style.display = 'block';
    
    document.getElementById('produkForm').addEventListener('submit', handleAddProduk);
}

async function handleAddProduk(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const produkData = Object.fromEntries(formData);
    
    try {
        showLoading('Adding product...');
        const response = await apiRequest('/produk', 'POST', produkData);
        
        if (response.success) {
            showSuccess('Product added successfully');
            closeModal('produkModal');
            loadProdukData();
        } else {
            throw new Error(response.message || 'Failed to add product');
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to add product: ' + error.message);
    }
}

async function deleteProduk(id) {
    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }
    
    try {
        showLoading('Deleting product...');
        const response = await apiRequest(`/produk/${id}`, 'DELETE');
        
        if (response.success) {
            showSuccess('Product deleted successfully');
            loadProdukData();
        } else {
            throw new Error(response.message || 'Failed to delete product');
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to delete product: ' + error.message);
    }
}

// Location Management Functions
async function loadLokasiData() {
    try {
        showLoading('Loading locations...');
        const response = await apiRequest('/lokasi');
        
        if (response.success) {
            updateLokasiTable(response.data);
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to load locations: ' + error.message);
    }
}

function updateLokasiTable(lokasiData) {
    const tbody = document.querySelector('#lokasiTable tbody');
    tbody.innerHTML = '';
    
    lokasiData.forEach(lokasi => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${lokasi.kode}</td>
            <td>${lokasi.nama}</td>
            <td>${lokasi.deskripsi || '-'}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editLokasi(${lokasi.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteLokasi(${lokasi.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function showAddLokasiModal() {
    const modalHtml = `
        <div class="modal" id="lokasiModal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('lokasiModal')">&times;</span>
                <h2>Tambah Lokasi</h2>
                <form id="lokasiForm">
                    <div class="form-group">
                        <label for="lokasiKode">Kode:</label>
                        <input type="text" id="lokasiKode" name="kode" required>
                    </div>
                    <div class="form-group">
                        <label for="lokasiNama">Nama:</label>
                        <input type="text" id="lokasiNama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="lokasiDeskripsi">Deskripsi:</label>
                        <textarea id="lokasiDeskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modalHtml;
    document.getElementById('lokasiModal').style.display = 'block';
    
    document.getElementById('lokasiForm').addEventListener('submit', handleAddLokasi);
}

async function handleAddLokasi(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const lokasiData = Object.fromEntries(formData);
    
    try {
        showLoading('Adding location...');
        const response = await apiRequest('/lokasi', 'POST', lokasiData);
        
        if (response.success) {
            showSuccess('Location added successfully');
            closeModal('lokasiModal');
            loadLokasiData();
        } else {
            throw new Error(response.message || 'Failed to add location');
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to add location: ' + error.message);
    }
}

// Mutation Management Functions
async function loadMutasiData() {
    try {
        showLoading('Loading mutations...');
        const response = await apiRequest('/mutasi');
        
        if (response.success) {
            updateMutasiTable(response.data);
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to load mutations: ' + error.message);
    }
}

function updateMutasiTable(mutasiData) {
    const tbody = document.querySelector('#mutasiTable tbody');
    tbody.innerHTML = '';
    
    mutasiData.forEach(mutasi => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${formatDate(mutasi.tanggal)}</td>
            <td>
                <span class="badge ${mutasi.jenis === 'masuk' ? 'badge-success' : 'badge-danger'}">
                    ${mutasi.jenis}
                </span>
            </td>
            <td>${mutasi.produk?.nama}</td>
            <td>${mutasi.lokasi?.nama}</td>
            <td>${mutasi.jumlah}</td>
            <td>${mutasi.user?.name}</td>
            <td>
                <button class="btn btn-sm btn-danger" onclick="deleteMutasi(${mutasi.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function showAddMutasiModal() {
    // First load required data
    loadMutasiModalData();
}

async function loadMutasiModalData() {
    try {
        const [produkResponse, lokasiResponse] = await Promise.all([
            apiRequest('/produk'),
            apiRequest('/lokasi')
        ]);
        
        if (produkResponse.success && lokasiResponse.success) {
            showMutasiModal(produkResponse.data, lokasiResponse.data);
        }
    } catch (error) {
        showError('Failed to load modal data: ' + error.message);
    }
}

function showMutasiModal(produkData, lokasiData) {
    const produkOptions = produkData.map(p => `<option value="${p.id}">${p.nama}</option>`).join('');
    const lokasiOptions = lokasiData.map(l => `<option value="${l.id}">${l.nama}</option>`).join('');
    
    const modalHtml = `
        <div class="modal" id="mutasiModal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('mutasiModal')">&times;</span>
                <h2>Tambah Mutasi</h2>
                <form id="mutasiForm">
                    <div class="form-group">
                        <label for="mutasiTanggal">Tanggal:</label>
                        <input type="date" id="mutasiTanggal" name="tanggal" value="${getCurrentDate()}" required>
                    </div>
                    <div class="form-group">
                        <label for="mutasiJenis">Jenis:</label>
                        <select id="mutasiJenis" name="jenis" required>
                            <option value="">Pilih Jenis</option>
                            <option value="masuk">Masuk</option>
                            <option value="keluar">Keluar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mutasiProduk">Produk:</label>
                        <select id="mutasiProduk" name="produk_id" required>
                            <option value="">Pilih Produk</option>
                            ${produkOptions}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mutasiLokasi">Lokasi:</label>
                        <select id="mutasiLokasi" name="lokasi_id" required>
                            <option value="">Pilih Lokasi</option>
                            ${lokasiOptions}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mutasiJumlah">Jumlah:</label>
                        <input type="number" id="mutasiJumlah" name="jumlah" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="mutasiKeterangan">Keterangan:</label>
                        <textarea id="mutasiKeterangan" name="keterangan" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modalHtml;
    document.getElementById('mutasiModal').style.display = 'block';
    
    document.getElementById('mutasiForm').addEventListener('submit', handleAddMutasi);
}

async function handleAddMutasi(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const mutasiData = Object.fromEntries(formData);
    
    try {
        showLoading('Adding mutation...');
        const response = await apiRequest('/mutasi', 'POST', mutasiData);
        
        if (response.success) {
            showSuccess('Mutation added successfully');
            closeModal('mutasiModal');
            loadMutasiData();
            // Refresh dashboard if it's the current page
            if (document.getElementById('dashboardPage').classList.contains('active')) {
                loadDashboardData();
            }
        } else {
            throw new Error(response.message || 'Failed to add mutation');
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to add mutation: ' + error.message);
    }
}

// User Management Functions
async function loadUsersData() {
    try {
        showLoading('Loading users...');
        const response = await apiRequest('/users');
        
        if (response.success) {
            updateUsersTable(response.data);
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to load users: ' + error.message);
    }
}

function updateUsersTable(usersData) {
    const tbody = document.querySelector('#usersTable tbody');
    tbody.innerHTML = '';
    
    usersData.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editUser(${user.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})" 
                        ${user.id === currentUser?.id ? 'disabled' : ''}>
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function showAddUserModal() {
    const modalHtml = `
        <div class="modal" id="userModal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('userModal')">&times;</span>
                <h2>Tambah User</h2>
                <form id="userForm">
                    <div class="form-group">
                        <label for="userName">Nama:</label>
                        <input type="text" id="userName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="userEmail">Email:</label>
                        <input type="email" id="userEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="userPassword">Password:</label>
                        <input type="password" id="userPassword" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="userPasswordConfirm">Konfirmasi Password:</label>
                        <input type="password" id="userPasswordConfirm" name="password_confirmation" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    `;
    
    document.getElementById('modalContainer').innerHTML = modalHtml;
    document.getElementById('userModal').style.display = 'block';
    
    document.getElementById('userForm').addEventListener('submit', handleAddUser);
}

async function handleAddUser(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const userData = Object.fromEntries(formData);
    
    if (userData.password !== userData.password_confirmation) {
        showError('Password confirmation does not match');
        return;
    }
    
    try {
        showLoading('Adding user...');
        const response = await apiRequest('/users', 'POST', userData);
        
        if (response.success) {
            showSuccess('User added successfully');
            closeModal('userModal');
            loadUsersData();
        } else {
            throw new Error(response.message || 'Failed to add user');
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to add user: ' + error.message);
    }
}

// Report Functions
async function generateReport() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const reportType = document.getElementById('reportType').value;
    
    if (!startDate || !endDate) {
        showError('Please select start and end dates');
        return;
    }
    
    try {
        showLoading('Generating report...');
        
        const params = new URLSearchParams({
            start_date: startDate,
            end_date: endDate,
            type: reportType
        });
        
        const response = await apiRequest(`/reports?${params}`);
        
        if (response.success) {
            displayReport(response.data);
        } else {
            throw new Error(response.message || 'Failed to generate report');
        }
        
        hideLoading();
    } catch (error) {
        hideLoading();
        showError('Failed to generate report: ' + error.message);
    }
}

function displayReport(reportData) {
    const container = document.getElementById('reportResults');
    
    let html = `
        <div class="report-summary">
            <h3>Ringkasan Laporan</h3>
            <div class="summary-stats">
                <div class="summary-item">
                    <strong>Total Mutasi:</strong> ${reportData.total || 0}
                </div>
                <div class="summary-item">
                    <strong>Total Masuk:</strong> ${reportData.total_masuk || 0}
                </div>
                <div class="summary-item">
                    <strong>Total Keluar:</strong> ${reportData.total_keluar || 0}
                </div>
            </div>
        </div>
        
        <div class="report-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Produk</th>
                        <th>Lokasi</th>
                        <th>Jumlah</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Tambahkan data ke tabel
    if (reportData.data && reportData.data.length > 0) {
        reportData.data.forEach(item => {
            html += `
                <tr>
                    <td>${formatDate(item.tanggal)}</td>
                    <td>
                        <span class="badge ${item.jenis === 'masuk' ? 'badge-success' : 'badge-danger'}">
                            ${item.jenis}
                        </span>
                    </td>
                    <td>${item.produk?.nama || '-'}</td>
                    <td>${item.lokasi?.nama || '-'}</td>
                    <td>${item.jumlah}</td>
                    <td>${item.user?.name || '-'}</td>
                </tr>
            `;
        });
    } else {
        html += `
            <tr>
                <td colspan="6" class="text-center">Tidak ada data untuk periode yang dipilih</td>
            </tr>
        `;
    }
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

// Fungsi helper untuk format tanggal (jika belum ada)
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

// Fungsi helper untuk format angka (jika belum ada)
function formatNumber(number) {
    if (!number) return '0';
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Fungsi helper untuk mendapatkan tanggal hari ini (jika belum ada)
function getCurrentDate() {
    const today = new Date();
    return today.toISOString().split('T')[0];
}

// Fungsi untuk menutup modal (jika belum ada)
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Fungsi API request (jika belum ada implementasi lengkapnya)
async function apiRequest(endpoint, method = 'GET', data = null) {
    const config = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    };
    
    if (authToken) {
        config.headers['Authorization'] = `Bearer ${authToken}`;
    }
    
    if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
        config.body = JSON.stringify(data);
    }
    
    const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
    return await response.json();
}