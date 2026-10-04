<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Inertia\Inertia;
use Inertia\Response;

class PublicPromoController extends Controller
{
    public function index(): Response
    {
        $promos = Voucher::query()
            ->currentlyAvailable()
            ->orderByRaw('valid_until is null, valid_until')
            ->get()
            ->map(fn (Voucher $voucher): array => [
                'code' => $voucher->code,
                'title' => $voucher->title,
                'type' => $voucher->type,
                'value' => (float) $voucher->value,
                'min_spend' => (float) $voucher->min_spend,
                'max_discount' => $voucher->max_discount === null ? null : (float) $voucher->max_discount,
                'valid_until' => $voucher->valid_until?->toIso8601String(),
            ]);

        return Inertia::render('customer/promos', ['promos' => $promos]);
    }

    public function show(string $code): Response
    {
        $voucher = Voucher::query()->currentlyAvailable()->where('code', $code)->firstOrFail();

        return Inertia::render('customer/promo-detail', [
            'promo' => [
                'code' => $voucher->code,
                'title' => $voucher->title,
                'description' => $voucher->description,
                'type' => $voucher->type,
                'value' => (float) $voucher->value,
                'min_spend' => (float) $voucher->min_spend,
                'max_discount' => $voucher->max_discount === null ? null : (float) $voucher->max_discount,
                'valid_until' => $voucher->valid_until?->toIso8601String(),
            ],
        ]);
    }
}
