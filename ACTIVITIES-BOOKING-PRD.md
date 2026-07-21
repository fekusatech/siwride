# PRD: Booking Aktivitas & Experience ("What We Offer")

**Status:** Draft
**Dibuat:** 2026-07-17
**Terkait:** Section "What We Offer" di homepage (`resources/js/pages/Welcome.svelte`)

## 1. Latar Belakang & Masalah

Homepage Siwride punya section **"What We Offer" / "Our Premium Services"** yang menampilkan kartu-kartu layanan (di-render dari tabel `services`, model `App\Models\Service`). Tiga di antaranya adalah aktivitas wisata:

- **ATV Ride**
- **Rafting**
- **Jeep Sunrise Mountain Batur**

Setiap kartu punya tombol **"Book"** yang link ke `service.href` (kolom string bebas di tabel `services`, diisi manual lewat Admin > Services). Untuk layanan seperti Ride Sharing atau Airport Transfer, `href` mengarah ke halaman booking yang memang sudah ada. Tapi untuk ketiga aktivitas ini, `href`-nya mengarah ke URL yang **tidak punya route/controller/halaman apapun di baliknya** — makanya klik "Book" hasilnya 404.

Singkatnya: **kartu-kartu ini murni konten marketing (CMS card), belum ada produk yang bisa benar-benar dipesan di baliknya.**

### Yang sudah ada vs yang belum

| Komponen | Status |
|---|---|
| Kartu marketing di homepage (`services` table) | ✅ Ada |
| Halaman detail aktivitas per item | ❌ Belum ada |
| Model data untuk harga, jadwal, kapasitas per aktivitas | ❌ Belum ada |
| Alur booking customer (pilih tanggal/jumlah orang → checkout → bayar) | ❌ Belum ada |
| Pembayaran (Xendit) untuk aktivitas | ❌ Belum ada — infrastruktur Xendit sudah ada dan reusable (lihat §7) |
| Admin CRUD kelola aktivitas & pesanan | ❌ Belum ada |

**Catatan penting:** ada sisa kode dari fitur lama bernama "Tour Package" (`CustomerOrderController::tourIndex()`, halaman `customer/booking-tour.svelte`) yang statusnya **stub kosong** (`'tours' => []`, tidak ada model/tabel di baliknya). Itu konsepnya beda dari aktivitas ini — Tour Package itu carter mobil + supir sepanjang hari dengan itinerary custom, sedangkan ATV/Rafting/Jeep Sunrise itu **experience tetap dengan harga per-orang dan jadwal keberangkatan tetap**, mirip produk di Klook/GetYourGuide. Jangan digabung — PRD ini scope-nya khusus aktivitas per-orang, bukan revive Tour Package.

## 2. Tujuan

1. Tombol "Book" di ketiga kartu (dan aktivitas baru ke depannya) mengarah ke halaman yang benar-benar bisa dipakai untuk booking, bukan 404.
2. Admin bisa menambah/mengedit aktivitas baru dari panel admin — termasuk harga per orang, durasi, kapasitas, jadwal, foto, deskripsi — tanpa perlu bantuan developer tiap kali ada aktivitas baru.
3. Customer bisa pilih tanggal & jumlah peserta, lihat harga total, checkout, dan bayar (transfer/QRIS via Xendit) — hasil akhirnya sebuah booking yang bisa dilihat/dikelola admin seperti order lainnya.
4. Admin dapat notifikasi (WhatsApp, pola yang sudah ada) saat ada booking aktivitas baru.

## 3. Di Luar Cakupan (Non-goals)

- **Bukan** revive fitur "Tour Package" (carter mobil + itinerary custom) — itu PRD terpisah kalau memang mau dilanjutkan.
- Tidak membangun sistem alokasi kapasitas real-time yang kompleks (mis. lock kursi saat checkout, race-condition handling ketat). Cukup validasi kapasitas sederhana saat submit.
- Tidak membangun multi-currency atau multi-bahasa untuk halaman aktivitas.
- Tidak membangun review/rating system untuk aktivitas (bisa jadi fase berikutnya).
- Tidak mengubah alur Ride Sharing / Airport Transfer / Hourly yang sudah berjalan.

## 4. Model Data Baru

### 4.1 Tabel `activities`

Mengikuti pola yang sudah ada di `vehicle_categories` dan `tours` (migration lama yang sempat dibuat tapi belum tentu jalan), field yang dibutuhkan:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | |
| `slug` | string, unique | untuk URL detail (`/activities/atv-ride`) |
| `title` | string | "ATV Ride" |
| `subtitle` | string, nullable | tagline pendek |
| `description` | text | deskripsi panjang (bisa isi konten yang user kasih di brief ini) |
| `image` | string, nullable | pola sama seperti `Service::getImageUrlAttribute()` |
| `gallery` | json, nullable | foto tambahan untuk halaman detail |
| `price_per_pax` | decimal(10,2) | harga per orang |
| `min_pax` | integer, default 1 | minimum peserta per booking |
| `max_pax` | integer, nullable | kapasitas maksimum per jadwal keberangkatan (nullable = tidak dibatasi) |
| `duration_label` | string, nullable | "3-4 jam", "Half day", dst — teks bebas, bukan angka jam presisi |
| `meeting_point` | string, nullable | lokasi kumpul/penjemputan |
| `includes` | json, nullable | list "termasuk" (helm, life jacket, makan siang, dst) |
| `excludes` | json, nullable | list "tidak termasuk" |
| `highlights` | json, nullable | poin-poin jual singkat, sama seperti kolom `highlights` yang sudah ada di `services` |
| `is_active` | boolean, default true | |
| `sort_order` | integer, default 0 | |
| `created_at` / `updated_at` | | |

### 4.2 Jadwal keberangkatan — `activity_schedules` (opsional, lihat §8 keputusan terbuka)

Kalau aktivitas punya jadwal keberangkatan tetap (mis. Jeep Sunrise cuma berangkat jam 4 pagi), butuh tabel kecil terpisah:

| Kolom | Tipe |
|---|---|
| `id` | bigint |
| `activity_id` | FK → activities |
| `departure_time` | time (mis. "04:00") |
| `days_of_week` | json, nullable (null = setiap hari) |
| `is_active` | boolean |

Kalau di fase awal semua aktivitas cuma butuh 1 opsional jadwal fleksibel (customer pilih tanggal, waktu ditentukan otomatis/manual oleh admin lewat WhatsApp), tabel ini **bisa di-skip dulu** dan `duration_label` + catatan bebas di deskripsi sudah cukup untuk MVP.

### 4.3 Booking — reuse `Order` atau tabel baru `activity_bookings`?

**Rekomendasi: tabel baru `activity_bookings`**, bukan menambah kolom lagi ke `orders`. Alasannya konsisten dengan keputusan yang sama saat membangun fitur Transaction/QRIS kemarin — model `Order` sudah sarat kolom spesifik ride-sharing (`pickup_latitude/longitude`, `dropoff_*`, `vehicle_category_id`, `driver_id`, dst) yang **tidak relevan** untuk booking aktivitas (tidak ada pickup/dropoff koordinat, tidak ada driver assignment, tidak ada zona). Menumpangkan aktivitas ke `Order` akan membuat model itu makin penuh kolom nullable yang cuma dipakai sebagian jenis order — pola yang sama seperti kenapa fitur Transaction kemarin dibuatkan model `Transaction` sendiri, bukan reuse `Order`.

| Kolom `activity_bookings` | Keterangan |
|---|---|
| `id`, `booking_code` (unique, format mis. `ACT-XXXXXXXXXX`) | |
| `activity_id` | FK |
| `customer_id` | FK ke `customers` (reuse model `Customer` yang sudah ada) |
| `booking_date` | tanggal aktivitas |
| `pax` | jumlah peserta |
| `price_per_pax`, `total_price` | snapshot harga saat booking (jangan hanya baca dari `activities.price_per_pax` — harga bisa berubah setelahnya) |
| `customer_name`, `customer_phone`, `customer_email` | snapshot, pola sama seperti `Order` |
| `notes` | catatan customer |
| `status` | `pending`, `confirmed`, `cancelled`, `completed` |
| `payment_status`, `payment_method`, `payment_reference` | pola sama persis seperti `Order` |
| `created_at`, `updated_at` | |

## 5. Alur Customer

1. Homepage → klik "Book" di kartu aktivitas → masuk ke halaman detail `/activities/{slug}` (bukan lagi 404).
2. Halaman detail menampilkan: galeri foto, deskripsi lengkap, harga per orang, includes/excludes, form pilih **tanggal** + **jumlah peserta**.
3. Harga total dihitung otomatis (`price_per_pax × pax`), mirip pola live-price di `booking.svelte` untuk ride sharing.
4. Klik "Lanjut ke Pembayaran" → checkout: isi nama, email, no. HP (kalau belum login), ringkasan pesanan.
5. Bayar via Xendit — **reuse infrastruktur yang sudah ada**, bukan bangun baru:
   - Opsi A: Invoice API (pola yang sama seperti `RideSharingController::generateXenditPayment()` / `CustomerOrderController::generateXenditPayment()`) — customer diarahkan ke halaman checkout Xendit yang support banyak metode (VA, e-wallet, QRIS, kartu).
   - Opsi B: QRIS langsung (pola yang sama seperti fitur Transaction admin kemarin, `PaymentRequestApi`) kalau mau tampilkan QR langsung di halaman tanpa redirect.
   - **Rekomendasi: Opsi A (Invoice API)** — lebih konsisten dengan alur booking customer yang sudah ada (ride sharing, airport transfer), dan customer sudah familiar dengan tampilan checkout Xendit itu.
6. Setelah bayar, webhook Xendit (`WebhookController`) update `activity_bookings.payment_status` — perlu tambah `resolveActivityBooking()` + `markActivityBookingPaid/Failed/Expired()`, pola identik dengan `resolveTransaction()`/`markTransactionPaid()` yang baru dibuat untuk fitur Transaction.
7. Customer dapat halaman konfirmasi + (opsional) email/WA konfirmasi.

## 6. Alur Admin

1. **Admin > Activities** (menu baru di sidebar, sejajar dengan "Services") — CRUD aktivitas: tambah/edit judul, harga, foto, kapasitas, includes/excludes. Pola UI sama persis dengan `Admin/Services/Index.svelte` + `Create.svelte` yang sudah ada (bisa dicontek langsung).
2. **Admin > Activity Bookings** — list semua booking, filter by status/tanggal, lihat detail pemesan, update status manual kalau perlu (mis. tandai "completed" setelah aktivitas selesai).
3. Notifikasi WhatsApp ke grup admin saat ada booking baru & saat pembayaran masuk — reuse `WhatsAppService` yang sudah dipakai untuk notifikasi order ride-sharing.
4. Setelah tabel `activities` terisi, kolom `href` di `services` untuk ATV/Rafting/Jeep Sunrise diupdate manual dari `/booking-lama-yang-404` ke `/activities/atv-ride` dst — **tidak perlu migrasi data otomatis**, tinggal edit 3 baris lewat Admin > Services yang sudah ada.

## 7. Integrasi Teknis yang Bisa Di-reuse Langsung

Supaya scope implementasi nanti tidak melebar, ini daftar yang **sudah ada dan tinggal dipakai**, tidak perlu dibangun ulang:

- **Xendit Invoice API** — pola di `RideSharingController`/`CustomerOrderController::generateXenditPayment()`.
- **Xendit QRIS (Payment Request API)** — pola di `Admin\TransactionController` (kalau opsi B dipilih).
- **`Setting::getValue('xendit_secret_key')`** — key Xendit sudah dikonfigurasi lewat Admin > Settings > General, tidak perlu env var baru.
- **`Customer` model** — reuse untuk data pemesan, jangan bikin tabel customer baru.
- **`WhatsAppService`** — notifikasi grup admin.
- **Pola upload gambar** — `Service::store()`/`Storage::disk('public')`, sama persis untuk foto aktivitas.
- **Pola generate kode unik** — `generateUniqueBookingCode()` (do-while + exists check) sudah ada 3 implementasi serupa di codebase, tinggal contek.

## 8. Keputusan Terbuka (perlu diputuskan sebelum development)

1. **Jadwal keberangkatan**: fixed per aktivitas (butuh tabel `activity_schedules`) atau cukup "pilih tanggal, waktu dikonfirmasi manual via WA" untuk MVP?
2. **Metode pembayaran**: Invoice API (redirect) atau QRIS langsung di halaman (seperti Admin Transactions)? Atau kasih pilihan keduanya ke customer?
3. **Kapasitas**: perlu validasi keras "kalau kapasitas hari itu sudah penuh, tidak bisa booking lagi" di MVP, atau cukup informasi manual (admin cancel/reschedule manual kalau bentrok)?
4. **Kebijakan pembatalan/refund**: ada aturan refund H-berapa hari? (belum ada infrastruktur refund otomatis di manapun di codebase saat ini — kalau dibutuhkan, itu scope tambahan).
5. **Struktur URL**: `/activities/{slug}` atau `/experiences/{slug}` atau ikut pola lain? (memengaruhi penamaan model/tabel juga — PRD ini pakai "activities" sebagai working name).

## 9. Kriteria Sukses

- Klik "Book" pada ketiga kartu existing (ATV Ride, Rafting, Jeep Sunrise) tidak lagi 404.
- Admin bisa menambah aktivitas baru dan langsung tayang di homepage tanpa deploy/kode baru.
- Ada minimal 1 booking aktivitas yang berhasil bayar lunas end-to-end (dari klik Book sampai status "paid") sebagai bukti alur berfungsi.

## 10. Perkiraan Cakupan Implementasi (kalau lanjut ke eksekusi)

- Migration: `activities`, `activity_bookings` (+ `activity_schedules` kalau §8.1 diputuskan perlu)
- Model: `Activity`, `ActivityBooking`
- Backend: `Admin\ActivityController` (CRUD), `Admin\ActivityBookingController` (list/manage), `ActivityBookingController` publik (detail + store + checkout), tambahan handler di `WebhookController`
- Frontend: `Admin/Activities/{Index,Create}.svelte`, `Admin/ActivityBookings/Index.svelte`, `customer/activity-detail.svelte`, `customer/activity-checkout.svelte`
- Update: `resources/js/pages/Welcome.svelte` — tidak perlu diubah (kartu sudah generic, tinggal `href`-nya yang diupdate lewat Admin > Services setelah §6.4)

Perkiraan ini **bukan bagian dari PRD final**, hanya gambaran awal untuk estimasi effort — detail teknis final tetap mengikuti keputusan di §8.
