<?php

namespace App\Http\Controllers;

use App\Models\DriverService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DriverServiceListController extends Controller
{
    /**
     * Display the public list of approved, bookable driver services.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search', '');

        $services = DriverService::with('driver')
            ->where('status', DriverService::STATUS_APPROVED)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest('published_at')
            ->get();

        return Inertia::render('customer/driver-services', [
            'services' => $services,
            'filters' => ['search' => $search],
        ]);
    }
}
