# Software Requirements Specification (SRS)

## Siwride — Sistem Manajemen Transportasi & Pemesanan Kendaraan

**Versi:** 1.0
**Tanggal:** 4 Juni 2026
**Status:** Draft

---

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Deskripsi Umum](#2-deskripsi-umum)
3. [Fitur Sistem](#3-fitur-sistem)
4. [Arsitektur Sistem](#4-arsitektur-sistem)
5. [Database](#5-database)
6. [API Endpoint](#6-api-endpoint)
7. [Antarmuka Pengguna](#7-antarmuka-pengguna)
8. [Integrasi Pihak Ketiga](#8-integrasi-pihak-ketiga)
9. [Kebutuhan Non-Fungsional](#9-kebutuhan-non-fungsional)
10. [Keamanan](#10-keamanan)

---

## 1. Pendahuluan

### 1.1 Tujuan

Dokumen SRS ini bertujuan untuk mendefinisikan secara lengkap kebutuhan perangkat lunak untuk sistem Siwride — sebuah platform manajemen transportasi yang mencakup pemesanan kendaraan online (customer-facing), dashboard admin, dan API mobile untuk driver.

### 1.2 Ruang Lingkup

Sistem Siwride mencakup:
- **Portal Pemesanan Publik**: Customer dapat mencari rute, melihat harga, memilih kendaraan, dan melakukan booking dengan pembayaran via Xendit.
- **Admin Dashboard**: Admin mengelola order, driver, kendaraan, zona, harga, dan pengaturan sistem.
- **Mobile API (REST)**: API untuk aplikasi mobile driver (D'Jali Team) termasuk autentikasi, job management, tracking GPS, dan laporan gaji.
- **Sistem Klaim Driver**: Driver dapat mengklaim job dari pool bersama dengan sistem antrian konfirmasi admin.
- **Integrasi WhatsApp**: Notifikasi dan pengiriman detail order ke grup WhatsApp dan private message ke driver.

### 1.3 Definisi, Akronim, dan Singkatan

| Istilah | Definisi |
|---------|----------|
| Customer | Pengguna yang melakukan pemesanan transportasi |
| Driver | Pengemudi yang terdaftar dan telah disetujui admin |
| Admin | Pengelola sistem yang memiliki akses penuh |
| Order/Job | Pesanan transportasi |
| Booking Code | Kode unik pemesanan (format: SW + 6 karakter) |
| Xendit | Payment gateway untuk pemrosesan pembayaran |
| Zone | Wilayah geografis layanan (Bali) |
| Wayfinder | Laravel package untuk generate fungsi TypeScript dari route |
| Fortify | Laravel package untuk autentikasi (login, register, 2FA) |
| Inertia | Framework yang menghubungkan Laravel backend dengan Svelte frontend |

### 1.4 Referensi

- Laravel Framework v13.2.0
- Inertia.js v3 + Inertia Laravel v3.0.1
- Svelte 5
- Tailwind CSS v4
- Laravel Fortify v1.36.2
- Laravel Sanctum v4.3.1
- MySQL Database
- Xendit Payment Gateway v7.0
- Google Maps API

### 1.5 Gambaran Produk

Siwride adalah sistem informasi manajemen transportasi berbasis web dan API yang dikembangkan untuk melayani operasional perusahaan transportasi di Bali. Sistem ini menggantikan backend Firebase yang sebelumnya digunakan oleh aplikasi D'Jali Team.

---

## 2. Deskripsi Umum

### 2.1 Perspektif Produk

Siwride terdiri dari tiga antarmuka utama:

1. **Website Publik (Svelte + Inertia)** — Landing page, booking form, checkout, tracking booking
2. **Admin Panel (Svelte + Inertia)** — Dashboard, manajemen data, pengaturan
3. **REST API** — Melayani aplikasi mobile driver Flutter

### 2.2 Karakteristik Pengguna

| Role | Deskripsi | Hak Akses |
|------|-----------|-----------|
| Guest/Customer | Pengunjung website | Melihat halaman publik, melakukan booking, tracking |
| Customer (Terdaftar) | Pelanggan yang memiliki akun | Booking, riwayat pesanan, profil |
| Admin | Pengelola sistem | Semua fitur admin, manajemen data master |
| Driver (User) | Pengemudi via web | Melihat order yang ditugaskan, update status |
| Driver (Mobile) | Pengemudi via aplikasi Flutter | Claim job, update status, upload evidence, tracking GPS |

### 2.3 Lingkungan Operasi

- **Server**: PHP 8.4 + Nginx/Apache (VPS atau Cloud)
- **Database**: MySQL
- **Frontend**: Svelte 5 via Inertia.js
- **Mobile App**: Flutter (terpisah, mengkonsumsi REST API)
- **Storage**: Local storage atau S3-compatible
- **Cache**: Database driver untuk cache & session

### 2.5 Asumsi dan Dependensi

- API Google Maps tersedia dan aktif untuk geocoding dan distance matrix
- Akun Xendit aktif untuk pemrosesan pembayaran
- API WhatsApp tersedia untuk notifikasi
- MySQL dengan dukungan spatial (ST_Contains, ST_GeomFromText) untuk zone detection

---

## 3. Fitur Sistem

### 3.1 Manajemen Autentikasi & Pengguna

#### 3.1.1 Autentikasi Web (Fortify)
- **ID**: FR-AUTH-01
- **Deskripsi**: Login, register, logout dengan Laravel Fortify
- **Fitur**:
  - Login dengan email & password
  - Registrasi user baru (admin/driver)
  - Verifikasi email
  - Two-Factor Authentication (2FA)
  - Reset password (lupa password)
- **Route Prefix**: `/` (default Fortify routes)
- **Auth Guard**: `web` (user), `customer` (customer)

#### 3.1.2 Autentikasi Customer
- **ID**: FR-AUTH-02
- **Deskripsi**: Autentikasi khusus customer untuk mengelola profil dan riwayat
- **Endpoint**:
  - `GET/POST /customer/login` — Login customer
  - `GET/POST /customer/register` — Register customer
  - `POST /customer/logout` — Logout customer
- **Session**: Guard `customer`
- **Fitur**: Login + Registrasi + Riwayat pesanan

#### 3.1.3 Autentikasi API (Sanctum)
- **ID**: FR-AUTH-03
- **Deskripsi**: API token-based authentication untuk aplikasi mobile
- **Endpoint**:
  - `POST /api/v1/auth/login` — Login, return token
  - `POST /api/v1/auth/register` — Register driver baru (status: pending)
  - `POST /api/v1/auth/logout` — Revoke current token
  - `POST /api/v1/auth/logout-all` — Revoke all tokens
- **Auth**: Laravel Sanctum

### 3.2 Manajemen Order

#### 3.2.1 Customer Booking (Public)
- **ID**: FR-ORDER-01
- **Endpoint Web**:
  - `GET /booking` — Halaman form booking dengan pencarian rute
  - `GET /booking/checkout?vehicle_category_id=...` — Multi-step checkout (detail transfer → info penumpang → ekstra → ringkasan → pembayaran)
  - `POST /orders` — Simpan order baru + generate Xendit invoice + redirect ke halaman pembayaran
  - `GET /booking/estimate-price?pickup_lat=...&pickup_lng=...&dropoff_lat=...&dropoff_lng=...` — Estimasi harga real-time
  - `GET /booking/{booking_code}` — Detail booking
  - `GET /booking/success?code=...` — Halaman sukses
  - `GET /booking/payment-success?code=...` — Halaman sukses pembayaran
  - `GET /booking/track?code=...` — Track booking via kode
- **Flow**: Pilih rute → Pilih kendaraan → Isi data → Pilih ekstra → Bayar via Xendit → Redirect ke invoice

#### 3.2.2 Admin Order Management
- **ID**: FR-ORDER-02
- **Deskripsi**: CRUD order oleh admin
- **Fitur**:
  - List orders dengan filter (search, status, driver, date range)
  - Pagination (10 per page)
  - View Calendar (semua order dalam format kalender)
  - Create order manual
  - Edit order
  - Delete order
  - Update status
  - Assign driver ke order
  - Accept/Reject claim driver
  - Share order ke WhatsApp Group
  - Resend WA ke driver
  - Import Excel (bulk insert dari file .xlsx/.xls/.csv)
  - Download template Excel

#### 3.2.3 Mobile Job Management (API)
- **ID**: FR-ORDER-03
- **Deskripsi**: Job management untuk aplikasi mobile driver
- **Endpoint API v1**:
  - `GET /api/v1/jobs/shared` — Lihat job pool (belum ada driver)
  - `GET /api/v1/jobs/my` — Job yang sudah diambil
  - `GET /api/v1/jobs/{id}` — Detail job
  - `GET /api/v1/jobs/{id}/history` — Riwayat status job
  - `POST /api/v1/jobs/{id}/take` — Ambil job dari pool (dengan locking)
  - `POST /api/v1/jobs/{id}/claim` — Klaim job (menunggu konfirmasi admin)
  - `PATCH /api/v1/jobs/{id}/status` — Update status perjalanan
  - `POST /api/v1/jobs/{id}/evidence` — Upload foto bukti
- **Aturan Bisnis**:
  - Informasi customer disembunyikan sebelum jam 16:00 di hari H
  - Update status: pending → otw → tiba → completed
  - Wajib upload foto saat "Tiba" dan "Berangkat"

### 3.3 Manajemen Driver

#### 3.3.1 Registrasi & Verifikasi
- **ID**: FR-DRV-01
- **Deskripsi**: Driver mendaftar, admin menyetujui
- **Fitur**:
  - Registrasi driver (via web `/driver/register` atau API `/api/v1/auth/register`)
  - Status: `inactive` (default) → `active` (setelah disetujui admin)
  - Upload dokumen: KTP/NIK, SIM, foto profil
  - Sync data driver ke user table

#### 3.3.2 Manajemen Driver (Admin)
- **ID**: FR-DRV-02
- **Fitur**:
  - CRUD driver
  - Sync driver ke user table (membuat akun login web)
  - Lihat daftar driver + kendaraan
  - Filter & search driver

### 3.4 Manajemen Kendaraan

- **ID**: FR-VEH-01
- **Fitur**:
  - CRUD kendaraan (admin)
  - Relasi: Driver hasMany Vehicles
  - Fields: brand, model, type, registration_number, color, status
- **Vehicle Categories**: Menyimpan tipe kendaraan yang ditampilkan ke customer saat booking
  - Fields: slug, title, description, capacity, passenger/luggage capacity, advantages, base_price, price_per_km, examples, image, vehicle_type

### 3.5 Zona & Pricing

#### 3.5.1 Manajemen Zona
- **ID**: FR-ZONE-01
- **Deskripsi**: Mendefinisikan wilayah geografis layanan
- **Fitur**:
  - Buat zona dengan koordinat poligon (MySQL Spatial)
  - Deteksi titik dalam zona dengan `ST_Contains`
  - Status aktif/non-aktif
  - Validasi titik koordinat

#### 3.5.2 Pricing Rules
- **ID**: FR-ZONE-02
- **Deskripsi**: Aturan harga antar zona
- **Fitur**:
  - Pairing pickup_zone → dropoff_zone
  - Formula: `max(minimum_price, base_price + distance_km × price_per_km)`
  - Price per km bisa dari zona atau dari vehicle category
  - Status aktif/non-aktif
  - Kalkulasi harga otomatis

### 3.6 Dashboard Admin

- **ID**: FR-DASH-01
- **Komponen**:
  - **KPI Cards**: Total bookings, Today's schedule, Unassigned orders, Estimated Revenue
  - **Charts**: Revenue trend (7 hari, ApexCharts area chart), Status distribution (donut chart)
  - **Recent Orders Table**: 5 order terbaru
  - **Statistik**: Total drivers, total vehicles

### 3.7 GPS Tracking

- **ID**: FR-TRACK-01
- **Deskripsi**: Tracking real-time lokasi driver
- **Endpoint API**:
  - `POST /api/v1/tracking/update` — Driver update lokasi (setiap 30 detik)
  - `GET /api/v1/tracking/active` — Admin lihat semua lokasi driver aktif (5 menit terakhir)

### 3.8 Laporan Gaji

- **ID**: FR-REPORT-01
- **Deskripsi**: Rekapitulasi gaji driver per periode
- **Endpoint**: `GET /api/v1/reports/salary?month=&year=&period=`
- **Aturan**: Job cancelled atau cash tidak dihitung
- **Periode**:
  - Periode 1: Tanggal 1-15 (dibayar tgl 25)
  - Periode 2: Tanggal 16-akhir bulan (dibayar tgl 10 bulan berikutnya)

### 3.9 Payroll

- **ID**: FR-PAYR-01
- **Deskripsi**: Manajemen penggajian driver
- **Tabel**: payrolls (driver_id, period_label, total_jobs, amount, admin_fee, net_amount, status)

### 3.10 WhatsApp Integration

- **ID**: FR-WA-01
- **Deskripsi**: Notifikasi order via WhatsApp API
- **Fitur**:
  - Share order ke WhatsApp Group (admin broadcast)
  - Kirim private message detail order ke driver setelah claim disetujui
  - Resend order details ke driver
- **Konfigurasi**: WA_API_URL, WA_API_KEY, WA_SESSION_ID, WA_GROUP_ID

### 3.11 Payment Gateway (Xendit)

- **ID**: FR-PAY-01
- **Deskripsi**: Pemrosesan pembayaran booking customer
- **Fitur**:
  - Generate Xendit Invoice setelah booking
  - Redirect customer ke halaman pembayaran Xendit
  - Webhook untuk notifikasi status pembayaran
  - Update payment_status, payment_reference, payment_expiry
  - Harga termasuk base price + extras

### 3.12 Import Excel

- **ID**: FR-IMP-01
- **Deskripsi**: Bulk import orders dari file Excel
- **Fitur**:
  - Upload file .xlsx, .xls, .csv (max 10MB)
  - Drag & drop upload
  - Download template Excel
  - Smart lookup driver by code/nid & name
  - Smart lookup vehicle by plate/brand
  - Auto-generate booking code & order number
  - Batch insert (100 rows/batch) dalam transaksi atomik
  - Validasi: required fields, tipe data, format tanggal, format waktu
  - 6 format date parsing + time parsing
- **Flow**: Upload → Validasi → Parse → Lookup → Batch Insert → Redirect

### 3.13 Halaman Publik (Landing Page)

- **ID**: FR-PUB-01
- **Halaman**:
  - `/` — Welcome/Landing page dengan vehicle categories
  - `/about` — Tentang Siwride
  - `/services` — Layanan
  - `/vehicles` — Daftar kendaraan
  - `/vehicles/{slug}` — Detail kendaraan
  - `/area-coverage` — Area layanan
  - `/testimonials` — Testimonial
  - `/faq` — FAQ
  - `/contact` — Kontak
  - `/terms` — Syarat & ketentuan
  - `/privacy` — Kebijakan privasi

### 3.14 Customer Profile

- **ID**: FR-PROF-01
- **Fitur**:
  - Lihat & update profil
  - Riwayat pemesanan
  - Login/Register via customer auth
  - Guard: `customer`

### 3.15 Claim Order (Public)

- **ID**: FR-CLM-01
- **Deskripsi**: Halaman publik untuk claim order via booking code
- **Endpoint**: `GET/POST /c/{booking_code}`

---

## 4. Arsitektur Sistem

### 4.1 Stack Teknologi

```
┌─────────────────────────────────────────────────┐
│                Frontend (Svelte 5)              │
│         Inertia.js v3 + Tailwind CSS v4         │
├─────────────────────────────────────────────────┤
│              Laravel Framework v13               │
│     Fortify v1 | Sanctum v4 | Wayfinder v0      │
├─────────────────────────────────────────────────┤
│              Database (MySQL)                    │
│          Spatial (ST_Contains, etc.)            │
├─────────────────────────────────────────────────┤
│        External Services Integration            │
│  Google Maps | Xendit | WhatsApp API            │
└─────────────────────────────────────────────────┘
```

### 4.2 Alur Data Booking (Customer)

```
User (Browser)
    ↓
GET /booking → Pilih rute & kendaraan
    ↓
GET /booking/checkout → Multi-step form
    ↓
POST /orders → Simpan order
    ↓
Generate Xendit Invoice
    ↓
Redirect ke halaman pembayaran Xendit
    ↓
Webhook Xendit → Update payment_status
    ↓
Admin assign driver / Driver claim job
    ↓
Driver selesaikan order
```

### 4.3 Alur Data Mobile (Driver)

```
Flutter App
    ↓
POST /api/v1/auth/login → Dapat token
    ↓
GET /api/v1/jobs/shared → Lihat job pool
    ↓
POST /api/v1/jobs/{id}/take → Ambil job
    ↓
PATCH /api/v1/jobs/{id}/status → Update status perjalanan
    ↓
POST /api/v1/jobs/{id}/evidence → Upload foto bukti
    ↓
POST /api/v1/tracking/update → Update lokasi (tiap 30 detik)
```

---

## 5. Database

### 5.1 Entity Relationship Overview

```
users ──hasMany── orders (as driver)
users ──hasOne── driver_locations

drivers ──hasMany── vehicles
drivers ──hasMany── payrolls
drivers ──hasMany── orders

customers ──hasMany── orders

orders ──hasMany── order_evidences
orders ──hasMany── order_status_histories
orders ──hasMany── job_claims
orders ──belongsTo── vehicles
orders ──belongsTo── vehicle_categories

zones ──hasMany── zone_pricing_rules (pickup/dropoff)
zone_pricing_rules ──belongsTo── zones (pickup/dropoff)
```

### 5.2 Daftar Tabel (19 tabel)

| Tabel | Tipe | Fungsi |
|-------|------|--------|
| users | Master | User admin & driver (auth web) |
| drivers | Master | Data profil driver |
| customers | Master | Data customer |
| orders | Transaksi | Pesanan transportasi |
| order_evidences | Transaksi | Foto bukti perjalanan |
| order_status_histories | Log | Riwayat status order |
| job_claims | Transaksi | Klaim driver atas order |
| vehicles | Master | Data kendaraan |
| vehicle_categories | Master | Kategori kendaraan untuk booking |
| zones | Master | Zona geografis layanan |
| zone_pricing_rules | Master | Aturan harga antar zona |
| driver_locations | Real-time | Lokasi driver saat ini |
| payrolls | Transaksi | Data penggajian |
| settings | Konfigurasi | Pengaturan sistem |
| api_logs | Log | Log request API |
| personal_access_tokens | Auth | Token Sanctum |
| sessions | Session | Session pengguna |
| jobs/failed_jobs | Queue | Antrian job |
| cache/cache_locks | Cache | Data cache |

### 5.3 Tabel Orders — Field Detail

Kolom kunci:
- `booking_code` (varchar, unique) — Format: SW + 6 random chars
- `order_number` (varchar) — Format: ORD + YYYYMMDD + 4 random chars
- `customer_id` → FK customers
- `driver_id` → FK drivers
- `claimed_driver_id` → FK drivers (driver yang melakukan claim)
- `vehicle_id` → FK vehicles
- `vehicle_category_id` → FK vehicle_categories
- `date` (date), `time` (time)
- `pickup_address`, `dropoff_address` (text)
- `pickup_latitude`, `pickup_longitude`, `dropoff_latitude`, `dropoff_longitude` (decimal)
- `distance_km` (decimal)
- `passengers` (int)
- `price`, `parking_gas_fee` (decimal)
- `status` — pending, confirmed, otw, tiba, completed, cancelled
- `is_cash`, `is_shared`, `is_cancelled` (boolean)
- `payment_method`, `payment_reference`, `payment_status`, `payment_expiry`
- `extras` (JSON), `notes`, `flight_number`

---

## 6. API Endpoint

### 6.1 Web Routes (Inertia)

| Method | URI | Controller | Keterangan |
|--------|-----|------------|------------|
| GET | `/` | - | Welcome page |
| GET | `/dashboard` | DashboardController@index | Admin/Dashboard |
| GET | `/booking` | CustomerOrderController@index | Booking form |
| GET | `/booking/checkout` | CustomerOrderController@checkout | Checkout |
| POST | `/orders` | CustomerOrderController@store | Simpan order |
| GET | `/booking/estimate-price` | CustomerOrderController@estimatePrice | Estimasi harga |
| GET/POST | `/customer/login` | CustomerAuthController | Login customer |
| GET/POST | `/customer/register` | CustomerAuthController | Register customer |
| GET/POST | `/c/{booking_code}` | PublicClaimController | Claim publik |
| POST | `/locations/search` | LocationSearchController | Cari lokasi |

### 6.2 Admin Routes (Inertia + Auth)

| Method | URI | Controller |
|--------|-----|------------|
| Resource | `/admin/orders` | OrderController |
| Resource | `/admin/drivers` | DriverController |
| Resource | `/admin/vehicles` | VehicleController |
| Resource | `/admin/vehicle-categories` | VehicleCategoryController |
| Resource | `/admin/zones` | ZoneController |
| Resource | `/admin/zones/pricing` | ZonePricingRuleController |
| Resource | `/admin/users` | UserController |
| GET/POST | `/admin/settings/general` | SettingController |
| GET/POST | `/admin/settings/frontend` | FrontendSettingController |
| POST | `/admin/orders/import` | OrderController@import |
| POST | `/admin/orders/{order}/share` | OrderController@share |

### 6.3 Mobile API Routes (v1)

| Method | URI | Auth | Keterangan |
|--------|-----|------|------------|
| POST | `/api/v1/auth/login` | Public | Login |
| POST | `/api/v1/auth/register` | Public | Register driver |
| POST | `/api/v1/auth/logout` | Sanctum | Logout |
| GET | `/api/v1/user` | Sanctum | Profil user |
| GET | `/api/v1/jobs/shared` | Sanctum | Job pool |
| GET | `/api/v1/jobs/my` | Sanctum | Job saya |
| POST | `/api/v1/jobs/{id}/take` | Sanctum | Ambil job |
| POST | `/api/v1/jobs/{id}/claim` | Sanctum | Klaim job |
| PATCH | `/api/v1/jobs/{id}/status` | Sanctum | Update status |
| POST | `/api/v1/jobs/{id}/evidence` | Sanctum | Upload bukti |
| POST | `/api/v1/tracking/update` | Sanctum | Update lokasi |
| GET | `/api/v1/tracking/active` | Sanctum+Admin | Lihat lokasi driver |
| GET | `/api/v1/reports/salary` | Sanctum+Admin | Laporan gaji |
| GET/PUT | `/api/v1/settings` | Sanctum+Admin | Pengaturan |
| GET | `/api/v1/users/pending` | Sanctum+Admin | Driver pending |
| PUT | `/api/v1/users/{id}/status` | Sanctum+Admin | Approve/reject |

---

## 7. Antarmuka Pengguna

### 7.1 Halaman Publik (Customer-facing)
- **Landing Page**: Hero, vehicle categories, CTA
- **Booking Page**: Form rute (pickup/dropoff via Google Maps autocomplete), date/time picker, passenger counter, estimasi harga real-time, pilih kendaraan, track booking
- **Checkout**: Multi-step: Detail Transfer → Info Penumpang → Ekstra → Ringkasan → Pembayaran (Xendit redirect)
- **Vehicle Catalog**: Grid kendaraan dengan detail
- **Static Pages**: About, Services, FAQ, Contact, Terms, Privacy

### 7.2 Admin Panel
- **Sidebar**: Dashboard, Orders, Drivers, Vehicles, Zones, Settings
- **Dashboard**: KPI cards, ApexCharts (revenue trend, status distribution), recent orders table
- **Orders**: Table dengan filter/search, calendar view, create/edit modal, import Excel
- **Drivers**: Table, create/edit, sync user, upload dokumen
- **Zones**: Map-based polygon zone editor, pricing rules CRUD
- **Settings**: General settings (app name, WA config, etc.), Frontend settings (hero, branding, extras)

### 7.3 Mobile App Pages (via API)
- **Login/Register**: Email & password
- **Dashboard**: Job counter, revenue summary
- **Job Pool**: Available shared jobs (guest info hidden before 4PM)
- **Active Job**: Status stepper, map, evidence upload
- **Profile**: Online/offline toggle, vehicle info, settings

---

## 8. Integrasi Pihak Ketiga

| Layanan | Fungsi | Konfigurasi |
|---------|--------|-------------|
| Google Maps API | Geocoding, Distance Matrix, Autocomplete, Maps | `GOOGLE_MAPS_API_KEY` |
| Xendit | Payment gateway (invoice) | `services.xendit.secret_key` |
| WhatsApp API | Notifikasi order ke grup & private message | `WA_API_URL`, `WA_API_KEY`, `WA_SESSION_ID`, `WA_GROUP_ID` |
| ApexCharts | Dashboard charts (CDN) | Loaded via CDN di frontend |

---

## 9. Kebutuhan Non-Fungsional

### 9.1 Kinerja
- Waktu respon API rata-rata < 200ms (target: 500ms max)
- Import Excel: 100 order ~1-2 detik, 1000 order ~10-20 detik
- Batch insert 100 rows per transaksi

### 9.2 Keamanan
- Semua password di-hash (bcrypt)
- Autentikasi: Sanctum token (API), Session (Web)
- Two-Factor Authentication (Fortify)
- Rate limiting: login (5x/menit), password reset (6x/menit)
- SQL Injection: Dilindungi oleh Eloquent ORM
- File upload: MIME type check, extension whitelist, size limit 10MB
- Authorization: Admin middleware untuk admin-only routes

### 9.3 Ketersediaan
- Queue: Laravel queue dengan database driver untuk background jobs
- Session: Database session driver
- Cache: Database cache driver

### 9.4 Skalabilitas
- Pagination: 10 items per page
- Batch processing: 100 rows/batch untuk import
- Query optimization: Eager loading for relationships

---

## 10. Keamanan

### 10.1 Autentikasi
- Web: Fortify (session-based) + 2FA
- Customer: Custom guard (session-based)
- API: Sanctum token-based

### 10.2 Otorisasi
- Role-based: Admin, Driver, Customer
- AdminMiddleware untuk route admin
- Policy/Gate untuk operasi spesifik

### 10.3 Validasi
- Form Request validation
- Server-side validation di semua endpoint
- Client-side validation di frontend

### 10.4 File Upload
- Whitelist ekstensi: xlsx, xls, csv, jpg, png
- MIME type validation
- Size limit: 10MB (import), 2MB (photo)
- Storage: public disk (local)

---

## 11. Model Data (Eloquent)

### Models List (15 models)

| Model | Table | Key Relations |
|-------|-------|---------------|
| User | users | hasOne(Driver), hasMany(Order), hasOne(DriverLocation) |
| Driver | drivers | hasMany(Vehicle), hasMany(Payroll) |
| Customer | customers | hasMany(Order) |
| Order | orders | belongsTo(Customer, Driver, Vehicle, VehicleCategory), hasMany(OrderEvidence, JobClaim) |
| Vehicle | vehicles | belongsTo(Driver), hasMany(Order) |
| VehicleCategory | vehicle_categories | - |
| Zone | zones | hasMany(ZonePricingRule) |
| ZonePricingRule | zone_pricing_rules | belongsTo(Zone) |
| DriverLocation | driver_locations | - |
| OrderEvidence | order_evidences | belongsTo(Order) |
| OrderStatusHistory | order_status_histories | - |
| JobClaim | job_claims | belongsTo(Order) |
| Payroll | payrolls | belongsTo(Driver) |
| Setting | settings | - |
| ApiLog | api_logs | - |

---

## 12. Kebutuhan Masa Depan

### Potensi Pengembangan
1. **Real-time WebSocket** — Menggantikan long-polling untuk GPS tracking
2. **Export Excel** — Export orders ke Excel
3. **Notifikasi Push** — Firebase Cloud Messaging untuk mobile driver
4. **Multi-language** — Dukungan bahasa Inggris + Indonesia
5. **Driver Rating** — Sistem rating untuk driver
6. **Promo & Diskon** — Kode promo, diskon musiman
7. **Multiple Payment Methods** — Tambahan payment gateway selain Xendit
8. **Recaptcha** — Sudah ada konfigurasi RecaptchaService, siap diintegrasikan
