<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PartnerApplyController extends Controller
{
    /**
     * Show the public "become a partner" landing + application form.
     */
    public function create(): Response
    {
        return Inertia::render('Public/BecomePartner');
    }

    /**
     * Store a newly submitted partner application (stays pending until admin approves).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:travel,guide'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(Partner::class)],
            'phone' => ['required', 'string', 'max:50', Rule::unique(Partner::class)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'description' => ['nullable', 'string', 'max:2000'],
            'nik' => ['nullable', 'string', 'max:20'],
            'nik_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $partner = DB::transaction(function () use ($validated, $request) {
            if ($request->hasFile('nik_image')) {
                $validated['nik_image'] = $request->file('nik_image')->store('partners/nik', 'local');
            }

            $validated['slug'] = $this->uniqueSlug($validated['name']);

            return Partner::create($validated);
        });

        return redirect()->route('become-partner')
            ->with('status', "Thank you, {$partner->name}! Your application has been received and is waiting for admin approval.");
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'partner';
        $slug = $base;
        $counter = 2;

        while (Partner::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
