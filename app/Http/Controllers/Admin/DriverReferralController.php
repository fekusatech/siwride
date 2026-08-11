<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverReferral;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverReferralController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $referrals = DriverReferral::with('driver', 'article', 'order', 'activityBooking')
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/DriverReferrals/Index', [
            'referrals' => $referrals,
            'filters' => $request->only(['status']),
        ]);
    }

    public function markPaid(DriverReferral $driverReferral)
    {
        abort_unless($driverReferral->status === DriverReferral::STATUS_PENDING, 422, 'Only pending referrals can be paid.');

        $driverReferral->update(['status' => DriverReferral::STATUS_PAID]);

        return back()->with('success', 'Referral marked as paid.');
    }
}
