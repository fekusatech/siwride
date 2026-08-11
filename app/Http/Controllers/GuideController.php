<?php

namespace App\Http\Controllers;

use App\Models\DriverArticle;
use App\Support\DriverReferralAttribution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuideController extends Controller
{
    /**
     * Display the public list of approved driver guides.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $guides = DriverArticle::with('driver', 'activity')
            ->where('status', 'approved')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->latest('published_at')
            ->get();

        return Inertia::render('customer/guides', [
            'guides' => $guides,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Display a single approved driver guide. Visiting this page is what
     * establishes referral attribution for its author (see
     * DriverReferralAttribution::remember()).
     */
    public function show(Request $request, DriverArticle $driverArticle): Response
    {
        abort_if($driverArticle->status !== 'approved', 404);

        $driverArticle->load('driver', 'activity');

        DriverReferralAttribution::remember($request, $driverArticle);

        return Inertia::render('customer/guide-detail', [
            'guide' => $driverArticle,
        ]);
    }
}
