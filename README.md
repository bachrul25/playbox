# PlayBox Rental Management System

Aplikasi web manajemen rental PlayBox (Laravel 12 + Livewire 3 + Bootstrap 5) untuk mengelola dua jenis sistem rental:

1. **Rental Pribadi** — pendapatan dipotong 20% maintenance, 80% jadi keuntungan owner.
2. **Rental Kerjasama** dengan Cafe/Mitra — potong biaya staff penunggu Rp 800.000, sisa bersih dibagi 50:50 antara owner & cafe.

---

## Teknologi
- Laravel 12, Livewire 3 (full page components, **tanpa Controller untuk CRUD utama**)
- Bootstrap 5 (CDN), Bootstrap Icons (CDN)
- Chart.js (CDN) untuk grafik dashboard
- SweetAlert2 (CDN) untuk notifikasi & konfirmasi
- DomPDF (`barryvdh/laravel-dompdf`) untuk export PDF
- Laravel Excel (`maatwebsite/excel`) untuk export XLSX
- MySQL (default) atau SQLite

## Arsitektur
```
Route → Livewire Component → Repository → Model → Database
```

| Layer | Path |
|---|---|
| Routes | `routes/web.php` |
| Livewire components | `app/Livewire/` |
| Repository | `app/Repositories/` |
| Models | `app/Models/` |
| Helper bisnis | `app/Support/RentalCalculator.php`, `app/Support/Rupiah.php` |
| Middleware role | `app/Http/Middleware/RoleMiddleware.php` |
| Layout & UI | `resources/views/layouts`, `resources/views/livewire`, `resources/views/partials` |

## Setup

### 1. Clone & install
```bash
git clone <repo-url> playbox
cd playbox
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Database
**Default — SQLite (cepat):**
```bash
touch database/database.sqlite
# DB_CONNECTION=sqlite di .env
```

**MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=playbox
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migrate & seed
```bash
php artisan migrate:fresh --seed --force
```

### 4. Jalankan
```bash
php artisan serve --host=127.0.0.1 --port=8001
# Buka http://127.0.0.1:8001
```

## Akun Demo

| Role | Email | Password |
|---|---|---|
| Admin | admin@playbox.com | password |
| Owner | owner@playbox.com | password |
| Mitra (Cafe Tata Krama) | mitra@playbox.com | password |

### Hak Akses

- **Admin** — semua fitur (CRUD PlayBox/Mitra/User, transaksi, biaya, semua laporan).
- **Owner** — dashboard + lihat data + lihat semua laporan + export.
- **Mitra** — hanya laporan kerjasama miliknya sendiri.

## Aturan Perhitungan

### Pribadi (80/20)
```
maintenance  = total_income * 20%
owner_profit = total_income * 80%
```

### Kerjasama (potong staff lalu 50:50)
```
staff_cost   = Rp 800.000   // tetap
net_income   = max(0, total_income - staff_cost)
owner_share  = net_income * 50%
partner_share= net_income * 50%
```
Jika `total_income < staff_cost`, sistem memberi peringatan & menyimpan bagi hasil = 0.

## Halaman Utama

| Path | Komponen | Role |
|---|---|---|
| `/dashboard` | DashboardComponent | semua login |
| `/playboxes` | PlayboxComponent | admin, owner |
| `/partners` | PartnerComponent | admin, owner |
| `/rentals` | RentalComponent | admin |
| `/expenses` | ExpenseComponent | admin, owner |
| `/reports/private` | PrivateReportComponent | admin, owner |
| `/reports/partnership` | PartnershipReportComponent | admin, owner, mitra |
| `/users` | UserManagementComponent | admin |

Tidak ada Controller untuk CRUD utama — semua proses dijalankan oleh Livewire components.

## Export Laporan
- **PDF**: tombol *Export PDF* di setiap halaman laporan (DomPDF, view `resources/views/exports/*-report-pdf.blade.php`).
- **Excel**: tombol *Export Excel* (Maatwebsite Excel, class di `app/Exports/`).

## Lint / Style
```bash
./vendor/bin/pint
```

## Lisensi
MIT (template Laravel default).
