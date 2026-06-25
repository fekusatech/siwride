<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Verify the Xendit callback token from the request header.
     */
    private function verifyToken(Request $request): bool
    {
        $token = Setting::getValue('xendit_webhook_token') ?? config('services.xendit.webhook_token');
        $callbackToken = $request->header('x-callback-token');

        if (! $token) {
            return true;
        }

        return $callbackToken === $token;
    }

    /**
     * Reject unverified requests.
     */
    private function requireValidToken(Request $request): ?JsonResponse
    {
        if (! $this->verifyToken($request)) {
            Log::warning('Xendit Webhook — invalid callback token', [
                'ip' => $request->ip(),
                'event' => $request->input('event'),
            ]);

            return response()->json(['error' => 'Invalid callback token'], 403);
        }

        return null;
    }

    /**
     * Resolve an Order by the external_id embedded in the payload.
     *
     * The external_id from Xendit is formatted as: BOOKINGCODE_TIMESTAMP
     */
    private function resolveOrder(array $payload): ?Order
    {
        $externalId = $payload['external_id'] ?? null;

        if (! $externalId) {
            return null;
        }

        $bookingCode = explode('_', $externalId)[0];

        return Order::where('booking_code', $bookingCode)->first();
    }

    /**
     * Log every incoming webhook payload for audit / debugging.
     */
    private function logPayload(string $product, Request $request): void
    {
        Log::info("Xendit Webhook — {$product}", [
            'event' => $request->input('event'),
            'id' => $request->input('id'),
            'status' => $request->input('status'),
            'external_id' => $request->input('external_id'),
        ]);
    }

    // ─────────────────────────────────────────────
    //  INVOICES
    // ─────────────────────────────────────────────

    /**
     * Handle Invoice webhooks (paid / expired / failed / cancelled).
     *
     * Configure at Xendit Dashboard → Settings → Webhooks → Invoices.
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/invoice
     */
    public function invoice(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('Invoice', $request);
        $payload = $request->all();
        $order = $this->resolveOrder($payload);

        if (($payload['status'] ?? '') === 'PAID') {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true, 'message' => 'Invoice paid']);
        }

        if ($payload['status'] ?? '' === 'EXPIRED') {
            $this->markOrderExpired($order, $payload);

            return response()->json(['success' => true, 'message' => 'Invoice expired']);
        }

        if ($payload['status'] ?? '' === 'FAILED') {
            $this->markOrderFailed($order, $payload);

            return response()->json(['success' => true, 'message' => 'Invoice failed']);
        }

        if ($payload['status'] ?? '' === 'CANCELLED') {
            $this->markOrderCancelled($order, $payload);

            return response()->json(['success' => true, 'message' => 'Invoice cancelled']);
        }

        return response()->json(['success' => true, 'message' => 'Invoice event ignored']);
    }

    // ─────────────────────────────────────────────
    //  FIXED VIRTUAL ACCOUNTS
    // ─────────────────────────────────────────────

    /**
     * Handle FVA webhooks (paid, created, updated, failed, expired).
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/fva
     */
    public function fva(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('FVA', $request);
        $payload = $request->all();
        $order = $this->resolveOrder($payload);

        if (isset($payload['payment_id']) || isset($payload['callback_virtual_account_id'])) {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true, 'message' => 'FVA paid']);
        }

        if (($payload['status'] ?? '') === 'EXPIRED') {
            $this->markOrderExpired($order, $payload);

            return response()->json(['success' => true, 'message' => 'FVA expired']);
        }

        if (($payload['status'] ?? '') === 'FAILED') {
            $this->markOrderFailed($order, $payload);

            return response()->json(['success' => true, 'message' => 'FVA failed']);
        }

        return response()->json(['success' => true, 'message' => 'FVA event ignored']);
    }

    // ─────────────────────────────────────────────
    //  DISBURSEMENT
    // ─────────────────────────────────────────────

    /**
     * Handle Disbursement webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/disbursement
     */
    public function disbursement(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('Disbursement', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  PAYOUT LINK
    // ─────────────────────────────────────────────

    /**
     * Handle Payout Link webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/payout-link
     */
    public function payoutLink(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('PayoutLink', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  RETAIL OUTLETS (OTC)
    // ─────────────────────────────────────────────

    /**
     * Handle Retail Outlet webhooks (paid, failed, expired).
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/retail-outlet
     */
    public function retailOutlet(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('RetailOutlet', $request);
        $payload = $request->all();
        $order = $this->resolveOrder($payload);

        if ($payload['status'] ?? '' === 'PAID') {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true, 'message' => 'OTC paid']);
        }

        if (($payload['status'] ?? '') === 'EXPIRED') {
            $this->markOrderExpired($order, $payload);

            return response()->json(['success' => true, 'message' => 'OTC expired']);
        }

        if (($payload['status'] ?? '') === 'FAILED') {
            $this->markOrderFailed($order, $payload);

            return response()->json(['success' => true, 'message' => 'OTC failed']);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  CARDS
    // ─────────────────────────────────────────────

    /**
     * Handle Cards webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/cards
     */
    public function cards(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('Cards', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  DIRECT DEBIT
    // ─────────────────────────────────────────────

    /**
     * Handle Direct Debit webhooks (completed, failed).
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/direct-debit
     */
    public function directDebit(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('DirectDebit', $request);
        $payload = $request->all();
        $order = $this->resolveOrder($payload);

        if ($payload['event'] ?? '' === 'payment_completed') {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true, 'message' => 'Direct debit completed']);
        }

        if (($payload['event'] ?? '') === 'payment_failed') {
            $this->markOrderFailed($order, $payload);

            return response()->json(['success' => true, 'message' => 'Direct debit failed']);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  XENPLATFORM
    // ─────────────────────────────────────────────

    /**
     * Handle XenPlatform webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/xenplatform
     */
    public function xenplatform(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('XenPlatform', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  REPORTS
    // ─────────────────────────────────────────────

    /**
     * Handle Reports webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/reports
     */
    public function reports(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('Reports', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  REQUEST PAYMENT V2
    // ─────────────────────────────────────────────

    /**
     * Handle Payment Request v2 webhooks (success, failed, expired).
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/payment-request
     */
    public function paymentRequest(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('PaymentRequestV2', $request);
        $payload = $request->all();
        $order = $this->resolveOrder($payload);

        if (($payload['event'] ?? '') === 'payment_request.payment_success') {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true, 'message' => 'Payment request success']);
        }

        if (($payload['event'] ?? '') === 'payment_request.payment_failed') {
            $this->markOrderFailed($order, $payload);

            return response()->json(['success' => true, 'message' => 'Payment request failed']);
        }

        if (($payload['event'] ?? '') === 'payment_request.expired') {
            $this->markOrderExpired($order, $payload);

            return response()->json(['success' => true, 'message' => 'Payment request expired']);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  PAYMENT METHOD V2
    // ─────────────────────────────────────────────

    /**
     * Handle Payment Method v2 webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/payment-method
     */
    public function paymentMethod(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('PaymentMethodV2', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  PAYMENT TOKEN V3
    // ─────────────────────────────────────────────

    /**
     * Handle Payment Token v3 webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/payment-token
     */
    public function paymentToken(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('PaymentTokenV3', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  E-WALLET
    // ─────────────────────────────────────────────

    /**
     * Handle E-Wallet webhooks (completed, failed, cancelled).
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/ewallet
     */
    public function ewallet(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('Ewallet', $request);
        $payload = $request->all();
        $order = $this->resolveOrder($payload);

        if ($payload['status'] ?? '' === 'COMPLETED') {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true, 'message' => 'E-Wallet completed']);
        }

        if (($payload['status'] ?? '') === 'FAILED') {
            $this->markOrderFailed($order, $payload);

            return response()->json(['success' => true, 'message' => 'E-Wallet failed']);
        }

        if (($payload['status'] ?? '') === 'CANCELLED') {
            $this->markOrderCancelled($order, $payload);

            return response()->json(['success' => true, 'message' => 'E-Wallet cancelled']);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  RECURRING PAYMENT
    // ─────────────────────────────────────────────

    /**
     * Handle Recurring Payment webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/recurring-payment
     */
    public function recurringPayment(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('RecurringPayment', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  PAYLATER
    // ─────────────────────────────────────────────

    /**
     * Handle Paylater webhooks (completed, failed, cancelled).
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/paylater
     */
    public function paylater(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('Paylater', $request);
        $payload = $request->all();
        $order = $this->resolveOrder($payload);

        if ($payload['status'] ?? '' === 'COMPLETED') {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true, 'message' => 'Paylater completed']);
        }

        if (($payload['status'] ?? '') === 'FAILED') {
            $this->markOrderFailed($order, $payload);

            return response()->json(['success' => true, 'message' => 'Paylater failed']);
        }

        if (($payload['status'] ?? '') === 'CANCELLED') {
            $this->markOrderCancelled($order, $payload);

            return response()->json(['success' => true, 'message' => 'Paylater cancelled']);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  QR CODE
    // ─────────────────────────────────────────────

    /**
     * Handle QR Code webhooks (completed, failed, expired).
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/qr-code
     */
    public function qrCode(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('QRCode', $request);
        $payload = $request->all();
        $order = $this->resolveOrder($payload);

        if ($payload['status'] ?? '' === 'COMPLETED') {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true, 'message' => 'QR Code completed']);
        }

        if (($payload['status'] ?? '') === 'FAILED') {
            $this->markOrderFailed($order, $payload);

            return response()->json(['success' => true, 'message' => 'QR Code failed']);
        }

        if (($payload['status'] ?? '') === 'EXPIRED') {
            $this->markOrderExpired($order, $payload);

            return response()->json(['success' => true, 'message' => 'QR Code expired']);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  PAYMENT SESSION
    // ─────────────────────────────────────────────

    /**
     * Handle Payment Session webhooks (completed, failed, expired).
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/payment-session
     */
    public function paymentSession(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('PaymentSession', $request);
        $payload = $request->all();
        $order = $this->resolveOrder($payload);

        if (($payload['event'] ?? '') === 'payment_session.completed') {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true, 'message' => 'Payment session completed']);
        }

        if (($payload['event'] ?? '') === 'payment_session.failed') {
            $this->markOrderFailed($order, $payload);

            return response()->json(['success' => true, 'message' => 'Payment session failed']);
        }

        if (($payload['event'] ?? '') === 'payment_session.expired') {
            $this->markOrderExpired($order, $payload);

            return response()->json(['success' => true, 'message' => 'Payment session expired']);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  BILL PAYMENTS
    // ─────────────────────────────────────────────

    /**
     * Handle Bill Payments webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/bill-payments
     */
    public function billPayments(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('BillPayments', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  PAYOUTS V3
    // ─────────────────────────────────────────────

    /**
     * Handle Payouts v3 webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/payouts-v3
     */
    public function payoutsV3(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('PayoutsV3', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  PAYMENT REQUEST V3
    // ─────────────────────────────────────────────

    /**
     * Handle Payment Request v3 webhooks.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit/payment-request-v3
     */
    public function paymentRequestV3(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('PaymentRequestV3', $request);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────
    //  DEPRECATED — Single catch-all handler
    // ─────────────────────────────────────────────

    /**
     * Legacy catch-all handler for backward compatibility.
     *
     * Endpoint: POST https://siwride.com/api/webhooks/xendit
     */
    public function xendit(Request $request): JsonResponse
    {
        $error = $this->requireValidToken($request);
        if ($error) {
            return $error;
        }

        $this->logPayload('Legacy', $request);
        $payload = $request->all();
        $order = $this->resolveOrder($payload);

        if (($payload['status'] ?? '') === 'PAID' && $order) {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true]);
        }

        if ((isset($payload['payment_id']) || isset($payload['callback_virtual_account_id'])) && $order) {
            $this->markOrderPaid($order, $payload);

            return response()->json(['success' => true]);
        }

        if (($payload['status'] ?? '') === 'EXPIRED' && $order) {
            $this->markOrderExpired($order, $payload);

            return response()->json(['success' => true]);
        }

        if (($payload['status'] ?? '') === 'FAILED' && $order) {
            $this->markOrderFailed($order, $payload);

            return response()->json(['success' => true]);
        }

        if (($payload['status'] ?? '') === 'CANCELLED' && $order) {
            $this->markOrderCancelled($order, $payload);

            return response()->json(['success' => true]);
        }

        if (($payload['status'] ?? '') === 'REFUNDED' && $order) {
            $this->markOrderRefunded($order, $payload);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => true, 'message' => 'Ignored event']);
    }

    // ─────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────

    /**
     * Mark an order as paid.
     */
    private function markOrderPaid(?Order $order, array $payload): void
    {
        if (! $order) {
            Log::warning('Xendit Webhook — order not found for paid event', [
                'external_id' => $payload['external_id'] ?? null,
            ]);

            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        if ($order->linked_order_id) {
            Order::where('id', $order->linked_order_id)->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
            ]);
        }
        Order::where('linked_order_id', $order->id)->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);

        Log::info("Xendit Webhook — order {$order->booking_code} marked as paid and confirmed");
    }

    /**
     * Mark an order as expired.
     */
    private function markOrderExpired(?Order $order, array $payload): void
    {
        if (! $order) {
            Log::warning('Xendit Webhook — order not found for expired event', [
                'external_id' => $payload['external_id'] ?? null,
            ]);

            return;
        }

        $order->update(['payment_status' => 'expired']);

        if ($order->linked_order_id) {
            Order::where('id', $order->linked_order_id)->update(['payment_status' => 'expired']);
        }
        Order::where('linked_order_id', $order->id)->update(['payment_status' => 'expired']);

        Log::info("Xendit Webhook — order {$order->booking_code} marked as expired");
    }

    /**
     * Mark an order as failed.
     */
    private function markOrderFailed(?Order $order, array $payload): void
    {
        if (! $order) {
            Log::warning('Xendit Webhook — order not found for failed event', [
                'external_id' => $payload['external_id'] ?? null,
            ]);

            return;
        }

        $order->update(['payment_status' => 'failed']);

        if ($order->linked_order_id) {
            Order::where('id', $order->linked_order_id)->update(['payment_status' => 'failed']);
        }
        Order::where('linked_order_id', $order->id)->update(['payment_status' => 'failed']);

        Log::info("Xendit Webhook — order {$order->booking_code} marked as failed", [
            'reason' => $payload['failure_code'] ?? null,
            'message' => $payload['failure_message'] ?? null,
        ]);
    }

    /**
     * Mark an order as cancelled.
     */
    private function markOrderCancelled(?Order $order, array $payload): void
    {
        if (! $order) {
            Log::warning('Xendit Webhook — order not found for cancelled event', [
                'external_id' => $payload['external_id'] ?? null,
            ]);

            return;
        }

        $order->update(['payment_status' => 'cancelled']);

        if ($order->linked_order_id) {
            Order::where('id', $order->linked_order_id)->update(['payment_status' => 'cancelled']);
        }
        Order::where('linked_order_id', $order->id)->update(['payment_status' => 'cancelled']);

        Log::info("Xendit Webhook — order {$order->booking_code} marked as cancelled");
    }

    /**
     * Mark an order as refunded.
     */
    private function markOrderRefunded(?Order $order, array $payload): void
    {
        if (! $order) {
            Log::warning('Xendit Webhook — order not found for refunded event', [
                'external_id' => $payload['external_id'] ?? null,
            ]);

            return;
        }

        $order->update(['payment_status' => 'refunded']);

        Log::info("Xendit Webhook — order {$order->booking_code} marked as refunded", [
            'refund_id' => $payload['refund_id'] ?? null,
        ]);
    }
}
