# Laravel Inventory Management System

Sistem manajemen inventori berbasis Laravel 11 dengan fitur mutasi stok, multi-lokasi, dan REST API lengkap dengan autentikasi Bearer Token.

## 📋 Project Overview

Project ini dibuat sebagai bagian dari test technical untuk posisi Backend Developer. Aplikasi ini mengelola inventori produk dengan sistem mutasi stok yang dapat dilacak per user dan per produk, serta mendukung penyimpanan produk di multiple lokasi.

## 🔗 Links

* **GitHub Repository** : [Laravel Inventory System](https://github.com/erikzr/projek_ID-GROW_by_PT.CLAVATA)
* **Postman Documentation** : [Laravel Inventory API Collection](https://www.postman.com/spacecraft-observer-70980855/workspace/laravel-inventory/collection/38977497-f227ba6b-cbf5-4955-b580-3646b1217fb1?)

## ✨ Features

* ✅ **Laravel 11** sebagai framework utama
* ✅  **4 Model Utama** : User, Produk, Lokasi, Mutasi
* ✅ **Many-to-Many Relationship** antara Produk dan Lokasi dengan pivot table untuk stok
* ✅ **Sistem Mutasi** dengan tracking user dan otomatis update stok
* ✅ **REST API lengkap** dengan CRUD operations
* ✅ **JWT Authentication** dengan Bearer Token
* ✅ **History tracking** untuk mutasi per produk dan per user
* ✅ **Docker containerization** untuk easy deployment
* ✅ **Comprehensive API Documentation** via Postman

## 🏗️ Database Schema

### Models & Relationships

1. **User Model**
   * `id`, `nama`, `email`, `password`, `timestamps`
   * Relasi: `hasMany(Mutasi)`
2. **Produk Model**
   * `id`, `nama_produk`, `kode_produk`, `kategori`, `satuan`, `timestamps`
   * Relasi: `belongsToMany(Lokasi)` via pivot `produk_lokasi`
3. **Lokasi Model**
   * `id`, `kode_lokasi`, `nama_lokasi`, `timestamps`
   * Relasi: `belongsToMany(Produk)` via pivot `produk_lokasi`
4. **Mutasi Model**
   * `id`, `tanggal`, `jenis_mutasi`, `jumlah`, `keterangan`, `user_id`, `produk_lokasi_id`, `timestamps`
   * Relasi: `belongsTo(User)`, `belongsTo(ProdukLokasi)`
5. **Pivot ProdukLokasi**
   * `produk_id`, `lokasi_id`, `stok`, `timestamps`

## 📦 Prerequisites

Pastikan sistem Anda sudah terinstall:

* [Docker](https://docs.docker.com/get-docker/) (v20.10+)
* [Docker Compose](https://docs.docker.com/compose/install/) (v2.0+)
* [Git](https://git-scm.com/downloads)

## 🚀 Installation & Setup

### 1. Clone Repository

```bash
git clone https://github.com/erikzr/projek_ID-GROW_by_PT.CLAVATA.git
cd laravel-inventory
```

### 2. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Edit .env file dengan konfigurasi berikut:
# DB_CONNECTION=mysql
# DB_HOST=db
# DB_PORT=3306
# DB_DATABASE=laravel_inventory
# DB_USERNAME=laravel_user
# DB_PASSWORD=laravel_password
# 
# JWT_SECRET=your_jwt_secret_key
# APP_URL=http://localhost:8000
```

### 3. Build & Run Docker Containers

```bash
# Build dan jalankan semua containers
docker-compose up -d --build

# Tunggu hingga semua containers running
docker-compose ps
```

### 4. Laravel Application Setup

```bash
# Generate application key
docker-compose exec app php artisan key:generate

# Generate JWT secret
docker-compose exec app php artisan jwt:secret

# Run database migrations
docker-compose exec app php artisan migrate

# Seed database dengan sample data
docker-compose exec app php artisan db:seed
```

### 5. Verify Installation

Akses aplikasi di:

* **Laravel API** : [http://localhost:8000](http://localhost:8000/)
* **phpMyAdmin** : [http://localhost:8080](http://localhost:8080/)
* Username: `laravel_user`
* Password: `laravel_password`

## 🔌 API Endpoints

### Authentication

```
POST /api/auth/login          # Login dan generate token
POST /api/auth/logout         # Logout
POST /api/auth/refresh        # Refresh token
GET  /api/auth/me             # Get user profile
```

### CRUD Operations

```
# Users Management
GET    /api/users             # List all users
POST   /api/users             # Create new user
GET    /api/users/{id}        # Get user by ID
PUT    /api/users/{id}        # Update user
DELETE /api/users/{id}        # Delete user

# Produk Management
GET    /api/produk            # List all products
POST   /api/produk            # Create new product
GET    /api/produk/{id}       # Get product by ID
PUT    /api/produk/{id}       # Update product
DELETE /api/produk/{id}       # Delete product

# Lokasi Management
GET    /api/lokasi            # List all locations
POST   /api/lokasi            # Create new location
GET    /api/lokasi/{id}       # Get location by ID
PUT    /api/lokasi/{id}       # Update location
DELETE /api/lokasi/{id}       # Delete location

# Mutasi Management
GET    /api/mutasi            # List all mutations
POST   /api/mutasi            # Create new mutation
GET    /api/mutasi/{id}       # Get mutation by ID
PUT    /api/mutasi/{id}       # Update mutation
DELETE /api/mutasi/{id}       # Delete mutation
```

### History & Reports

```
GET /api/produk/{id}/mutasi   # History mutasi per produk
GET /api/users/{id}/mutasi    # History mutasi per user
GET /api/stok                 # Current stock per produk-lokasi
```

## 📚 API Testing dengan Postman

### 1. Import Collection

* Klik link: [Laravel Inventory API Collection](https://www.postman.com/spacecraft-observer-70980855/workspace/laravel-inventory/collection/38977497-f227ba6b-cbf5-4955-b580-3646b1217fb1?)
* Fork collection ke workspace Anda

### 2. Setup Environment

Buat environment baru dengan variables:

```
base_url: http://localhost:8000
token: {{akan_diisi_setelah_login}}
```

### 3. Authentication Flow

1. **Login** : POST `/api/auth/login` dengan email & password
2. **Copy Token** : Dari response, copy `access_token`
3. **Set Authorization** : Gunakan Bearer Token untuk semua endpoint selanjutnya

### 4. Testing Scenario

1. Login untuk mendapatkan token
2. Create Lokasi (gudang, toko)
3. Create Produk (laptop, mouse, keyboard)
4. Create ProdukLokasi dengan stok awal
5. Create Mutasi masuk/keluar
6. Check history mutasi
7. Verify stok terkini

## 🛠️ Development Commands

### Docker Management

```bash
# View container status
docker-compose ps

# View logs
docker-compose logs app
docker-compose logs -f app  # follow logs

# Restart containers
docker-compose restart app

# Stop all containers
docker-compose down

# Stop and remove volumes
docker-compose down -v
```

### Laravel Commands

```bash
# Artisan commands
docker-compose exec app php artisan migrate:fresh --seed
docker-compose exec app php artisan route:list
docker-compose exec app php artisan queue:work

# Composer commands
docker-compose exec app composer install
docker-compose exec app composer update

# Database operations
docker-compose exec app php artisan migrate:rollback
docker-compose exec app php artisan db:seed --class=UserSeeder
```

### Debugging

```bash
# Access application shell
docker-compose exec app bash

# Access MySQL
docker-compose exec db mysql -u laravel_user -p laravel_inventory

# Check application logs
docker-compose exec app tail -f storage/logs/laravel.log
```

## 🐛 Troubleshooting

### Port Already in Use

```bash
# Check what's using port 8000
netstat -tulpn | grep :8000

# Kill process if needed
sudo kill -9 $(lsof -t -i:8000)
```

### Database Connection Issues

1. Verify `.env` database configuration
2. Ensure database container is running: `docker-compose ps db`
3. Wait for database initialization (30-60 seconds on first run)

### JWT Token Issues

```bash
# Regenerate JWT secret
docker-compose exec app php artisan jwt:secret --force

# Clear config cache
docker-compose exec app php artisan config:clear
```

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

## 🏗️ Project Structure

```
laravel-inventory/
├── app/
│   ├── Http/Controllers/Api/    # API Controllers
│   ├── Models/                  # Eloquent Models
│   ├── Http/Requests/          # Form Requests
│   └── Http/Resources/         # API Resources
├── database/
│   ├── migrations/             # Database migrations
│   ├── seeders/               # Database seeders
│   └── factories/             # Model factories
├── routes/
│   └── api.php                # API routes
├── docker-compose.yml         # Docker services
├── Dockerfile                 # Application container
└── README.md                  # This file
```

## 🔒 Security Features

* ✅ JWT Authentication dengan refresh token
* ✅ API Rate limiting
* ✅ Input validation dan sanitization
* ✅ Password hashing dengan bcrypt
* ✅ CORS configuration
* ✅ SQL injection protection via Eloquent ORM

## 📊 Sample Data

Setelah running seeder, sistem akan memiliki:

* **3 Users** : Admin, Manager, Staff
* **5 Produk** : Laptop, Mouse, Keyboard, Monitor, Printer
* **3 Lokasi** : Gudang Pusat, Toko Jakarta, Toko Surabaya
* **Sample Mutasi** : Berbagai transaksi masuk/keluar

## 🚀 Deployment Notes

### Production Checklist

* [ ] Set `APP_ENV=production` di `.env`
* [ ] Generate strong `APP_KEY` dan `JWT_SECRET`
* [ ] Setup proper database credentials
* [ ] Configure reverse proxy (Nginx)
* [ ] Setup SSL certificates
* [ ] Configure log rotation
* [ ] Setup backup strategy
* [ ] Monitor resource usage

### Docker Production

```bash
# Build production image
docker build -t laravel-inventory:prod .

# Run with production environment
docker run -d --name inventory-app \
  -p 8000:8000 \
  -e APP_ENV=production \
  laravel-inventory:prod
```

## 🧪 Testing

```bash
# Run feature tests
docker-compose exec app php artisan test

# Run specific test
docker-compose exec app php artisan test --filter AuthTest

# Generate test coverage
docker-compose exec app php artisan test --coverage
```

## 📝 Additional Notes

Aplikasi ini dikembangkan mengikuti best practices:

* **RESTful API design** dengan proper HTTP methods
* **Resource-based responses** untuk konsistensi output
* **Proper error handling** dengan meaningful messages
* **Database transactions** untuk data integrity
* **Eager loading** untuk optimized queries
* **API versioning** ready untuk future updates

## 👨‍💻 Developer

 **Nama** : Muhammad Erik Zubair Rohman

 **Email** : muhammaderikzubairrohman@gmail.com

 **LinkedIn** : [linkedin.com/in/profile-anda]

---

*Project ini dibuat sebagai bagian dari technical test untuk posisi Backend Developer. Semua requirements telah diimplementasi sesuai dengan spesifikasi yang diminta.*

**🚀 Happy Coding!**
