<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $activities = Activity::query()
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Activities/Index', [
            'activities' => $activities,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Activities/Create');
    }

    public function store(Request $request)
    {
        $request->merge(['price_per_pax' => $request->input('price_per_pax') ?: null]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:2048'],
            'href' => ['nullable', 'string', 'max:255'],
            'price_per_pax' => ['nullable', 'numeric', 'min:0'],
            'min_pax' => ['nullable', 'integer', 'min:1'],
            'max_pax' => ['nullable', 'integer', 'min:1'],
            'duration_label' => ['nullable', 'string', 'max:100'],
            'meeting_point' => ['nullable', 'string', 'max:255'],
            'includes' => ['nullable', 'string'],
            'excludes' => ['nullable', 'string'],
            'highlights' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['includes'] = $this->parseLines($validated['includes'] ?? null);
        $validated['excludes'] = $this->parseLines($validated['excludes'] ?? null);
        $validated['highlights'] = $this->parseLines($validated['highlights'] ?? null);

        // The gallery is one ordered photo list — whichever photo ends up
        // first (drag-and-drop order, set client-side) becomes the cover
        // image shown in listings.
        $gallery = $request->hasFile('gallery')
            ? collect($request->file('gallery'))->map(fn ($file) => $file->store('activities/gallery', 'public'))->all()
            : [];
        $validated['gallery'] = $gallery ?: null;
        $validated['image'] = $gallery[0] ?? null;

        Activity::create($validated);

        return redirect()->route('admin.activities.index')
            ->with('success', 'Activity created successfully.');
    }

    public function edit(Activity $activity)
    {
        return Inertia::render('Admin/Activities/Create', [
            'activity' => $activity,
        ]);
    }

    public function update(Request $request, Activity $activity)
    {
        $request->merge(['price_per_pax' => $request->input('price_per_pax') ?: null]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:2048'],
            'existing_gallery' => ['nullable', 'array'],
            'existing_gallery.*' => ['string'],
            'gallery_order' => ['nullable', 'array'],
            'gallery_order.*' => ['string'],
            'href' => ['nullable', 'string', 'max:255'],
            'price_per_pax' => ['nullable', 'numeric', 'min:0'],
            'min_pax' => ['nullable', 'integer', 'min:1'],
            'max_pax' => ['nullable', 'integer', 'min:1'],
            'duration_label' => ['nullable', 'string', 'max:100'],
            'meeting_point' => ['nullable', 'string', 'max:255'],
            'includes' => ['nullable', 'string'],
            'excludes' => ['nullable', 'string'],
            'highlights' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['includes'] = $this->parseLines($validated['includes'] ?? null);
        $validated['excludes'] = $this->parseLines($validated['excludes'] ?? null);
        $validated['highlights'] = $this->parseLines($validated['highlights'] ?? null);

        // Kept existing photos + newly uploaded ones, merged back into the
        // exact order the admin arranged them in (drag-and-drop client
        // side): gallery_order holds tokens like "e0" (existing_gallery[0])
        // or "n0" (gallery[0]) describing the final sequence.
        $currentGallery = $activity->gallery ?? [];
        $keepGallery = array_values(array_intersect($validated['existing_gallery'] ?? [], $currentGallery));

        foreach (array_diff($currentGallery, $keepGallery) as $removedPath) {
            Storage::disk('public')->delete($removedPath);
        }

        $newGallery = $request->hasFile('gallery')
            ? collect($request->file('gallery'))->map(fn ($file) => $file->store('activities/gallery', 'public'))->all()
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

        // Fallback if gallery_order wasn't sent for some reason.
        if (empty($gallery)) {
            $gallery = array_values(array_merge($keepGallery, $newGallery));
        }

        unset($validated['existing_gallery'], $validated['gallery_order']);
        $validated['gallery'] = $gallery ?: null;
        $validated['image'] = $gallery[0] ?? null;

        $activity->update($validated);

        return redirect()->route('admin.activities.index')
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(Activity $activity)
    {
        if ($activity->image && ! str_starts_with($activity->image, 'assets/')) {
            Storage::disk('public')->delete($activity->image);
        }

        foreach ($activity->gallery ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }

        $activity->delete();

        return redirect()->route('admin.activities.index')
            ->with('success', 'Activity deleted successfully.');
    }

    private function parseLines(?string $value): ?array
    {
        if (is_null($value) || trim($value) === '') {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode("\n", $value))));
    }
}
