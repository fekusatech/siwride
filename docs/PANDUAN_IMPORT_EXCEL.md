# Panduan Cepat: Import Orders via Excel

## Mulai Sekarang! 🚀

### Step 1: Buka Halaman Orders
Pergi ke **Admin > Orders**
```
http://localhost:8000/admin/orders
```

### Step 2: Klik Tombol "Import Excel"
Cari tombol hijau dengan ikon spreadsheet di pojok kanan atas.

### Step 3: Download Template
Di halaman import, klik **"Download Template"** untuk mendapatkan file Excel format yang benar.

### Step 4: Isi Data di Excel
Buka template Excel yang sudah didownload dan isi data orders dengan format berikut:

#### Kolom yang WAJIB diisi:
```
Date              → 2026-03-30 (YYYY-MM-DD atau DD/MM/YYYY)
Time              → 08:00 (HH:MM)
Customer Name     → John Doe
Customer Phone    → 081234567890
Pickup Address    → Airport Terminal 1
Dropoff Address   → Hotel Grand Bali
Price             → 150000
```

#### Kolom Optional (boleh kosong):
```
Flight Number     → GA123
Driver Code       → siw01 (gunakan kode driver/nid)
Vehicle Plate     → DK 1234 AB
Passengers        → 2 (default: 1)
Parking Gas Fee   → 20000 (default: 0)
Status            → pending/completed/cancelled (default: pending)
Booking Code      → (auto-generate jika kosong)
Order Number      → (auto-generate jika kosong)
```

### Step 5: Upload File
- **Cara 1**: Drag & drop file Excel ke area upload
- **Cara 2**: Klik tombol "Browse File" untuk pilih file

### Step 6: Import Orders
Klik tombol **"Import Orders"** dan tunggu prosesnya selesai.

### Step 7: Konfirmasi Sukses
Jika berhasil, Anda akan diarahkan ke halaman Orders dengan pesan sukses.

---

## Contoh Data Order

```
| Date       | Time  | Customer Name | Customer Phone | Flight Number | Driver Code | Vehicle Plate | Pickup Address     | Dropoff Address   | Passengers | Price  | Parking Gas Fee | Status  |
|------------|-------|---------------|----------------|---------------|-------------|---------------|--------------------|-------------------|------------|--------|-----------------|---------|
| 2026-03-30 | 08:00 | John Doe      | 081234567890   | GA123         | siw01       | DK 1234 AB    | Airport Terminal 1 | Hotel Grand Bali  | 2          | 150000 | 20000           | pending |
| 2026-03-30 | 10:00 | Jane Smith    | 081234567891   | GA456         | siw02       | DK 5678 CD    | Airport Terminal 2 | Ubud Resort       | 1          | 200000 | 30000           | pending |
| 2026-03-31 | 14:00 | Bob Wilson    | 081234567892   |               |             |               | Hotel Bali Raya    | Kuta Beach        | 3          | 120000 | 15000           | pending |
```

---

## Format Tanggal yang Didukung

Semua format ini bisa digunakan:
- ✅ `2026-03-30` (YYYY-MM-DD)
- ✅ `30/03/2026` (DD/MM/YYYY)
- ✅ `30-03-2026` (DD-MM-YYYY)
- ✅ `03/30/2026` (MM/DD/YYYY)
- ✅ `30 March 2026` (DD MONTH YYYY)

---

## Format Waktu yang Didukung

- ✅ `08:00` (HH:MM)
- ✅ `08:00:00` (HH:MM:SS)
- ✅ Format Excel numeric time

---

## Lookup Driver & Vehicle

### Driver Lookup
Sistem akan cari driver berdasarkan:
1. **Driver Code (NID)** → `siw01`, `siw02`, dll
2. **Nama Driver** → `Rahman`, `Budi`, dll

Jika tidak ditemukan, order tetap dibuat tanpa driver.

### Vehicle Lookup
Sistem akan cari kendaraan berdasarkan:
1. **Registration Number** → `DK 1234 AB`
2. **Type** → `Innova`, `Avanza`, dll
3. **Model** → `Reborn`, `Luxury`, dll

Jika tidak ditemukan, order tetap dibuat tanpa kendaraan.

---

## Auto-Generate Codes

Jika Anda tidak isi kolom berikut, sistem akan otomatis membuat:

### Booking Code
Format: `ORD{YYYYMMDD}{RANDOM}`
Contoh: `ORD20260330A1B2C3`

### Order Number
Format: `SIW{YYYYMMDD}{RANDOM}`
Contoh: `SIW20260330X1Y2`

---

## Error & Solusi

### ❌ "File terlalu besar"
**Solusi**: Ukuran file maksimal 10MB. Jika lebih, pisah jadi beberapa file.

### ❌ "Format file tidak valid"
**Solusi**: Gunakan file `.xlsx`, `.xls`, atau `.csv`.

### ❌ "Kolom wajib kosong"
**Solusi**: Pastikan Date, Time, Customer Name, Phone, Pickup, Dropoff, Price diisi.

### ❌ "Format tanggal salah"
**Solusi**: Gunakan format yang didukung (lihat di atas).

### ❌ "Driver/Vehicle tidak ditemukan"
**Solusi**: 
- Pastikan driver code/vehicle plate sesuai di database
- Gunakan driver code (nid) untuk hasil terbaik
- Jika tidak ditemukan, order tetap dibuat tanpa driver/vehicle

### ❌ "Validasi gagal"
**Solusi**:
- Review kolom yang wajib diisi
- Check format data (tanggal, nomor telepon, harga)
- Pastikan harga & passengers adalah angka

---

## Tips & Tricks

### 💡 Gunakan Driver Code
Lebih akurat menggunakan driver code (nid) daripada nama. Contoh: `siw01`, `siw02`

### 💡 Format Tanggal Konsisten
Untuk menghindari error, gunakan format yang sama di semua baris. Contoh: semua pakai `YYYY-MM-DD`

### 💡 Hapus Spacing Ekstra
Jangan ada spasi di awal/akhir data. Contoh salah: `" John Doe "` → Benar: `John Doe`

### 💡 Nomor Telepon
Gunakan format tanpa spasi. Contoh: `081234567890` bukan `0812 3456 7890`

### 💡 Harga Tanpa Format
Input harga hanya angka. Contoh: `150000` bukan `Rp 150.000` atau `150,000`

### 💡 Pisahkan File Besar
Jika ada 5000+ orders, pisahkan jadi beberapa file untuk import lebih lancar.

---

## Kapan Menggunakan Import?

### ✅ Cocok untuk:
- Bulk input orders dari spreadsheet
- Data dari sistem lain
- Migrasi data
- Seasonal import (liburan, event)

### ❌ Tidak cocok untuk:
- Satu order doang → Gunakan "Input Order" button
- Data yang sering berubah → Lebih baik manual
- Real-time data → Perlu API integration

---

## Jaminan Data

✅ **Semua data valid** sebelum disimpan ke database
✅ **Automatic rollback** jika ada error (semua atau tidak sama sekali)
✅ **No duplicate** booking codes
✅ **Unique order numbers** dijamin sistem
✅ **Validation** pada setiap field

---

## Perlu Bantuan?

1. **Lihat template** yang sudah didownload
2. **Cek contoh data** di panduan ini
3. **Review error message** yang muncul
4. **Hubungi tim support** jika stuck

---

**Happy Importing! 🎉**