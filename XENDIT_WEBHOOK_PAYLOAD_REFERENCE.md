# Xendit Webhook Payload Reference

**Documentation untuk testing dan debugging webhook Xendit**

---

## Invoice Webhooks

### Success Payload
```json
{
  "id": "inv_1234567890",
  "external_id": "BOOKING123_1717599600",
  "user_id": 12345,
  "status": "PAID",
  "merchant_name": "SiWride",
  "merchant_profile_picture_url": "https://...",
  "amount": 150000,
  "payer_email": "customer@example.com",
  "description": "Payment for Booking BOOKING123",
  "paid_amount": 150000,
  "paid_at": "2026-06-05T10:30:00Z",
  "currency": "IDR",
  "payment_method": "BANK_TRANSFER",
  "bank_code": "BCA",
  "event": "invoice.paid",
  "created": "2026-06-05T10:00:00Z",
  "updated": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'paid'`

### Failure Payload
```json
{
  "id": "inv_1234567890",
  "external_id": "BOOKING123_1717599600",
  "user_id": 12345,
  "status": "FAILED",
  "merchant_name": "SiWride",
  "amount": 150000,
  "payer_email": "customer@example.com",
  "description": "Payment for Booking BOOKING123",
  "failure_code": "PAYMENT_DECLINED",
  "failure_message": "Your card was declined",
  "currency": "IDR",
  "event": "invoice.failed",
  "created": "2026-06-05T10:00:00Z",
  "updated": "2026-06-05T10:05:00Z"
}
```
✅ **Result:** `order.payment_status = 'failed'`

### Expiry Payload
```json
{
  "id": "inv_1234567890",
  "external_id": "BOOKING123_1717599600",
  "user_id": 12345,
  "status": "EXPIRED",
  "merchant_name": "SiWride",
  "amount": 150000,
  "payer_email": "customer@example.com",
  "description": "Payment for Booking BOOKING123",
  "currency": "IDR",
  "event": "invoice.expired",
  "created": "2026-06-05T10:00:00Z",
  "expired_date": "2026-06-06T10:00:00Z"
}
```
✅ **Result:** `order.payment_status = 'expired'`

### Cancellation Payload
```json
{
  "id": "inv_1234567890",
  "external_id": "BOOKING123_1717599600",
  "user_id": 12345,
  "status": "CANCELLED",
  "merchant_name": "SiWride",
  "amount": 150000,
  "payer_email": "customer@example.com",
  "description": "Payment for Booking BOOKING123",
  "currency": "IDR",
  "event": "invoice.cancelled",
  "created": "2026-06-05T10:00:00Z",
  "updated": "2026-06-05T10:15:00Z"
}
```
✅ **Result:** `order.payment_status = 'cancelled'`

---

## E-Wallet Webhooks

### Success Payload
```json
{
  "event": "ewallet.charge_succeeded",
  "external_id": "BOOKING123_1717599600",
  "status": "COMPLETED",
  "charge_id": "ewc_1234567890",
  "business_id": 12345,
  "reference_id": "BOOKING123",
  "wallet_code": "OVO",
  "amount": 150000,
  "currency": "IDR",
  "receipt_number": "OVO123456",
  "created": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'paid'`

### Failure Payload
```json
{
  "event": "ewallet.charge_failed",
  "external_id": "BOOKING123_1717599600",
  "status": "FAILED",
  "charge_id": "ewc_1234567890",
  "business_id": 12345,
  "reference_id": "BOOKING123",
  "wallet_code": "OVO",
  "amount": 150000,
  "currency": "IDR",
  "failure_reason": "INSUFFICIENT_BALANCE",
  "created": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'failed'`

### Cancellation Payload
```json
{
  "event": "ewallet.charge_cancelled",
  "external_id": "BOOKING123_1717599600",
  "status": "CANCELLED",
  "charge_id": "ewc_1234567890",
  "business_id": 12345,
  "reference_id": "BOOKING123",
  "wallet_code": "OVO",
  "amount": 150000,
  "currency": "IDR",
  "created": "2026-06-05T10:35:00Z"
}
```
✅ **Result:** `order.payment_status = 'cancelled'`

---

## Virtual Account (FVA) Webhooks

### Success Payload
```json
{
  "event": "virtual_account_payment",
  "id": "va_payment_1234567890",
  "external_id": "BOOKING123_1717599600",
  "business_id": 12345,
  "callback_virtual_account_id": "va_1234567890",
  "payment_id": "pay_1234567890",
  "amount": 150000,
  "currency": "IDR",
  "transaction_timestamp": "2026-06-05T10:30:00Z",
  "merchant_reference_code": "BOOKING123"
}
```
✅ **Result:** `order.payment_status = 'paid'`

### Expiry Payload
```json
{
  "event": "virtual_account_expiration",
  "id": "va_expiration_1234567890",
  "external_id": "BOOKING123_1717599600",
  "business_id": 12345,
  "callback_virtual_account_id": "va_1234567890",
  "status": "EXPIRED",
  "amount": 150000,
  "currency": "IDR",
  "timestamp": "2026-06-06T10:00:00Z"
}
```
✅ **Result:** `order.payment_status = 'expired'`

---

## QR Code Webhooks

### Success Payload
```json
{
  "event": "qr_code_charge.completed",
  "external_id": "BOOKING123_1717599600",
  "status": "COMPLETED",
  "qr_code_id": "qr_code_1234567890",
  "charge_id": "qrc_1234567890",
  "amount": 150000,
  "currency": "IDR",
  "receipt_number": "QRIS123456",
  "created": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'paid'`

### Failure Payload
```json
{
  "event": "qr_code_charge.failed",
  "external_id": "BOOKING123_1717599600",
  "status": "FAILED",
  "qr_code_id": "qr_code_1234567890",
  "charge_id": "qrc_1234567890",
  "amount": 150000,
  "currency": "IDR",
  "failure_reason": "USER_CANCELLED",
  "created": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'failed'`

### Expiry Payload
```json
{
  "event": "qr_code_charge.expired",
  "external_id": "BOOKING123_1717599600",
  "status": "EXPIRED",
  "qr_code_id": "qr_code_1234567890",
  "charge_id": "qrc_1234567890",
  "amount": 150000,
  "currency": "IDR",
  "expires_at": "2026-06-05T10:45:00Z"
}
```
✅ **Result:** `order.payment_status = 'expired'`

---

## Paylater Webhooks

### Success Payload
```json
{
  "event": "paylater.charge.completed",
  "external_id": "BOOKING123_1717599600",
  "status": "COMPLETED",
  "paylater_charge_id": "pl_charge_1234567890",
  "paylater_type": "KREDIVO",
  "amount": 150000,
  "currency": "IDR",
  "customer_email": "customer@example.com",
  "order_reference": "BOOKING123",
  "created_at": "2026-06-05T10:00:00Z",
  "completed_at": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'paid'`

### Failure Payload
```json
{
  "event": "paylater.charge.failed",
  "external_id": "BOOKING123_1717599600",
  "status": "FAILED",
  "paylater_charge_id": "pl_charge_1234567890",
  "paylater_type": "KREDIVO",
  "amount": 150000,
  "currency": "IDR",
  "failure_reason": "CREDIT_LIMIT_EXCEEDED",
  "order_reference": "BOOKING123",
  "failed_at": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'failed'`

---

## Direct Debit Webhooks

### Success Payload
```json
{
  "event": "payment_completed",
  "external_id": "BOOKING123_1717599600",
  "charge_id": "dd_charge_1234567890",
  "business_id": 12345,
  "status": "COMPLETED",
  "amount": 150000,
  "currency": "IDR",
  "channel_code": "BRI",
  "receipt_number": "DD123456789",
  "timestamp": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'paid'`

### Failure Payload
```json
{
  "event": "payment_failed",
  "external_id": "BOOKING123_1717599600",
  "charge_id": "dd_charge_1234567890",
  "business_id": 12345,
  "status": "FAILED",
  "amount": 150000,
  "currency": "IDR",
  "channel_code": "BRI",
  "failure_reason": "INVALID_ACCOUNT",
  "timestamp": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'failed'`

---

## Payment Session Webhooks

### Success Payload
```json
{
  "event": "payment_session.completed",
  "external_id": "BOOKING123_1717599600",
  "payment_session_id": "ps_1234567890",
  "business_id": 12345,
  "status": "COMPLETED",
  "amount": 150000,
  "currency": "IDR",
  "payment_method_type": "BANK_TRANSFER",
  "created_at": "2026-06-05T10:00:00Z",
  "completed_at": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'paid'`

### Failure Payload
```json
{
  "event": "payment_session.failed",
  "external_id": "BOOKING123_1717599600",
  "payment_session_id": "ps_1234567890",
  "business_id": 12345,
  "status": "FAILED",
  "amount": 150000,
  "currency": "IDR",
  "failure_code": "PAYMENT_DECLINED",
  "failed_at": "2026-06-05T10:30:00Z"
}
```
✅ **Result:** `order.payment_status = 'failed'`

### Expiry Payload
```json
{
  "event": "payment_session.expired",
  "external_id": "BOOKING123_1717599600",
  "payment_session_id": "ps_1234567890",
  "business_id": 12345,
  "status": "EXPIRED",
  "amount": 150000,
  "currency": "IDR",
  "expired_at": "2026-06-05T11:00:00Z"
}
```
✅ **Result:** `order.payment_status = 'expired'`

---

## Testing with Curl

### Test Invoice Success
```bash
curl -X POST http://localhost:8000/api/webhooks/xendit/invoice \
  -H "Content-Type: application/json" \
  -H "x-callback-token: YOUR_WEBHOOK_TOKEN" \
  -d '{
    "id": "inv_test_123",
    "external_id": "BOOKING123_'$(date +%s)'",
    "status": "PAID",
    "amount": 150000,
    "payer_email": "test@example.com",
    "event": "invoice.paid"
  }'
```

### Test Invoice Failure
```bash
curl -X POST http://localhost:8000/api/webhooks/xendit/invoice \
  -H "Content-Type: application/json" \
  -H "x-callback-token: YOUR_WEBHOOK_TOKEN" \
  -d '{
    "id": "inv_test_123",
    "external_id": "BOOKING123_'$(date +%s)'",
    "status": "FAILED",
    "amount": 150000,
    "failure_code": "PAYMENT_DECLINED",
    "failure_message": "Card declined",
    "payer_email": "test@example.com",
    "event": "invoice.failed"
  }'
```

### Test Invoice Expiry
```bash
curl -X POST http://localhost:8000/api/webhooks/xendit/invoice \
  -H "Content-Type: application/json" \
  -H "x-callback-token: YOUR_WEBHOOK_TOKEN" \
  -d '{
    "id": "inv_test_123",
    "external_id": "BOOKING123_'$(date +%s)'",
    "status": "EXPIRED",
    "amount": 150000,
    "payer_email": "test@example.com",
    "event": "invoice.expired"
  }'
```

---

## Common Failure Codes

| Code | Meaning |
|------|---------|
| PAYMENT_DECLINED | Pembayaran ditolak (insufficient funds, etc) |
| INSUFFICIENT_BALANCE | Saldo tidak cukup |
| INVALID_ACCOUNT | Rekening tidak valid |
| EXPIRED_CARD | Kartu kadaluarsa |
| USER_CANCELLED | User membatalkan pembayaran |
| TIMEOUT | Pembayaran timeout |
| FRAUD_DETECTED | Pembayaran dicurigai fraud |
| CREDIT_LIMIT_EXCEEDED | Limit kredit terlampaui |

---

## Debugging Checklist

- ✅ Webhook token ada di request header `x-callback-token`
- ✅ Webhook token di `.env` match dengan request header
- ✅ `external_id` format: `BOOKING_CODE_TIMESTAMP`
- ✅ Order dengan booking_code ada di database
- ✅ `payment_status` updated sesuai dengan status dari webhook
- ✅ Check logs: `storage/logs/laravel.log`
- ✅ Webhook endpoint di-trigger via Xendit Dashboard → Webhooks → Test
