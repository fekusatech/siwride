# Product Requirements Document (PRD): Migrasi Backend D'Jali Team (Firebase ke REST API & MySQL)

## 1. Executive Summary

*   **Problem Statement**: Aplikasi D'Jali Team saat ini menggunakan Firebase (Authentication & Firestore). Seiring berjalannya waktu dan bertambahnya pengguna, diperlukan transisi ke backend kustom (RESTful API) dengan database relasional (MySQL) untuk kontrol data yang lebih baik, skalabilitas, keamanan tingkat lanjut (terutama untuk transaksi ambil job rebutan), dan efisiensi biaya.
*   **Proposed Solution**: Mengembangkan backend kustom (menggunakan Node.js, PHP/Laravel, Go, atau Python) dengan database MySQL. Backend ini akan menyediakan sekumpulan RESTful API yang merespons dalam format JSON untuk menggantikan seluruh fungsionalitas Firebase. Aplikasi Flutter di sisi klien akan diubah pada layer _service_ (dari SDK Firebase menjadi HTTP Client seperti `dio` atau `http`) tanpa mengubah User Interface (UI) atau User Experience (UX). Supabase Storage tetap dapat digunakan untuk penyimpanan file foto bukti, atau bisa dimigrasi ke backend lokal jika diinginkan.
*   **Success Criteria**:
    *   100% fitur eksisting (Auth, Job Management, Real-time GPS Tracking, Salary Report) berjalan mulus menggunakan API baru.
    *   Waktu respons API (latency) rata-rata di bawah 200ms.
    *   Tidak ada kehilangan data selama proses migrasi (jika ada data lama yang dipindah).
    *   *Race condition* pada fitur "Share Job / Job Rebutan" terselesaikan sepenuhnya menggunakan MySQL Transaction (`SELECT ... FOR UPDATE`).

---

## 2. Struktur Routing Aplikasi (Flutter Screens)

Berikut adalah daftar routing atau halaman (screens) yang ada di aplikasi Flutter saat ini. Struktur ini menjadi acuan untuk menentukan endpoint API apa saja yang akan dipanggil di setiap layarnya.

### 🔒 Authentication Flow
*   `/login`: Halaman masuk pengguna (Mendukung Email/Password dan integrasi Google Sign-In via API).
*   `/register`: Halaman pendaftaran khusus untuk Driver baru. Status awal driver setelah mendaftar adalah *Pending*.
*   `/forgot-password`: Halaman untuk meminta reset password.

### 👨‍💼 Admin Flow (Role: `admin`)
*   `/admin/dashboard`: Ringkasan statistik (total job, total driver, job hari ini) dan tombol aksi cepat.
*   `/admin/drivers/pending`: Halaman ulasan (Review) untuk menyetujui (Approve) atau menolak (Reject) pendaftaran driver baru. Terdapat tombol untuk chat WhatsApp ke driver.
*   `/admin/drivers/list`: Menampilkan daftar semua driver dengan status aktif/approved beserta tombol chat.
*   `/admin/jobs/create`: Form pembuatan Job manual. Mencakup toggle pembayaran (Cash/Transfer).
*   `/admin/jobs/import`: Halaman untuk mengunggah dan mengimpor file Excel yang berisi daftar job (Bulk insert).
*   `/admin/jobs/list`: Halaman manajemen job. Menampilkan seluruh job, filter status, pembatalan (Cancel) job, dan penugasan (Assign) manual ke driver.
*   `/admin/jobs/detail`: Menampilkan informasi lengkap sebuah job tertentu.
*   `/admin/tracking`: Peta interaktif (Google Maps) yang menampilkan lokasi seluruh driver yang sedang bertugas secara real-time.
*   `/admin/reports/salary`: Halaman rekapitulasi gaji driver. (Periode 1: Tgl 1-15 cair tgl 25 | Periode 2: Tgl 16-31 cair tgl 10 bulan berikutnya).
*   `/admin/settings`: Konfigurasi sistem global (misal: mengatur limit maksimal job yang bisa diambil seorang driver per hari).

### 🚗 Driver Flow (Role: `driver`)
*   `/driver/dashboard`: Ringkasan kerja driver (Job to do, Job done, Total Revenue bulan berjalan).
*   `/driver/jobs/my-rides`: Daftar job yang sudah ditugaskan/diambil oleh driver tersebut. Driver mengupdate status di sini (Pending → OTW → Tiba → Selesai).
*   `/driver/jobs/shared`: *Job Pool*. Daftar job yang di-share oleh admin dan bisa diambil secara "rebutan" oleh driver.
*   `/driver/jobs/detail`: Detail pekerjaan. *Constraint bisnis*: Informasi detail tamu (nama, no. telp) disembunyikan dan baru muncul pada jam 16:00 (4 sore) di hari H. Wajib upload foto saat "Tiba" dan "Berangkat".
*   `/driver/profile`: Informasi profil driver dan pengaturan preferensi (Notifikasi, Bahasa).
*   `/driver/chat`: Pintasan untuk membuka WhatsApp ke nomor Admin.

---

## 3. Spesifikasi Teknis RESTful API (Pengganti Firebase)

Semua endpoint di bawah ini mengasumsikan penggunaan arsitektur REST. Format data adalah `application/json`. Endpoint yang membutuhkan otentikasi wajib menyertakan HTTP Header: `Authorization: Bearer <JWT_TOKEN>`.

### A. Authentication API

#### 1. Login
*   **Endpoint**: `POST /api/v1/auth/login`
*   **Deskripsi**: Mengautentikasi user dan mengembalikan JWT Token.
*   **Request Body**:
    ```json
    {
      "email": "driver@test.com",
      "password": "password123"
    }
    ```
*   **Expected Response (200 OK)**:
    ```json
    {
      "status": "success",
      "data": {
        "token": "eyJhbGciOiJIUzI1NiIsInR...",
        "user": {
          "id": 1,
          "name": "Budi",
          "email": "driver@test.com",
          "role": "driver",
          "status": "approved"
        }
      }
    }
    ```

#### 2. Register Driver
*   **Endpoint**: `POST /api/v1/auth/register`
*   **Deskripsi**: Pendaftaran akun driver baru. Role otomatis `driver`, status awal `pending`.
*   **Request Body**:
    ```json
    {
      "name": "Joko Driver",
      "email": "joko@test.com",
      "password": "SecurePassword123",
      "phone": "08123456789"
    }
    ```
*   **Expected Response (201 Created)**:
    ```json
    {
      "status": "success",
      "message": "Registrasi berhasil. Akun Anda sedang menunggu persetujuan admin."
    }
    ```

### B. User / Driver Management API

#### 1. Get Pending Drivers (Admin Only)
*   **Endpoint**: `GET /api/v1/users/pending`
*   **Expected Response (200 OK)**:
    ```json
    {
      "status": "success",
      "data": [
        {
          "id": 2,
          "name": "Joko Driver",
          "email": "joko@test.com",
          "phone": "08123456789",
          "created_at": "2023-10-24T10:00:00Z"
        }
      ]
    }
    ```

#### 2. Update Driver Status (Admin Only)
*   **Endpoint**: `PUT /api/v1/users/:id/status`
*   **Request Body**:
    ```json
    {
      "status": "approved" // atau "rejected"
    }
    ```
*   **Expected Response (200 OK)**:
    ```json
    {
      "status": "success",
      "message": "Status driver berhasil diperbarui menjadi approved."
    }
    ```

### C. Job Management API

*Penting: Seluruh proses yang mengubah status Job (terutama Take Job) harus dibungkus dalam Database Transaction untuk mencegah inkonsistensi data.*

#### 1. Create Job (Admin Only)
*   **Endpoint**: `POST /api/v1/jobs`
*   **Request Body**:
    ```json
    {
      "guest_name": "Mr. Smith",
      "guest_phone": "+628111222333",
      "pickup_location": "Bandara Ngurah Rai",
      "dropoff_location": "Hotel Kuta",
      "date": "2023-10-25",
      "time": "14:00",
      "is_cash": false,
      "fee": 150000,
      "driver_id": null, // Jika null, job otomatis masuk ke pool (status: shared)
      "status": "shared"
    }
    ```
*   **Expected Response (201 Created)**: (Mengembalikan data job yang baru dibuat)

#### 2. Get Shared Jobs / Pool (Driver)
*   **Endpoint**: `GET /api/v1/jobs/shared`
*   **Deskripsi**: Mengambil daftar job yang belum ada drivernya (`driver_id` is null).
*   **Expected Response (200 OK)**:
    ```json
    {
      "status": "success",
      "data": [
        {
          "id": 101,
          "pickup_location": "Bandara Ngurah Rai",
          "dropoff_location": "Hotel Kuta",
          "date": "2023-10-25",
          "time": "14:00",
          "fee": 150000,
          "guest_info_hidden": true // Logic disembunyikan sebelum jam 4 sore bisa di-handle di Backend atau Frontend
        }
      ]
    }
    ```

#### 3. Take Job from Pool (Driver)
*   **Endpoint**: `POST /api/v1/jobs/:id/take`
*   **Deskripsi**: Endpoint krusial. Backend harus mengecek: 1. Apakah job masih tersedia? 2. Apakah driver sudah melewati limit harian? Gunakan `SELECT * FROM jobs WHERE id = ? FOR UPDATE` di MySQL.
*   **Expected Response (200 OK)**:
    ```json
    {
      "status": "success",
      "message": "Berhasil mengambil pekerjaan."
    }
    ```
*   **Expected Error Response (400 Bad Request)**:
    ```json
    {
      "status": "error",
      "message": "Maaf, pekerjaan ini sudah diambil oleh driver lain atau limit harian Anda telah tercapai."
    }
    ```

#### 4. Update Job Status & Upload Evidence (Driver)
*   **Endpoint**: `PUT /api/v1/jobs/:id/status`
*   **Deskripsi**: Digunakan driver untuk mengupdate progress perjalanan.
*   **Request Body**:
    ```json
    {
      "status": "tiba", // Enum: pending, otw, tiba, selesai
      "evidence_photo_url": "https://supabase.co/storage/.../foto1.jpg", // Wajib diisi saat status 'tiba' & 'otw'
      "timestamp": "2023-10-25T14:15:00Z"
    }
    ```

### D. GPS & Location Tracking API

Karena aplikasi beralih dari Firestore (yang memiliki realtime listener bawaan) ke REST API biasa, klien Flutter perlu melakukan pemanggilan berkala (Long Polling) atau backend perlu menyediakan WebSocket server (misal Socket.io).

#### 1. Update Location (Driver - Terpanggil setiap 30 detik)
*   **Endpoint**: `POST /api/v1/tracking/update`
*   **Request Body**:
    ```json
    {
      "latitude": -8.748112,
      "longitude": 115.167156,
      "timestamp": "2023-10-25T14:00:00Z"
    }
    ```
*   **Expected Response (200 OK)**: Kosongkan isi response body untuk menghemat penggunaan bandwidth data seluler driver.

#### 2. Get Active Locations (Admin)
*   **Endpoint**: `GET /api/v1/tracking/active`
*   **Expected Response (200 OK)**:
    ```json
    {
      "status": "success",
      "data": [
        {
          "driver_id": 1,
          "driver_name": "Budi",
          "latitude": -8.748112,
          "longitude": 115.167156,
          "last_updated": "2023-10-25T14:00:00Z"
        }
      ]
    }
    ```

### E. Salary Report API

Alih-alih klien Flutter yang menghitung gaji satu per satu, kalkulasi dipindahkan ke database MySQL menggunakan fungsi agregasi (`SUM`, `GROUP BY`).

#### 1. Get Salary Report (Admin)
*   **Endpoint**: `GET /api/v1/reports/salary`
*   **Query Parameters**: `?month=10&year=2023&period=1`
    *   *Period 1*: Tanggal 1 - 15 (Dibayar tgl 25)
    *   *Period 2*: Tanggal 16 - 31 (Dibayar tgl 10)
*   **Aturan Bisnis**: Job yang statusnya di-cancel ATAU transaksinya *Cash* (tunai langsung ke driver) **tidak dihitung** dalam total gaji.
*   **Expected Response (200 OK)**:
    ```json
    {
      "status": "success",
      "data": {
        "report_period": "2023-10-01 to 2023-10-15",
        "payout_date": "2023-10-25",
        "drivers_recap": [
          {
            "driver_id": 1,
            "driver_name": "Budi",
            "total_jobs_completed": 12,
            "total_salary": 1800000
          },
          {
            "driver_id": 2,
            "driver_name": "Joko",
            "total_jobs_completed": 5,
            "total_salary": 750000
          }
        ]
      }
    }
    ```

---

## 4. Desain Skema Database Relasional (MySQL)

Di bawah ini adalah usulan relasi tabel yang direkomendasikan untuk menampung data eksisting.

**Table `users`**
*   `id` (INT, Primary Key, Auto Increment)
*   `name` (VARCHAR)
*   `email` (VARCHAR, Unique)
*   `password_hash` (VARCHAR)
*   `phone` (VARCHAR)
*   `role` (ENUM: 'admin', 'driver')
*   `status` (ENUM: 'pending', 'approved', 'rejected') - *Default: pending*
*   `created_at` (TIMESTAMP)

**Table `jobs`**
*   `id` (INT, Primary Key, Auto Increment)
*   `guest_name` (VARCHAR)
*   `guest_phone` (VARCHAR)
*   `pickup_location` (TEXT)
*   `dropoff_location` (TEXT)
*   `date` (DATE)
*   `time` (TIME)
*   `fee` (DECIMAL)
*   `is_cash` (BOOLEAN) - *Default: false*
*   `status` (ENUM: 'shared', 'pending', 'otw', 'tiba', 'selesai', 'cancelled')
*   `driver_id` (INT, Foreign Key references `users.id`, Nullable)
*   `created_at` (TIMESTAMP)

**Table `job_evidences`** *(Untuk menyimpan foto bukti perjalanan)*
*   `id` (INT, Primary Key)
*   `job_id` (INT, Foreign Key references `jobs.id`)
*   `evidence_type` (ENUM: 'tiba_di_lokasi', 'berangkat_dengan_tamu')
*   `photo_url` (VARCHAR)
*   `created_at` (TIMESTAMP)

**Table `driver_locations`** *(Pertimbangkan menggunakan Redis jika update terlalu masif, namun MySQL Memory table juga cukup untuk tahap awal)*
*   `driver_id` (INT, Primary Key, Foreign Key references `users.id`)
*   `latitude` (DECIMAL(10, 8))
*   `longitude` (DECIMAL(11, 8))
*   `updated_at` (TIMESTAMP)

**Table `settings`**
*   `id` (INT, Primary Key)
*   `setting_key` (VARCHAR, Unique) - *e.g., 'daily_job_limit'*
*   `setting_value` (VARCHAR)

---

## 5. Rencana Migrasi (Roadmap)

1.  **Fase 1: Backend Development**
    *   Setup arsitektur project backend (Node.js/PHP/Go).
    *   Buat skema tabel MySQL berdasarkan rancangan di atas.
    *   Implementasikan endpoint RESTful API dan pengamanan menggunakan JWT.
2.  **Fase 2: Refactoring Flutter App**
    *   Hapus dependensi Firebase SDK (`firebase_core`, `firebase_auth`, `cloud_firestore`) dari `pubspec.yaml`.
    *   Tambahkan dependensi HTTP Client (seperti `dio` atau `http`) dan penyimpanan token lokal (`flutter_secure_storage` atau `shared_preferences`).
    *   Tulis ulang semua file di dalam `lib/services/` (`auth_service.dart`, `firestore_service.dart`, dll) agar memanggil REST API alih-alih fungsi Firebase.
3.  **Fase 3: Pengujian (Testing)**
    *   Uji coba skenario Auth (Login/Register).
    *   Uji coba krusial: *Stress testing* saat beberapa driver mengambil job dari pool secara bersamaan untuk memastikan *Race Condition* berhasil ditangani oleh sistem Transaction MySQL.
4.  **Fase 4: Deployment**
    *   Deploy backend API ke VPS atau Cloud Provider (misal: AWS, DigitalOcean, VPS Hostinger).
    *   Setup SSL (HTTPS) agar komunikasi dari aplikasi Flutter aman.
    *   Rilis pembaruan aplikasi Flutter (APK/AAB/IPA) ke pengguna.
