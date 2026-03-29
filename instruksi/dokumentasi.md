# Siwride App - Dokumentasi Teknis

## 1. Overview Project

Siwride adalah aplikasi ride-hailing (seperti Gojek/Grab) yang dibangun dengan:
- **Frontend**: Next.js 16 (App Router) + React 19 + Tailwind CSS
- **Backend**: Next.js API Routes (Serverless)
- **Database**: PostgreSQL (via Prisma ORM)
- **Auth**: Supabase Auth
- **Storage**: Supabase Storage (gambar driver/user)
- **Payment**: Midtrans (Snap API)
- **Notifications**: Web Push Notifications
- **Maps**: Google Maps API

---

## 2. Arsitektur Aplikasi

### 2.1 App Structure
```
app/
├── (mobile)/              # Mobile app (user & driver)
│   ├── login/            # User login
│   ├── signup/           # User registration
│   ├── home/             # User home (tabs: ride, history, chat, settings)
│   ├── driver/          # Driver app
│   │   ├── login/
│   │   ├── signup/
│   │   ├── home/        # Driver dashboard
│   │   ├── earnings/    # Driver earnings
│   │   ├── chat/        # Driver chat
│   │   └── support-chat/
│   ├── chat/            # User chat list
│   ├── orders/          # Order details & payment
│   └── support-chat/   # User support
├── admin/                # Admin dashboard
│   ├── login/
│   ├── users/
│   ├── drivers/
│   ├── orders/
│   ├── payments/
│   ├── refunds/
│   ├── driver-payouts/
│   ├── ride-categories/
│   ├── packages/
│   ├── pricing-zones/
│   ├── sharing-rides/
│   ├── support-chat/
│   └── chat/
└── api/                  # API Routes
    ├── auth/
    ├── orders/
    ├── drivers/
    ├── users/
    ├── payments/
    ├── refunds/
    ├── driver-payouts/
    ├── chat/
    ├── support-chat/
    ├── ride-categories/
    ├── packages/
    ├── pricing-zones/
    ├── sharing-rides/
    ├── driver-locations/
    ├── ratings/
    └── cron/
```

---

## 3. Fitur & Flow Aplikasi

### 3.1 Authentication

#### User Registration
1. User mengisi form: firstname, lastname, email, phone, password
2. Validasi: email & phone unik
3. Hash password dengan bcrypt
4. Simpan ke tabel `users`
5. Kirim email verifikasi (opsional)

#### Driver Registration
1. Driver填写: name, email, phone, password, vehicleType, vehicleRegistrationNumber
2. Admin approve driver (status: active/inactive)
3. Driver bisa login setelah diaktifkan

#### Forgot Password
1. User/Driver masukkan email
2. Generate token + simpan ke `password_reset_tokens`
3. Kirim email dengan link reset
4. User masukkan password baru

---

### 3.2 Ride Booking Flow

#### Regular Ride (Non-Sharing)
```
1. User Pilih Lokasi
   ├── Input pickup location (Google Places Autocomplete)
   ├── Input destination
   └── Google Routes API hitung distance & duration

2. Pilih Paket
   ├── Ride Category: DISTANCE / HOURLY
   ├── Package: economy / comfort / business
   └── Hitung fare berdasarkan pricing

3. Detail Ride
   ├── passengerCount, adults, children
   ├── carSeats (jika ada anak)
   ├── flightNumber (untuk airport pickup)
   ├── notes
   └── pickupDateTime (opsional)

4. Simpan Order
   ├── Status: PENDING
   ├── Fare dihitung
   ├── Push notification ke admin
   └── Redirect ke payment

5. Payment (Midtrans)
   ├── Create payment -> generate snap token
   ├── User pilih metode pembayaran
   ├── QRIS, GOPAY, ShopeePay, DANA, VA BCA/BNI/BRI/Mandiri/Permata, Credit Card
   ├── Webhook dari Midtrans update status payment
   └── Update order status

6. Admin/Driver Assign
   ├── Admin lihat order di dashboard
   ├── Assign driver ke order
   ├── Driver dapat push notification
   ├── Status order: ASSIGNED

7. Driver Arrives
   ├── Driver update status: PICKING_UP
   ├── Upload pickup proof image (opsional)
   └── Status: IN_PROGRESS

8. Ride Complete
   ├── Driver update status: COMPLETED
   ├── Upload dropoff proof image (opsional)
   └── Payment sudah PAID
```

#### Round Trip
- User centang "Round Trip"
- Sistem buat 2 orders:
  - Order pertama: pickup -> destination
  - Order kedua (parent_order_id): destination -> pickup
- Kedua order harus dibayar

---

### 3.3 Pricing System

#### Ride Categories
- `DISTANCE`: Harga per kilometer
- `HOURLY`: Harga per jam
- `TRIP`: Harga tetap (flat)

#### Package Tiers
- `economy`: Harga termurah, kapasitas 4
- `comfort`: Harga menengah, kapasitas 4
- `business`: Harga premium, kapasitas 4

#### Zone-Based Pricing
- Admin buat `pricing_zones` (polygon di map)
- Admin buat `zone_routes` (origin -> destination)
- Admin set `zone_route_prices` per package
- Sistem lookup harga berdasarkan zona pickup & dropoff

#### Driver Commission
- Per route bisa diset:
  - `PERCENTAGE`: % dari fare
  - `FLAT`: nominal tetap

---

### 3.4 Sharing Ride Flow

#### Create New Session (Opsi A)
```
1. User pilih pickup & destination
2. Sistem cari zone pricing
3. Cari matching session yang:
   - Same pickup zone
   - Same dropoff zone
   - Same package
   - Same scheduled time (dalam window)
   - Status: WAITING_PASSENGERS atau READY
   - Masih ada seat tersedia

4. Jika ada matching session -> join
5. Jika tidak ada -> buat session baru

6. Session properties:
   - time_window_minutes: 60 (configurable)
   - min_passengers: 2
   - max_passengers: 4
   - expires_at: scheduledPickupTime + timeWindow

7. Passenger join:
   - Bikin order dengan farePerPerson
   - Tambah ke sharing_ride_passengers
   - Urutan pickup/dropoff dihitung otomatis
```

#### Join Existing Session (Opsi B)
```
1. User lihat available sessions dari zone yang sama
2. Pilih session yang ingin digabung
3. Validasi:
   - Seat tersedia
   - User belum di session ini
   - Pickup location dalam zone yang sama
4. Join session
```

#### Sharing Ride Status
- `WAITING_PASSENGERS`: Menunggu passenger lain
- `READY`: Minimal passenger terpenuhi
- `ASSIGNED`: Driver sudah di-assign
- `IN_PROGRESS`: Sedang berlangsung
- `COMPLETED`: Selesai
- `CANCELLED`: Dibatalkan
- `EXPIRED`: Waktu habis

#### Auto-Expire Cron
- Job `cron/expire-sharing-rides` running periodically
- Update session status ke EXPIRED jika:
  - Waktu > expires_at
  - Status masih WAITING_PASSENGERS

---

### 3.5 Payment Flow

#### Payment Methods
- **QRIS**: QR Code untuk scan
- **E-Wallets**: GOPAY, ShopeePay, DANA, OVO
- **Virtual Account**: BCA, BNI, BRI, Mandiri, Permata
- **Credit Card**

#### Payment Status
- `PENDING`: Menunggu pembayaran
- `PAID`: Sudah dibayar
- `EXPIRED`: Kadaluarsa (30 menit)
- `FAILED`: Gagal
- `REFUNDED`: Full refund
- `PARTIALLY_REFUNDED`: Refund sebagian

#### Payment Flow
```
1. User klik "Bayar"
2. API call: POST /api/payments
3. Generate externalId: SIWR-{orderId}-{timestamp}
4. Create Midtrans Snap transaction
5. Simpan payment record (status: PENDING)
6. Return snapToken & redirectUrl

7. User pilih metode & lakukan pembayaran
8. Midtrans callback ke webhook
9. API /api/payments/webhook:
   ├── Verify signature
   ├── Find payment by externalId
   ├── Update status based on transaction_status
   └── If FAILED/EXPIRED -> cancel order

10. User di-redirect ke /orders/{id}/payment-status
```

#### Refund Flow
```
1. Admin klik "Refund" di payment
2. API call: POST /api/refunds
3. Midtrans refund API call
4. Create refund record
5. Update payment status: REFUNDED
```

---

### 3.6 Driver Payout Flow

#### Driver Earnings Calculation
```
totalEarnings = SUM(completedOrders.fare * 0.8)  // 80% ke driver
pendingPayout = totalEarnings - totalPaidOut - pendingAmount
```

#### Payout Status
- `PENDING`: Dibuat, menunggu proses
- `PROCESSING`: Sedang transfer
- `COMPLETED`: Transfer selesai
- `FAILED`: Transfer gagal

#### Payout Flow
```
1. Driver request payout (via app)
2. Admin approve di dashboard
3. Admin input:
   - amount
   - bankCode, bankName
   - accountNumber, accountName
   - notes
   - proofImageUrl (bukti transfer)

4. Update payout status
5. Driver dapat notifikasi
```

---

### 3.7 Chat System

#### Order Chat
- Otomatis dibuat saat order di-assign ke driver
- Tabel: `chat_rooms`, `chat_messages`
- Realtime via Supabase Realtime / Socket.io

#### Support Chat
- User/Driver bisa buat chat dengan admin
- Tabel: `support_chats`, `support_messages`
- Admin bisa assign ke dirinya

---

### 3.8 Push Notifications

#### Subscriptions
- Web Push API
- Simpan: endpoint, p256dh, auth
- Untuk user, driver, dan admin

#### Notification Types
- `NEW_ORDER`: Admin dapat order baru
- `ORDER_CANCELLED`: Order dibatalkan
- `DRIVER_ASSIGNED`: Driver di-assign ke order
- `ORDER_COMPLETED`: Order selesai
- `SHARING_RIDE_READY`: Sharing ride penuh
- `SHARING_RIDE_EXPIRED`: Sharing ride expired
- `ORDER_STATUS_CHANGED`: Status order berubah

---

### 3.9 Driver Location Tracking

#### Location Update
- Driver app send location periodically
- API: POST /api/driver-locations
- Simpan ke `driver_locations`

#### Location History
- Simpan histori untuk tracking
- Untuk calculate distance driver ke pickup

---

## 4. API Endpoints

### 4.1 Auth
- `POST /api/auth/login` - User/Driver login
- `POST /api/auth/forgot-password` - Request reset password
- `POST /api/auth/reset-password` - Reset password

### 4.2 Orders
- `GET /api/orders` - List orders (with filters)
- `POST /api/orders` - Create order
- `GET /api/orders/[id]` - Get order detail
- `POST /api/orders/[id]/assign` - Assign driver
- `POST /api/orders/[id]/cancel` - Cancel order
- `POST /api/orders/[id]/upload-image` - Upload proof image
- `POST /api/orders/calculate-fare` - Calculate fare

### 4.3 Payments
- `POST /api/payments` - Create payment
- `GET /api/payments/[id]` - Get payment
- `POST /api/payments/webhook` - Midtrans webhook

### 4.4 Drivers
- `GET /api/drivers` - List drivers
- `POST /api/drivers` - Create driver
- `GET /api/drivers/[id]` - Get driver
- `PUT /api/drivers/[id]` - Update driver
- `DELETE /api/drivers/[id]` - Delete driver
- `GET /api/drivers/[id]/earnings` - Get driver earnings

### 4.5 Sharing Rides
- `GET /api/sharing-rides` - List sessions
- `POST /api/sharing-rides` - Create/join session
- `GET /api/sharing-rides/[id]` - Get session
- `POST /api/sharing-rides/[id]/join` - Join session
- `POST /api/sharing-rides/[id]/passengers` - Add passenger

### 4.6 Admin
- `GET /api/admin/users` - Manage users
- `GET /api/admin/drivers` - Manage drivers
- `GET /api/admin/orders` - Manage orders
- `GET /api/admin/payments` - Manage payments
- `GET /api/admin/driver-payouts` - Manage payouts
- `GET /api/admin/refunds` - Manage refunds

---

## 5. Enums & Constants

### OrderStatus
```
PENDING -> ASSIGNED -> PICKING_UP -> IN_PROGRESS -> COMPLETED
                \                        /
                 ----> CANCELLED <------
```

### PaymentStatus
```
PENDING -> PAID
    |      |
    v      v
 EXPIRED  REFUNDED / PARTIALLY_REFUNDED
    |
    v
  FAILED
```

### SharingRideStatus
```
WAITING_PASSENGERS -> READY -> ASSIGNED -> IN_PROGRESS -> COMPLETED
                              \                 /
                               -> CANCELLED/EXPIRED
```

---

## 6. Integrasi Eksternal

### Google Maps
- Places Autocomplete: Input lokasi
- Directions API: Hitung distance & duration
- Geocoding: Konversi koordinat ke alamat

### Midtrans
- Snap API: Payment page
- Core API: Refund, status check
- Webhook: Status notifications

### Supabase
- Auth: User/Driver authentication
- Storage: Image uploads (driver-images, user-images)
- Realtime: Chat messages, order updates

---

## 7. Catatan untuk Laravel

### Schema Mapping
1. Semua tabel sudah di-convert ke SQL di `database.sql`
- Ganti `JSON` column type ke `JSON` di Laravel Migration
- `DATETIME` -> `timestamp` di Laravel
- `ENUM` -> `enum` di Laravel atau string dengan validation

### API Routes
- Route pattern: `/api/{resource}` untuk CRUD
- Policy/Authorization middleware
- Request validation dengan Form Request

### Realtime
- Laravel Broadcasting (Pusher/Reverb)
- Atau Supabase Realtime

### File Storage
- Laravel Storage (local/S3)
- Atau tetap gunakan Supabase Storage

### Authentication
- Laravel Sanctum / Jetstream
- Atau integrasi Supabase Auth

---

## 8. Configuration Variables

```env
# Database
DATABASE_URL=

# Supabase
NEXT_PUBLIC_SUPABASE_URL=
NEXT_PUBLIC_SUPABASE_ANON_KEY=
SUPABASE_SERVICE_ROLE_KEY=

# Midtrans
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=

# Google Maps
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=
GOOGLE_MAPS_API_SERVER_KEY=

# App
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

---

## 9. Cron Jobs

1. **Cleanup Location History**
   - Endpoint: `/api/cron/cleanup-location-history`
   - Hapus data lokasi lama

2. **Expire Sharing Rides**
   - Endpoint: `/api/cron/expire-sharing-rides`
   - Update status expired sessions

---

Dokumen ini membantu Anda memahami keseluruhan sistem Siwride untuk proses recode ke Laravel.
