<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverArticle;
use App\Models\DriverReferral;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $driverId = Auth::guard('driver')->id();

        $articleCounts = DriverArticle::where('driver_id', $driverId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $referrals = DriverReferral::where('driver_id', $driverId);

        return Inertia::render('Driver/Dashboard', [
            'articleCounts' => [
                'pending' => $articleCounts['pending'] ?? 0,
                'approved' => $articleCounts['approved'] ?? 0,
                'rejected' => $articleCounts['rejected'] ?? 0,
            ],
            'earnings' => [
                'pending' => (clone $referrals)->where('status', 'pending')->sum('commission_amount'),
                'paid' => (clone $referrals)->where('status', 'paid')->sum('commission_amount'),
            ],
        ]);
    }
}
