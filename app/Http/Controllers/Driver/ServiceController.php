<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index()
    {
        $services = DriverService::where('driver_id', Auth::guard('driver')->id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Driver/Services/Index', [
            'services' => $services,
        ]);
    }

    public function create()
    {
        return Inertia::render('Driver/Services/Create');
    }

    public function store(Request $request)
    {
        $request->merge(['price_per_pax' => $request->input('price_per_pax') ?: null]);

        $validated = $this->validated($request);

        $validated['driver_id'] = Auth::guard('driver')->id();
        $validated['slug'] = $this->uniqueSlug($validated['title']);
        $validated['status'] = DriverService::STATUS_PENDING;
        $validated['includes'] = $this->parseLines($validated['includes'] ?? null);
        $validated['excludes'] = $this->parseLines($validated['excludes'] ?? null);
        $validated['highlights'] = $this->parseLines($validated['highlights'] ?? null);

        $gallery = $request->hasFile('gallery')
            ? collect($request->file('gallery'))->map(fn ($file) => $file->store('driver-services', 'public'))->all()
            : [];
        $validated['gallery'] = $gallery ?: null;
        $validated['image'] = $gallery[0] ?? null;

        DriverService::create($validated);

        return redirect()->route('driver.services.index')->with('success', 'Service submitted for review.');
    }

    public function edit(DriverService $service)
    {
        $this->authorizeOwnService($service);

        return Inertia::render('Driver/Services/Create', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, DriverService $service)
    {
        $this->authorizeOwnService($service);

        $request->merge(['price_per_pax' => $request->input('price_per_pax') ?: null]);

        $validated = $this->validated($request, true);

        if ($validated['title'] !== $service->title) {
            $validated['slug'] = $this->uniqueSlug($validated['title'], $service->id);
        }

        if (in_array($service->status, [DriverService::STATUS_APPROVED, DriverService::STATUS_REJECTED], true)) {
            $validated['status'] = DriverService::STATUS_PENDING;
            $validated['published_at'] = null;
            $validated['rejection_reason'] = null;
        }

        $validated['includes'] = $this->parseLines($validated['includes'] ?? null);
        $validated['excludes'] = $this->parseLines($validated['excludes'] ?? null);
        $validated['highlights'] = $this->parseLines($validated['highlights'] ?? null);

        $currentGallery = $service->gallery ?? [];
        $keepGallery = array_values(array_intersect($validated['existing_gallery'] ?? [], $currentGallery));

        foreach (array_diff($currentGallery, $keepGallery) as $removedPath) {
            Storage::disk('public')->delete($removedPath);
        }

        $newGallery = $request->hasFile('gallery')
            ? collect($request->file('gallery'))->map(fn ($file) => $file->store('driver-services', 'public'))->all()
            : [];

        $order = $validated['gallery_order'] ?? [];
        $gallery = collect($order)
            ->map(function (string $token) use ($keepGallery, $newGallery) {
                $index = (int) substr($token, 1);

                return $token[0] === 'e' ? ($keepGallery[$index] ?? null) : ($newGallery[$index] ?? null);
            })
            ->filter()
            ->values()
            ->all();

        if (empty($gallery)) {
            $gallery = array_values(array_merge($keepGallery, $newGallery));
        }

        unset($validated['existing_gallery'], $validated['gallery_order']);
        $validated['gallery'] = $gallery ?: null;
        $validated['image'] = $gallery[0] ?? null;

        $service->update($validated);

        return redirect()->route('driver.services.index')->with('success', 'Service updated.');
    }

    private function validated(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:2048'],
            'price_per_pax' => ['nullable', 'numeric', 'min:0'],
            'min_pax' => ['nullable', 'integer', 'min:1'],
            'max_pax' => ['nullable', 'integer', 'min:1'],
            'duration_label' => ['nullable', 'string', 'max:100'],
            'meeting_point' => ['nullable', 'string', 'max:255'],
            'includes' => ['nullable', 'string'],
            'excludes' => ['nullable', 'string'],
            'highlights' => ['nullable', 'string'],
        ];

        if ($isUpdate) {
            $rules['existing_gallery'] = ['nullable', 'array'];
            $rules['existing_gallery.*'] = ['string'];
            $rules['gallery_order'] = ['nullable', 'array'];
            $rules['gallery_order.*'] = ['string'];
        }

        return $request->validate($rules);
    }

    private function parseLines(?string $value): ?array
    {
        if (is_null($value) || trim($value) === '') {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode("\n", $value))));
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (
            DriverService::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function authorizeOwnService(DriverService $service): void
    {
        abort_unless($service->driver_id === Auth::guard('driver')->id(), 403);
    }
}
