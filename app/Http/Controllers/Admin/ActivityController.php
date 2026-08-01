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
            'image' => ['nullable', 'image', 'max:2048'],
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

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('activities', 'public');
        }

        if ($request->hasFile('gallery')) {
            $validated['gallery'] = collect($request->file('gallery'))
                ->map(fn ($file) => $file->store('activities/gallery', 'public'))
                ->all();
        }

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
            'image' => ['nullable', 'image', 'max:2048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:2048'],
            'existing_gallery' => ['nullable', 'array'],
            'existing_gallery.*' => ['string'],
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

        if ($request->hasFile('image')) {
            if ($activity->image && ! str_starts_with($activity->image, 'assets/')) {
                Storage::disk('public')->delete($activity->image);
            }
            $validated['image'] = $request->file('image')->store('activities', 'public');
        }

        $currentGallery = $activity->gallery ?? [];
        $keepGallery = array_intersect($validated['existing_gallery'] ?? [], $currentGallery);
        unset($validated['existing_gallery']);

        foreach (array_diff($currentGallery, $keepGallery) as $removedPath) {
            Storage::disk('public')->delete($removedPath);
        }

        $newGallery = [];
        if ($request->hasFile('gallery')) {
            $newGallery = collect($request->file('gallery'))
                ->map(fn ($file) => $file->store('activities/gallery', 'public'))
                ->all();
        }

        $validated['gallery'] = array_values(array_merge($keepGallery, $newGallery)) ?: null;

        $activity->update($validated);

        return redirect()->route('admin.activities.index')
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(Activity $activity)
    {
        if ($activity->image && ! str_starts_with($activity->image, 'assets/')) {
            Storage::disk('public')->delete($activity->image);
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
