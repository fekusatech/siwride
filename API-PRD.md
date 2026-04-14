### **Product Requirements Document (PRD): API Laravel untuk Aplikasi D'Jali Team**

### 1. Executive Summary

*   **Problem Statement**: Aplikasi D'Jali Team saat ini bergantung pada backend Firebase yang aksesnya sudah tidak dimiliki. Hal ini menghalangi pengembangan lebih lanjut dan membuat aplikasi tidak dapat digunakan.
*   **Proposed Solution**: Membangun REST API menggunakan Laravel dan MySQL untuk sepenuhnya menggantikan fungsionalitas backend Firebase. API ini akan menangani otentikasi, manajemen data (user, kendaraan, tugas, dll.), dan semua logika bisnis yang diperlukan oleh aplikasi Flutter.
*   **Success Criteria**:
    *   100% fungsionalitas aplikasi Flutter yang sebelumnya bergantung pada Firebase berhasil dimigrasikan ke API Laravel.
    *   API mampu menangani semua *request* dari aplikasi dengan *response time* rata-rata di bawah 500ms.
    *   Tidak ada lagi *crash* atau *error* pada aplikasi yang disebabkan oleh koneksi ke Firebase.
    *   Proses login dan registrasi melalui API berjalan lancar dan aman.

### 2. User Experience & Functionality

*   **User Personas**:
    *   **Admin**: Pengguna yang mengelola data master (driver, kendaraan), membuat tugas, dan memonitor aktivitas melalui aplikasi.
    *   **Driver**: Pengguna yang menerima tugas, melakukan check-in/check-out, mengunggah bukti, dan melihat riwayat tugas melalui aplikasi.
*   **User Stories & Acceptance Criteria**:

    *   **Story 1: Otentikasi Pengguna**
        *   `As an` Admin/Driver, `I want to` register and log in using my email and password, `so that` I can access the application securely.
        *   **Acceptance Criteria**:
            *   Endpoint `POST /api/register` menerima `name`, `email`, `password`, `phone`, `role` dan membuat user baru dengan status default `pending`.
            *   Endpoint `POST /api/login` menerima `email` dan `password`, lalu mengembalikan token otentikasi (Sanctum) jika berhasil.
            *   Endpoint `POST /api/logout` (membutuhkan otentikasi) mencabut token yang sedang digunakan.
            *   Password disimpan menggunakan *hashing* yang aman (standar Laravel).

    *   **Story 2: Manajemen Tugas (Admin)**
        *   `As an` Admin, `I want to` create, read, update, and delete (CRUD) tasks, `so that` I can assign work to drivers.
        *   **Acceptance Criteria**:
            *   Endpoint `GET /api/tasks` mengembalikan daftar semua tugas.
            *   Endpoint `POST /api/tasks` dapat membuat tugas baru dengan semua detail yang relevan (nama, deskripsi, lokasi, driver_id, vehicle_id).
            *   Endpoint `GET /api/tasks/{id}` menampilkan detail satu tugas.
            *   Endpoint `PUT /api/tasks/{id}` memperbarui detail tugas.
            *   Endpoint `DELETE /api/tasks/{id}` menghapus tugas.
            *   Semua endpoint ini harus dilindungi dan hanya bisa diakses oleh Admin.

    *   **Story 3: Eksekusi Tugas (Driver)**
        *   `As a` Driver, `I want to` see my assigned tasks and update their status (check-in/check-out), `so that` I can report my work progress.
        *   **Acceptance Criteria**:
            *   Endpoint `GET /api/mytasks` mengembalikan daftar tugas yang di-assign ke driver yang sedang login.
            *   Endpoint `POST /api/tasks/{id}/checkin` mencatat waktu dan lokasi check-in.
            *   Endpoint `POST /api/tasks/{id}/checkout` mencatat waktu dan lokasi check-out, serta menerima unggahan file bukti.
            *   Endpoint untuk mengunggah file bukti harus terhubung dengan *storage* (bisa local storage atau S3-compatible).
            *   Semua endpoint ini harus dilindungi dan hanya bisa diakses oleh Driver.

*   **Non-Goals**:
    *   Membangun panel admin berbasis web (fokus hanya pada API untuk mobile).
    *   Menambahkan fitur baru yang tidak ada di aplikasi versi Firebase.
    *   Implementasi *real-time updates* menggunakan WebSockets (untuk fase awal ini).

### 3. Technical Specifications

*   **Architecture Overview**:
    *   **Framework**: Laravel (versi terbaru yang stabil).
    *   **Database**: MySQL.
    *   **Authentication**: Laravel Sanctum dengan API Token.
    *   **Server**: PHP-FPM dengan Nginx/Apache.
    *   Aplikasi Flutter akan berkomunikasi dengan Laravel API melalui request HTTP (RESTful).
*   **Database Schema**:
    *   Struktur tabel, kolom, dan relasi harus mengacu pada file `SUPABASE_TABLES.md`. Migrasi Laravel harus dibuat untuk setiap tabel: `users`, `vehicles`, `tasks`, `locations`, `task_checkins`, `task_proofs`, `settings`.
*   **API Endpoint Summary (MVP)**:
    *   `POST /api/register`
    *   `POST /api/login`
    *   `POST /api/logout`
    *   `GET /api/user` (mendapatkan data user yang login)
    *   **Admin Routes:**
        *   `GET, POST /api/tasks`
        *   `GET, PUT, DELETE /api/tasks/{id}`
        *   `GET, POST /api/users` (manajemen user)
        *   `PUT /api/users/{id}/status` (approve/reject/disable user)
        *   CRUD untuk `vehicles`.
    *   **Driver Routes:**
        *   `GET /api/mytasks`
        *   `POST /api/tasks/{id}/checkin`
        *   `POST /api/tasks/{id}/checkout` (dengan file upload)
*   **Security & Privacy**:
    *   Semua endpoint (kecuali login/register) harus dilindungi oleh middleware `auth:sanctum`.
    *   Otorisasi berbasis *role* (Admin vs Driver) harus diimplementasikan menggunakan Laravel Gates atau Policies.
    *   Validasi input harus diterapkan di semua request untuk mencegah SQL Injection dan XSS.
    *   Gunakan HTTPS (SSL) di lingkungan production.

### 4. Risks & Roadmap

*   **Phased Rollout**:
    1.  **Fase 1 (Backend Development)**: Implementasi semua migrasi database, model, dan API endpoint sesuai spesifikasi di atas. Lakukan testing menggunakan API client seperti Postman.
    2.  **Fase 2 (Mobile Integration)**: Modifikasi aplikasi Flutter untuk mengganti semua panggilan ke Firebase dengan panggilan ke API Laravel yang baru.
    3.  **Fase 3 (Testing & Deployment)**: Lakukan pengujian *end-to-end* secara menyeluruh, diikuti dengan proses deployment API ke server production.
*   **Technical Risks**:
    *   **File Uploads**: Perlu strategi yang jelas untuk menangani penyimpanan file (local, S3, dll.) dan bagaimana URL file akan diakses oleh aplikasi.
    *   **Data Migration**: Jika ada data produksi di Firebase yang perlu diselamatkan, proses migrasi data harus direncanakan dengan hati-hati (saat ini diasumsikan tidak perlu).
    *   **Environment Configuration**: Konfigurasi koneksi database dan variabel lingkungan lainnya harus dikelola dengan aman (menggunakan file `.env`).
