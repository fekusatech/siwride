# Session Changes Summary - D'Jali Team API & Web Updates

## Tanggal: 26 April 2026

---

## 1. API Endpoints Baru

### Authentication
- `POST /api/v1/auth/login` - Login user
- `POST /api/v1/auth/register` - Register driver
- `POST /api/v1/auth/logout` - Logout current token
- `POST /api/v1/auth/logout-all` - Logout all devices

### User Profile
- `GET /api/v1/user` - Get current user profile
- `PUT /api/v1/user` - Update profile
- `POST /api/v1/user/photo` - Upload profile photo

### Jobs (Driver)
- `GET /api/v1/jobs/shared` - Get job pool (driver_id = null)
- `GET /api/v1/jobs/my` - Get my rides (filtered by driver_id = current user)
- `GET /api/v1/jobs/{id}` - Get job detail
- `GET /api/v1/jobs/{id}/history` - Get job status history
- `POST /api/v1/jobs/{id}/take` - Take job from pool
- `POST /api/v1/jobs/{id}/claim` - Claim job (needs admin approval)
- `POST /api/v1/jobs/{id}/accept-claim` - Admin accept claim
- `POST /api/v1/jobs/{id}/reject-claim` - Admin reject claim
- `PATCH /api/v1/jobs/{id}/status` - Update job status (otw, tiba, selesai)
- `POST /api/v1/jobs/{id}/evidence` - Upload evidence photo

### Tracking
- `POST /api/v1/tracking/update` - Update driver location
- `GET /api/v1/tracking/active` - Get active driver locations (admin)

### Admin Routes
- `GET /api/v1/jobs` - Get all jobs (admin)
- `POST /api/v1/jobs` - Create job (admin)
- `POST /api/v1/jobs/{id}/assign` - Assign job to driver (admin)
- `GET /api/v1/users/pending` - Get pending drivers
- `PUT /api/v1/users/{id}/status` - Approve/reject driver
- `GET /api/v1/reports/salary` - Get salary report
- `GET /api/v1/settings` - Get settings
- `PUT /api/v1/settings` - Update settings

---

## 2. Database Tables

### Table: api_logs (NEW)
Logging semua request API untuk debugging.
```php
- method, path, request_headers, request_body
- response_body, status_code
- user_id, user_email, user_role
- ip_address, user_agent, duration_ms
```

### Table: order_status_history (NEW)
Tracking history perubahan status order.
```php
- order_id, driver_id, status
- latitude, longitude, notes
```

---

## 3. Model Updates

### User.php
- Added: `use Laravel\Sanctum\HasApiTokens`
- Added trait: `HasApiTokens`

### OrderStatusHistory.php (NEW)
- Model untuk tracking status job

---

## 4. Web Panel Changes (Svelte)

### Admin/Orders/Index.svelte
- Added user role detection: `let userRole = authUser?.role`
- Different UI for driver vs admin:
  - **Driver**: See only their orders, "Ambil Order" button for unassigned jobs
  - **Admin**: Full control, all buttons visible
- Driver status flow: OTW → TIBA → SELESAI

### Admin/Orders/Dashboard.svelte
- Fixed driver name display: `order.driver.firstname + order.driver.lastname`

---

## 5. Route Changes

### routes/api.php
- Added new endpoints for job management
- Added profile photo upload

### routes/web.php
- Added route: `POST admin/orders/{order}/take` for driver to take order
- Added route: `POST admin/orders/{order}/accept-claim`
- Added route: `POST admin/orders/{order}/reject-claim`

---

## 6. Controller Changes

### OrderController.php (Admin)
- Added `take()` method - driver takes order directly
- Updated `updateStatus()` - now supports driver role and more statuses (otw, tiba)

### JobController.php (API)
- Added `claim()` - driver claims order (needs approval)
- Added `acceptClaim()` - admin accepts claim
- Added `rejectClaim()` - admin rejects claim
- Added `history()` - get job status history
- Updated `updateStatus()` - records to order_status_history table
- Updated `myRides()` - filters by driver_id = current user, date >= today, limit 20
- Updated `show()` - authorization check for owner/admin/shared pool

---

## 7. Middleware

### LogApiRequest.php (NEW)
Middleware untuk logging semua request API ke tabel `api_logs`.

---

## 8. API Documentation

Updated: resources/views/api-docs.blade.php
- All endpoints documented with request/response examples
- Available at: `/dokumentasi`

---

## 9. Test Accounts

- Admin: `admin@siwride.com` / password admin
- Driver: `driver@driver.com` / `driver`

---

## Notes

- Jobs dengan `driver_id = null` dan `is_shared = true` adalah job pool yang bisa diambil
- Driver hanya bisa update status order miliknya sendiri
- API response format: `{ "status": "success", "data": {...} }`
- Semua API yang butuh auth gunakan header: `Authorization: Bearer {token}`