# API SRS — Driver Mobile App (Flutter)

**Base URL**: `https://siwride.com/api/v1`  
**Auth**: Sanctum Token (Bearer)  
**Format**: JSON  

---

## Daftar Isi

1. [Autentikasi](#1-autentikasi)
2. [Profile](#2-profile)
3. [Dashboard Driver](#3-dashboard-driver)
4. [Job Management](#4-job-management)
5. [Vehicle](#5-vehicle)
6. [Earnings](#6-earnings)
7. [Tracking](#7-tracking)
8. [Notifikasi](#8-notifikasi)
9. [Help Center](#9-help-center)
10. [Admin](#10-admin)

---

## 1. Autentikasi

### 1.1 Register Driver

`POST /auth/register`

Mendaftarkan driver baru. Status default `inactive` — menunggu approval admin.

**Request:**
```json
{
    "firstname": "string | required | max:255",
    "lastname": "string | nullable | max:255",
    "email": "string | required | email | unique",
    "phone": "string | required | max:50 | unique",
    "password": "string | required | min:8"
}
```

**Response (201):**
```json
{
    "status": "success",
    "message": "Registration successful. Please wait for admin approval.",
    "data": {
        "user": { ... }
    }
}
```

### 1.2 Login

`POST /auth/login`

**Request:**
```json
{
    "email": "string | required | email",
    "password": "string | required",
    "device_name": "string | nullable"
}
```

**Response (200):**
```json
{
    "status": "success",
    "data": {
        "token": "1|abc123...",
        "user": {
            "id": 1,
            "driver_id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "role": "driver",
            "status": "active",
            "vehicle": {
                "id": 1,
                "brand": "Toyota",
                "model": "Avanza",
                "registration_number": "B 1234 ABC",
                "color": "Silver",
                "type": "MPV"
            }
        }
    }
}
```

**Error (401):**
```json
{
    "status": "error",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

### 1.3 Logout

`POST /auth/logout`

Hapus token yang dipakai.

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": "success",
    "message": "Logged out successfully."
}
```

### 1.4 Logout All

`POST /auth/logout-all`

Hapus semua token user.

**Response:**
```json
{
    "status": "success",
    "message": "All tokens revoked."
}
```

### 1.5 Change Password

`POST /auth/change-password`

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
    "current_password": "string | required",
    "new_password": "string | required | min:8"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Password changed successfully."
}
```

---

## 2. Profile

### 2.1 Get Profile

`GET /user`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "uid": "...",
        "firstname": "John",
        "lastname": "Doe",
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "08123456789",
        "role": "driver",
        "status": "active",
        "image": "profiles/abc.jpg",
        "driver_id": 1,
        "vehicle": { ... },
        "nik": "...",
        "sim": "...",
        "nik_image": "storage/...",
        "sim_image": "storage/..."
    }
}
```

### 2.2 Update Profile

`PUT /user`

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
    "firstname": "string | max:255",
    "lastname": "string | max:255",
    "phone": "string | max:50"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Profile updated successfully.",
    "data": { ... }
}
```

### 2.3 Upload Photo

`POST /user/photo`

**Headers:** `Authorization: Bearer {token}`  
**Content-Type:** `multipart/form-data`

**Request:**
| Field | Type | Rules |
|-------|------|-------|
| photo | File | required, image, max 2MB |

**Response:**
```json
{
    "status": "success",
    "message": "Photo uploaded successfully.",
    "data": {
        "image": "profiles/abc.jpg"
    }
}
```

---

## 3. Dashboard Driver

### 3.1 Get Dashboard

`GET /driver/dashboard`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": "success",
    "data": {
        "today": {
            "total_jobs": 3,
            "completed": 2,
            "pending": 1,
            "earnings": 150000
        },
        "this_week": {
            "earnings": 750000
        },
        "this_month": {
            "earnings": 3200000
        },
        "all_time": {
            "total_jobs": 120,
            "completed": 115
        }
    }
}
```

---

## 4. Job Management

### 4.1 Available Jobs (Shared Pool)

`GET /jobs/shared`

**Headers:** `Authorization: Bearer {token}`

Jobs yang belum diambil driver mana pun.

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "booking_code": "SW-ABC123",
            "order_number": "#ABC123",
            "customer_name": "***",
            "customer_phone": "***",
            "pickup_address": "Kuta - Hotel X",
            "dropoff_address": "Seminyak - Beach",
            "date": "2026-06-30",
            "time": "10:00",
            "price": 150000.00,
            "passengers": 2,
            "status": "pending",
            "is_shared": true,
            "is_cash": false,
            "guest_info_hidden": true,
            "is_waiting_approval": false,
            "claimed_by_me": false,
            "distance_km": 12.50,
            "vehicle_category": { ... },
            "created_at": "..."
        }
    ]
}
```

> **Note:** `customer_name` & `customer_phone` akan di-masking (`***`) jika hari ini sebelum jam 16:00.

### 4.2 My Jobs

`GET /jobs/my`

**Headers:** `Authorization: Bearer {token}`

Jobs yang sudah jadi milik driver (confirmed, waiting approval, atau open order).

**Response:**
Sama seperti 4.1, dengan tambahan field:
```json
{
    "is_confirmed": true,
    "is_waiting_approval": false,
    "is_open_order": false
}
```

### 4.3 Job Detail

`GET /jobs/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "booking_code": "SW-ABC123",
        "customer_name": "Budi",
        "customer_phone": "08123456789",
        "customer_email": "budi@email.com",
        "pickup_address": "Kuta - Hotel X",
        "pickup_latitude": -8.718,
        "pickup_longitude": 115.168,
        "dropoff_address": "Seminyak - Beach",
        "dropoff_latitude": -8.691,
        "dropoff_longitude": 115.155,
        "date": "2026-06-30",
        "time": "10:00",
        "price": 150000.00,
        "passengers": 2,
        "distance_km": 12.50,
        "status": "pending",
        "is_cash": false,
        "payment_method": "xendit",
        "payment_status": "paid",
        "notes": "Tolong antar ke pintu belakang",
        "driver": { ... },
        "vehicle_category": {
            "id": 1,
            "title": "Standard Car",
            "image_url": "https://siwride.com/storage/..."
        },
        "guest_info_hidden": false
    }
}
```

### 4.4 Claim Job

`POST /jobs/{id}/take` atau `POST /jobs/{id}/claim`

**Headers:** `Authorization: Bearer {token}`

Mengajukan klaim untuk mengambil job. Menunggu approval admin.

**Response (200):**
```json
{
    "status": "success",
    "message": "Berhasil mengajukan klaim. Mohon tunggu konfirmasi dari admin."
}
```

**Error (400):**
```json
{
    "status": "error",
    "message": "Maaf, pekerjaan ini sudah diambil oleh driver lain."
}
```

### 4.5 Update Job Status

`PATCH /jobs/{id}/status`

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
    "status": "string | required | in:otw,tiba,selesai",
    "latitude": "numeric | nullable",
    "longitude": "numeric | nullable",
    "notes": "string | nullable"
}
```

**Urutan status:** `pending → otw → tiba → selesai`

**Response:**
```json
{
    "status": "success",
    "message": "Status updated successfully.",
    "data": { ... }
}
```

### 4.6 Upload Evidence

`POST /jobs/{id}/evidence`

**Headers:** `Authorization: Bearer {token}`  
**Content-Type:** `multipart/form-data`

**Request:**
| Field | Type | Rules |
|-------|------|-------|
| type | String | required, in:berangkat,tiba |
| photo | File | required, image, max 5MB |
| latitude | Float | nullable |
| longitude | Float | nullable |

**Response:**
```json
{
    "status": "success",
    "message": "Evidence uploaded successfully.",
    "data": {
        "id": 1,
        "type": "berangkat",
        "photo_url": "evidences/abc.jpg",
        "latitude": -8.718,
        "longitude": 115.168,
        "captured_at": "2026-06-28T10:00:00.000000Z"
    }
}
```

### 4.7 Complete Job (with Payment)

`POST /jobs/{id}/complete`

**Headers:** `Authorization: Bearer {token}`

Menandai job selesai + mencatat metode pembayaran.

**Request:**
```json
{
    "payment_method": "string | nullable | in:cash,xendit,transfer",
    "customer_phone": "string | nullable"
}
```

> Jika tidak dikirim, `payment_method` akan otomatis mengikuti `order.is_cash`.

**Response:**
```json
{
    "status": "success",
    "message": "Job completed successfully.",
    "data": {
        "order_id": 1,
        "status": "selesai",
        "payment_method": "cash",
        "payment_status": "paid",
        "total": 150000.00
    }
}
```

### 4.8 Job History

`GET /jobs/{id}/history`

**Headers:** `Authorization: Bearer {token}`

Riwayat status perubahan job.

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "order_id": 1,
            "status": "otw",
            "latitude": -8.718,
            "longitude": 115.168,
            "notes": "Dalam perjalanan ke pickup",
            "created_at": "2026-06-28T09:30:00.000000Z"
        }
    ]
}
```

---

## 5. Vehicle

### 5.1 Get Vehicle

`GET /driver/vehicle`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "driver_id": 1,
        "brand": "Toyota",
        "model": "Avanza",
        "type": "MPV",
        "registration_number": "B 1234 ABC",
        "color": "Silver",
        "status": "active",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

### 5.2 Update Vehicle

`PUT /driver/vehicle`

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
    "brand": "string | required",
    "model": "string | required",
    "type": "string | nullable",
    "registration_number": "string | required",
    "color": "string | nullable"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Vehicle info updated successfully.",
    "data": { ... }
}
```

---

## 6. Earnings

### 6.1 Earnings List

`GET /driver/earnings?period=monthly&year=2026&month=6`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
| Param | Type | Default | Options |
|-------|------|---------|---------|
| period | string | monthly | daily, weekly, monthly |
| year | number | current | - |
| month | number | current | 1-12 |
| date | string | today | YYYY-MM-DD (for daily) |
| start_date | string | - | YYYY-MM-DD (for weekly) |
| end_date | string | - | YYYY-MM-DD (for weekly) |

**Response:**
```json
{
    "status": "success",
    "data": {
        "period": "monthly",
        "start_date": "2026-06-01",
        "end_date": "2026-06-30",
        "summary": {
            "total_earnings": 3200000,
            "total_jobs": 20,
            "cash_jobs": 5,
            "non_cash_jobs": 15
        },
        "daily_breakdown": [
            {
                "date": "2026-06-01",
                "jobs": 2,
                "earnings": 300000,
                "orders": [
                    {
                        "id": 1,
                        "booking_code": "SW-ABC123",
                        "pickup": "Kuta - Hotel X",
                        "dropoff": "Seminyak",
                        "time": "10:00",
                        "price": 150000,
                        "is_cash": false
                    }
                ]
            }
        ]
    }
}
```

---

## 7. Tracking

### 7.1 Update Location

`POST /tracking/update`

**Headers:** `Authorization: Bearer {token}`

Kirim lokasi driver secara periodik (setiap 30-60 detik).

**Request:**
```json
{
    "latitude": "numeric | required",
    "longitude": "numeric | required"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Location updated."
}
```

---

## 8. Notifikasi

### 8.1 Get Notifications

`GET /driver/notifications`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": "success",
    "data": {
        "notifications": [
            {
                "id": 1,
                "type": "new_order",
                "title": "Pesanan Baru",
                "body": "Ada pesanan baru dari Kuta ke Seminyak",
                "data": {
                    "order_id": 1,
                    "pickup": "Kuta",
                    "dropoff": "Seminyak"
                },
                "is_read": false,
                "read_at": null,
                "created_at": "2026-06-28T10:00:00.000000Z"
            }
        ],
        "unread_count": 1,
        "pagination": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 20,
            "total": 1
        }
    }
}
```

### 8.2 Unread Count

`GET /driver/notifications/unread-count`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": "success",
    "data": {
        "unread_count": 5
    }
}
```

### 8.3 Mark Notification as Read

`PUT /driver/notifications/{id}/read`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": "success",
    "message": "Notification marked as read."
}
```

### 8.4 Mark All Notifications as Read

`PUT /driver/notifications/read-all`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
    "status": "success",
    "message": "All notifications marked as read."
}
```

### 8.5 Register FCM Token

`POST /driver/fcm-token`

**Headers:** `Authorization: Bearer {token}`

Daftarkan token Firebase Cloud Messaging untuk push notification.

**Request:**
```json
{
    "fcm_token": "string | required",
    "device_type": "string | nullable | in:android,ios"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "FCM token registered successfully."
}
```

### 8.6 Remove FCM Token

`DELETE /driver/fcm-token`

**Headers:** `Authorization: Bearer {token}`

**Request:**
```json
{
    "fcm_token": "string | required"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "FCM token removed."
}
```

---

## 9. Help Center

### 9.1 FAQ

`GET /help/faq`

**Headers:** `Authorization: Bearer {token}`

Ambil daftar FAQ dari pengaturan admin.

**Response:**
```json
{
    "status": "success",
    "data": {
        "faq": [
            {
                "question": "How do I book a ride?",
                "answer": "You can book via the app..."
            }
        ]
    }
}
```

### 9.2 Contact Info

`GET /help/contact`

**Headers:** `Authorization: Bearer {token}`

Ambil informasi kontak perusahaan.

**Response:**
```json
{
    "status": "success",
    "data": {
        "company_name": "SIWRide",
        "phone": "+6281138105600",
        "email": "info@siwride.com",
        "address": "Bali, Indonesia"
    }
}
```

### 9.3 Submit Contact

`POST /help/contact`

**Headers:** `Authorization: Bearer {token}`

Kirim pesan ke admin.

**Request:**
```json
{
    "subject": "string | required | max:255",
    "message": "string | required | max:5000"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Your message has been sent. We will contact you soon."
}
```

---

## 10. Admin

Semua endpoint di bawah ini khusus admin (`role = admin`).

### 8.1 List All Jobs

`GET /jobs`

**Headers:** `Authorization: Bearer {token}`  
**Query:** `?status=pending`

### 8.2 Create Job

`POST /jobs`

**Headers:** `Authorization: Bearer {token}`

```json
{
    "customer_name": "required",
    "customer_phone": "required",
    "pickup_address": "required",
    "dropoff_address": "required",
    "date": "required | date",
    "time": "required",
    "price": "required | numeric",
    "is_cash": "boolean",
    "driver_id": "nullable | exists:users,id"
}
```

### 8.3 Assign Driver

`POST /jobs/{id}/assign`

```json
{
    "driver_id": "required | exists:users,id"
}
```

### 8.4 Accept / Reject Claim

`POST /jobs/{id}/accept-claim`  
`POST /jobs/{id}/reject-claim`

**Request:**
```json
{
    "driver_id": "required | exists:users,id"
}
```

### 8.5 Pending Drivers

`GET /users/pending`

### 8.6 Approve / Reject Driver

`PUT /users/{id}/status`

```json
{
    "status": "required | in:approved,rejected"
}
```

### 8.7 Active Drivers Map

`GET /tracking/active`

Lokasi driver yang aktif (update < 5 menit).

### 8.8 Settings

`GET /settings` — Ambil semua settings  
`PUT /settings` — Update settings

```json
{
    "settings": {
        "key": "value"
    }
}
```

---

## Alur Driver App (Flutter)

```
Register → Login → Dashboard
                         │
            ┌────────────┼────────────┐
            ▼            ▼            ▼
      Available Jobs  My Jobs    Earnings
            │            │
            ▼            ▼
         Take Job    → Update Status
            │         otw → tiba → selesai
            │         (upload evidence di setiap step)
            ▼
         Complete Job
         (pilih payment method)
```

### Flow Update Status + Evidence

```
pending ──► otw ──► tiba ──► selesai
             │        │          │
        upload      upload     POST /complete
        evidence    evidence   (pilih payment)
        (berangkat) (tiba)
```

---

## Catatan Penting

1. **Guest info masking** — sebelum jam 16:00 hari ini, nama & telepon customer di-masking (`***`)
2. **Daily limit** — driver bisa di-set limit job per hari via admin settings
3. **Vehicle opsional saat register** — bisa diisi nanti lewat `PUT /driver/vehicle`
4. **Cash vs non-cash** — job `is_cash=true` berarti bayar langsung ke driver; `is_cash=false` via Xendit
5. **Image URLs** — semua path file perlu ditambahkan base URL: `https://siwride.com/storage/{path}`
6. **Driver dashboard** auto-hitung dari orders yang `status=selesai` dan `is_cancelled=false`
