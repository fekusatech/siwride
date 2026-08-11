<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverArticle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverArticleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $articles = DriverArticle::with('driver', 'activity')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('driver', fn ($q) => $q->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%"));
                });
            })
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/DriverArticles/Index', [
            'articles' => $articles,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(DriverArticle $driverArticle)
    {
        $driverArticle->load('driver', 'activity');

        return Inertia::render('Admin/DriverArticles/Show', [
            'article' => $driverArticle,
        ]);
    }

    public function updateStatus(Request $request, DriverArticle $driverArticle)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['status'] === DriverArticle::STATUS_APPROVED) {
            $validated['published_at'] ??= $driverArticle->published_at ?? now();
            $validated['rejection_reason'] = null;
        } elseif ($validated['status'] === DriverArticle::STATUS_REJECTED) {
            $validated['published_at'] = null;
        } else {
            $validated['published_at'] = null;
            $validated['rejection_reason'] = null;
        }

        $driverArticle->update($validated);

        return back()->with('success', 'Article status updated.');
    }
}
