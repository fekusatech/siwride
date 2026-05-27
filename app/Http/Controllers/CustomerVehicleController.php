<?php

namespace App\Http\Controllers;

use App\Models\VehicleCategory;
use Inertia\Inertia;
use Inertia\Response;

class CustomerVehicleController extends Controller
{
    /**
     * Display a listing of the vehicle categories.
     */
    public function index(): Response
    {
        return Inertia::render('customer/vehicles', [
            'vehicleCategories' => VehicleCategory::all(),
        ]);
    }

    /**
     * Display the specified vehicle category details.
     */
    public function show(string $slug): Response
    {
        $category = VehicleCategory::where('slug', $slug)->firstOrFail();

        return Inertia::render('customer/vehicles/[slug]', [
            'vehicleCategory' => $category,
        ]);
    }
}
