<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhooks from Xendit
     */
    public function xendit(Request $request)
    {
        // 1. Verify Webhook Token from Xendit
        $xenditToken = config('services.xendit.webhook_token');
        $callbackToken = $request->header('x-callback-token');

        if ($xenditToken && $callbackToken !== $xenditToken) {
            Log::warning('Invalid Xendit Webhook Token', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Invalid token'], 403);
        }

        $payload = $request->all();
        Log::info('Xendit Webhook Received', $payload);

        // 2. Extract external_id
        $externalId = $payload['external_id'] ?? null;
        if (!$externalId) {
            return response()->json(['error' => 'Missing external_id'], 400);
        }

        // external_id is formatted as: BOOKINGCODE_TIMESTAMP
        $parts = explode('_', $externalId);
        $bookingCode = $parts[0];

        $order = Order::where('booking_code', $bookingCode)->first();
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // 3. Process Invoice Webhook (E-wallets, Retail)
        if (isset($payload['status']) && $payload['status'] === 'PAID') {
            $order->update([
                'payment_status' => 'paid',
            ]);
            return response()->json(['success' => true]);
        }

        // 4. Process FVA (Virtual Account) Webhook
        // FVA webhook structure usually doesn't have a 'status' field, it just means it was paid.
        // It has 'amount', 'bank_code', 'payment_id'
        if (isset($payload['payment_id']) || isset($payload['callback_virtual_account_id'])) {
            $order->update([
                'payment_status' => 'paid',
            ]);
            return response()->json(['success' => true]);
        }

        // If status EXPIRED
        if (isset($payload['status']) && $payload['status'] === 'EXPIRED') {
            $order->update([
                'payment_status' => 'expired',
            ]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => true, 'message' => 'Ignored event']);
    }
}
