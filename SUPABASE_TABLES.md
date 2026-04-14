# Struktur Tabel Supabase

## 1. users
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| uid | uuid | Primary key (dari Firebase Auth) |
| email | text | Email user |
| display_name | text | Nama lengkap |
| phone_number | text | Nomor HP |
| role | text | 'admin' atau 'driver' |
| status | text | 'pending', 'approved', 'rejected', 'disabled' |
| photo_url | text | URL foto profil |
| created_at | timestamptz | Tanggal dibuat |

## 2. jobs
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key (timestamp ms) |
| kode_booking | text | Kode booking |
| no | int | Nomor urut |
| tanggal | text | Tanggal (DD/MM/YYYY) |
| jam | text | Jam pickup |
| nama_tamu | text | Nama tamu |
| phone_tamu | text | HP tamu |
| flight | text | Nomor flight |
| pickup | text | Lokasi jemput |
| dropoff | text | Lokasi tujuan |
| pickup_lat | double | Latitude pickup |
| pickup_lng | double | Longitude pickup |
| dropoff_lat | double | Latitude dropoff |
| dropoff_lng | double | Longitude dropoff |
| pass | int | Jumlah passenger |
| price | int | Harga |
| status | text | 'Pending', 'OTW', 'Tiba', 'Selesai', 'Cancel' |
| driver_id | uuid | FK ke users (nullable) |
| driver_name | text | Nama driver |
| admin_id | uuid | FK ke users (yang buat job) |
| is_shared | boolean | Apakah di pool |
| is_cash | boolean | Apakah cash |
| is_cancelled | boolean | Apakah dibatalkan |
| foto_tiba_url | text | Foto bukti tiba |
| foto_tiba_timestamp | timestamptz | Waktu foto tiba |
| foto_tiba_lat | double | Lokasi foto tiba |
| foto_tiba_lng | double | Lokasi foto tiba |
| foto_berangkat_url | text | Foto bukti berangkat |
| foto_berangkat_timestamp | timestamptz | Waktu foto berangkat |
| foto_berangkat_lat | double | Lokasi foto berangkat |
| foto_berangkat_lng | double | Lokasi foto berangkat |
| created_at | timestamptz | Dibuat |
| updated_at | timestamptz | Diupdate |

## 3. driver_locations
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| driver_id | uuid | Primary key, FK ke users |
| latitude | double | Latitude |
| longitude | double | Longitude |
| updated_at | timestamptz | Last update |

## 4. settings (opsional)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| key | text | Primary key (misal 'general') |
| max_job_per_day | int | Max job per driver/hari |
| updated_at | timestamptz | - |

## Catatan
- Firebase Auth masih digunakan untuk authentication
- Supabase Storage: bucket `photo-evidence` dan `profile-photos`