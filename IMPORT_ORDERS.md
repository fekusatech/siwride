# Fitur Import Orders via Excel

## Instalasi

1. **Install Package Maatwebsite Excel**
   
   Buka terminal dan jalankan perintah berikut dari direktori project:
   
   ```bash
   composer require maatwebsite/excel
   ```

2. **Publish Konfigurasi (Opsional)**
   
   ```bash
   php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
   ```

3. **Migrate Database**
   
   Pastikan database sudah di-migrate:
   
   ```bash
   php artisan migrate
   ```

## Cara Menggunakan

### 1. Download Template

- Buka halaman Admin > Orders
- Klik tombol **"Import Excel"** (hijau)
- Klik tombol **"Download Template"** untuk mendapatkan format Excel yang benar

### 2. Isi Data Orders

Template Excel sudah memiliki header yang benar. Isi data dengan format berikut:

| Kolom | Wajib | Contoh | Keterangan |
|-------|-------|--------|------------|
| **Date** | ✅ Ya | `2026-03-30` | Tanggal order (YYYY-MM-DD) |
| **Time** | ✅ Ya | `08:00` | Jam penjemputan (HH:MM) |
| **Customer Name** | ✅ Ya | `John Doe` | Nama pelanggan |
| **Customer Phone** | ✅ Ya | `081234567890` | Nomor telepon pelanggan |
| **Flight Number** | ❌ Tidak | `GA123` | Nomor penerbangan |
| **Driver Code** | ❌ Tidak | `siw01` | Kode driver (nid) atau nama driver |
| **Vehicle Plate** | ❌ Tidak | `DK 1234 AB` | Nomor registrasi kendaraan |
| **Pickup Address** | ✅ Ya | `Airport Terminal 1` | Alamat penjemputan |
| **Dropoff Address** | ✅ Ya | `Hotel Grand Bali` | Alamat tujuan |
| **Passengers** | ❌ Tidak | `2` | Jumlah penumpang (default: 1) |
| **Price** | ✅ Ya | `150000` | Harga order |
| **Parking Gas Fee** | ❌ Tidak | `20000` | Biaya parkir & bensin (default: 0) |
| **Status** | ❌ Tidak | `pending` | `pending`, `completed`, atau `cancelled` |
| **Booking Code** | ❌ Tidak | `ORD20260330ABC123` | Kode booking (auto-generate jika kosong) |
| **Order Number** | ❌ Tidak | `SIW20260330XYZ` | Nomor order (auto-generate jika kosong) |

### 3. Upload File Excel

1. Buka halaman **Admin > Orders > Import Excel**
2. Drag & drop file Excel atau klik **Browse File**
3. Klik tombol **Import Orders**
4. Tunggu proses import selesai
5. Jika berhasil, Anda akan diarahkan ke halaman Orders dengan pesan sukses

## Format Tanggal & Waktu

### Format Tanggal yang Didukung
- `YYYY-MM-DD` (contoh: `2026-03-30`)
- `DD/MM/YYYY` (contoh: `30/03/2026`)
- `DD-MM-YYYY` (contoh: `30-03-2026`)
- `MM/DD/YYYY` (contoh: `03/30/2026`)
- `DD F YYYY` (contoh: `30 March 2026`)

### Format Waktu yang Didukung
- `HH:MM` (contoh: `08:00`)
- `HH:MM:SS` (contoh: `08:00:00`)
- Format Excel time (numeric)

## Validasi

Jika ada data yang tidak valid, import akan gagal dan menampilkan error:

- **Baris yang error** akan ditampilkan
- **Pesan error** menjelaskan kolom mana yang bermasalah
- Data yang sudah valid **tidak akan tersimpan** (rollback)

## Fitur Import

### Auto-Generate Booking Code
Jika kolom `booking_code` kosong, sistem akan membuat kode booking otomatis:
- Format: `ORD{YYYYMMDD}{RANDOM}`
- Contoh: `ORD20260330A1B2C3`

### Auto-Generate Order Number
Jika kolom `order_number` kosong, sistem akan membuat nomor order otomatis:
- Format: `SIW{YYYYMMDD}{RANDOM}`
- Contoh: `SIW20260330X1Y2`

### Driver Lookup
Sistem akan mencari driver berdasarkan:
1. **Driver Code** (nid) - contoh: `siw01`
2. **Nama Driver** - contoh: `Rahman`

Jika driver tidak ditemukan, order akan dibuat tanpa driver (null).

### Vehicle Lookup
Sistem akan mencari kendaraan berdasarkan:
1. **Registration Number** - contoh: `DK 1234 AB`
2. **Vehicle Type** - contoh: `Innova`
3. **Vehicle Model** - contoh: `Reborn`

Jika kendaraan tidak ditemukan, order akan dibuat tanpa kendaraan (null).

## Troubleshooting

### Error: "Validation Failed"
- Pastikan semua kolom wajib sudah diisi
- Periksa format tanggal dan waktu
- Pastikan nilai numerik (price, passengers) valid

### Error: "File too large"
- Ukuran file maksimal **10MB**
- Jika lebih, pisah menjadi beberapa file

### Error: "Invalid file format"
- Pastikan file berformat `.xlsx`, `.xls`, atau `.csv`
- Jangan ubah nama header kolom di template

### Driver/Vehicle Tidak Terassign
- Pastikan Driver Code atau Vehicle Plate sesuai dengan data di database
- Gunakan kode driver (nid) untuk hasil terbaik

## Contoh Data

```excel
Date        Time    Customer Name  Customer Phone  Flight Number  Driver Code  Pickup Address     Dropoff Address    Passengers  Price    Parking Gas Fee  Status
2026-03-30  08:00   John Doe       081234567890    GA123          siw01        Airport Terminal 1  Hotel Grand Bali   2           150000   20000            pending
2026-03-30  10:00   Jane Smith     081234567891    GA456          siw02        Airport Terminal 2  Ubud Resort        1           200000   30000            pending
```

## API Endpoints

- **GET** `/admin/orders/import` - Halaman import
- **POST** `/admin/orders/import` - Upload file Excel
- **GET** `/admin/orders/import/template` - Download template Excel

## File Structure

```
app/
├── Http/Controllers/Admin/
│   └── OrderController.php       # Controller dengan method import
├── Imports/
│   └── OrdersImport.php          # Class untuk handling import Excel
resources/js/pages/admin/orders/
├── Index.svelte                  # Halaman Orders (dengan tombol Import)
└── Import.svelte                 # Halaman Upload Excel
```

## Keamanan

- File divalidasi (hanya .xlsx, .xls, .csv)
- Ukuran maksimal 10MB
- Data divalidasi sebelum disimpan
- Transaksi database (rollback jika ada error)

## Support

Jika ada masalah, silakan hubungi tim development atau buat issue di repository.
