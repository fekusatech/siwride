<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\DriverArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = DriverArticle::where('driver_id', Auth::guard('driver')->id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Driver/Articles/Index', [
            'articles' => $articles,
        ]);
    }

    public function create()
    {
        return Inertia::render('Driver/Articles/Create', [
            'activities' => Activity::select('id', 'title')->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $validated['driver_id'] = Auth::guard('driver')->id();
        $validated['slug'] = $this->uniqueSlug($validated['title']);
        $validated['status'] = DriverArticle::STATUS_PENDING;

        $gallery = $request->hasFile('gallery')
            ? collect($request->file('gallery'))->map(fn ($file) => $file->store('driver-articles', 'public'))->all()
            : [];
        $validated['gallery'] = $gallery ?: null;
        $validated['image'] = $gallery[0] ?? null;

        DriverArticle::create($validated);

        return redirect()->route('driver.articles.index')->with('success', 'Article submitted for review.');
    }

    public function edit(DriverArticle $article)
    {
        $this->authorizeOwnArticle($article);

        return Inertia::render('Driver/Articles/Create', [
            'article' => $article,
            'activities' => Activity::select('id', 'title')->orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, DriverArticle $article)
    {
        $this->authorizeOwnArticle($article);

        $validated = $this->validated($request);

        if ($validated['title'] !== $article->title) {
            $validated['slug'] = $this->uniqueSlug($validated['title'], $article->id);
        }

        if (in_array($article->status, [DriverArticle::STATUS_APPROVED, DriverArticle::STATUS_REJECTED], true)) {
            $validated['status'] = DriverArticle::STATUS_PENDING;
            $validated['published_at'] = null;
            $validated['rejection_reason'] = null;
        }

        $currentGallery = $article->gallery ?? [];
        $keepGallery = array_values(array_intersect($request->input('existing_gallery', []), $currentGallery));

        foreach (array_diff($currentGallery, $keepGallery) as $removedPath) {
            Storage::disk('public')->delete($removedPath);
        }

        $newGallery = $request->hasFile('gallery')
            ? collect($request->file('gallery'))->map(fn ($file) => $file->store('driver-articles', 'public'))->all()
            : [];

        $order = $request->input('gallery_order', []);
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

        $validated['gallery'] = $gallery ?: null;
        $validated['image'] = $gallery[0] ?? null;

        $article->update($validated);

        return redirect()->route('driver.articles.index')->with('success', 'Article updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'activity_id' => ['nullable', 'exists:activities,id'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:2048'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (
            DriverArticle::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    private function authorizeOwnArticle(DriverArticle $article): void
    {
        abort_unless($article->driver_id === Auth::guard('driver')->id(), 403);
    }
}
