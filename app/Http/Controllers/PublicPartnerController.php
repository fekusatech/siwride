<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPartnerController extends Controller
{
    /**
     * Public catalog of approved partners.
     */
    public function index(Request $request): Response
    {
        $type = $request->query('type');
        $search = $request->query('search');

        $partners = Partner::query()
            ->active()
            ->type(in_array($type, [Partner::TYPE_TRAVEL, Partner::TYPE_GUIDE], true) ? $type : null)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        return Inertia::render('Public/Partners', [
            'partners' => $partners,
            'filters' => [
                'type' => in_array($type, [Partner::TYPE_TRAVEL, Partner::TYPE_GUIDE], true) ? $type : '',
                'search' => $search ?? '',
            ],
        ]);
    }

    /**
     * Public profile for a single approved partner.
     */
    public function show(Partner $partner): Response
    {
        abort_unless($partner->isActive(), 404);

        return Inertia::render('Public/PartnerShow', [
            'partner' => $partner,
        ]);
    }
}
