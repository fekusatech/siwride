<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Transaction;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Xendit\Configuration;
use Xendit\PaymentRequest\PaymentMethodReusability;
use Xendit\PaymentRequest\PaymentMethodType;
use Xendit\PaymentRequest\PaymentRequestApi;
use Xendit\PaymentRequest\PaymentRequestParameters;
use Xendit\PaymentRequest\QRCodeChannelCode;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => Transaction::query()
                ->when($request->search, fn ($query, $search) => $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%"))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Transactions/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $transaction = Transaction::create([
            'code' => $this->generateUniqueCode(),
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'currency' => 'IDR',
            'status' => Transaction::STATUS_PENDING,
            'created_by' => $request->user()->id,
        ]);

        try {
            $result = $this->createQrisPaymentRequest($transaction);
            $channelProperties = $result->getPaymentMethod()->getQrCode()->getChannelProperties();

            $transaction->update([
                'xendit_payment_request_id' => $result->getId(),
                'qr_string' => $channelProperties->getQrString(),
                'expires_at' => $channelProperties->getExpiresAt(),
            ]);
        } catch (\Throwable $e) {
            $transaction->delete();

            return redirect()->route('admin.transactions.create')
                ->with('error', 'Failed to create QRIS payment: '.$e->getMessage());
        }

        return redirect()->route('admin.transactions.show', $transaction);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'TRX-'.strtoupper(Str::random(10));
        } while (Transaction::where('code', $code)->exists());

        return $code;
    }

    public function show(Transaction $transaction): Response
    {
        return Inertia::render('Admin/Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Lightweight JSON status check, polled by the show page while a payment is pending.
     */
    public function status(Transaction $transaction)
    {
        return response()->json([
            'status' => $transaction->status,
            'paid_at' => $transaction->paid_at,
        ]);
    }

    private function createQrisPaymentRequest(Transaction $transaction): \Xendit\PaymentRequest\PaymentRequest
    {
        $xenditKey = Setting::getValue('xendit_secret_key') ?: config('services.xendit.secret_key');
        Configuration::setXenditKey($xenditKey);

        $guzzleClient = new Client([
            'verify' => ! app()->environment('local'),
        ]);

        $apiInstance = new PaymentRequestApi($guzzleClient);

        return $apiInstance->createPaymentRequest(payment_request_parameters: new PaymentRequestParameters([
            'reference_id' => $transaction->code,
            'amount' => (float) $transaction->amount,
            'currency' => $transaction->currency,
            'description' => $transaction->description,
            'payment_method' => [
                'type' => PaymentMethodType::QR_CODE,
                'reusability' => PaymentMethodReusability::ONE_TIME_USE,
                'qr_code' => [
                    'channel_code' => QRCodeChannelCode::QRIS,
                ],
            ],
        ]));
    }
}
