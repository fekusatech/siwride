<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VoucherController extends Controller
{
    public function index(Request $request): Response
    {
        $now = now();

        return Inertia::render('Admin/Promos/Index', [
            'vouchers' => Voucher::query()
                ->withCount('redemptions')
                ->when($request->search, function ($query, string $search) {
                    $query->where('code', 'like', "%{$search}%");
                })
                ->when($request->filter === 'active', function ($query) use ($now) {
                    $query->where('is_active', true)
                        ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now))
                        ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>', $now));
                })
                ->when($request->filter === 'expired', function ($query) use ($now) {
                    $query->where(fn ($q) => $q->where('is_active', false)
                        ->orWhere('valid_until', '<=', $now));
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search', 'filter']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Promos/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Voucher::create($validated);

        return redirect()->route('admin.promos.index')
            ->with('success', 'Voucher created successfully.');
    }

    public function show(Voucher $voucher): Response
    {
        $stats = [
            'total_redemptions' => $voucher->redemptions()->count(),
            'total_discount' => (float) $voucher->redemptions()->sum('discount_amount'),
        ];

        return Inertia::render('Admin/Promos/Show', [
            'voucher' => $voucher->loadCount('redemptions'),
            'stats' => $stats,
            'redemptions' => $voucher->redemptions()
                ->with('booking:id,booking_code,customer_name,booking_date')
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }

    public function edit(Voucher $voucher): Response
    {
        return Inertia::render('Admin/Promos/Create', [
            'voucher' => $voucher,
        ]);
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $validated = $this->validated($request, $voucher);

        $voucher->update($validated);

        return redirect()->route('admin.promos.index')
            ->with('success', 'Voucher updated successfully.');
    }

    public function toggleActive(Voucher $voucher): RedirectResponse
    {
        $voucher->update(['is_active' => ! $voucher->is_active]);

        return redirect()->back()
            ->with('success', $voucher->is_active ? 'Voucher activated.' : 'Voucher deactivated.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        if ($voucher->redemptions()->exists()) {
            return redirect()->back()
                ->with('error', 'Voucher dengan redeem tidak bisa dihapus. Nonaktifkan saja.');
        }

        $voucher->delete();

        return redirect()->route('admin.promos.index')
            ->with('success', 'Voucher deleted successfully.');
    }

    private function validated(Request $request, ?Voucher $voucher = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'regex:/^[A-Z0-9-]{4,20}$/',
                Rule::unique('vouchers', 'code')->ignore($voucher),
            ],
            'type' => ['required', Rule::in([Voucher::TYPE_PERCENT, Voucher::TYPE_FIXED])],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_spend' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validated['type'] === Voucher::TYPE_PERCENT && $validated['value'] > 100) {
            throw ValidationException::withMessages([
                'value' => 'Persentase diskon tidak boleh lebih dari 100.',
            ]);
        }

        $validated['code'] = strtoupper($validated['code']);
        $validated['min_spend'] = $validated['min_spend'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
