<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\TaskRequest;
use App\Models\Mobile\Task;
use App\Models\Mobile\TaskCheckin;
use App\Models\Mobile\TaskProof;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TaskController
{
    public function index(Request $request): JsonResponse
    {
        $query = Task::with(['driver', 'admin'])->orderByDesc('created_at');

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('driver_id')) {
            $query->byDriver($request->driver_id);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('tanggal', [$request->date_from, $request->date_to]);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_tamu', 'like', "%{$search}%")
                    ->orWhere('kode_booking', 'like', "%{$search}%")
                    ->orWhere('driver_name', 'like', "%{$search}%");
            });
        }

        $tasks = $query->paginate($request->get('per_page', 15));

        return response()->json($tasks);
    }

    public function store(TaskRequest $request): JsonResponse
    {
        $task = Task::create(array_merge(
            $request->validated(),
            ['admin_id' => $request->user()->id]
        ));

        return response()->json($task, Response::HTTP_CREATED);
    }

    public function show(Task $task): JsonResponse
    {
        $task->load(['driver', 'admin', 'checkins', 'proofs']);

        return response()->json($task);
    }

    public function update(TaskRequest $request, Task $task): JsonResponse
    {
        $task->update($request->validated());

        return response()->json($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->update(['is_cancelled' => true, 'status' => 'Cancel']);

        return response()->json(['message' => 'Task cancelled successfully']);
    }

    public function myTasks(Request $request): JsonResponse
    {
        $query = Task::with(['admin'])
            ->byDriver($request->user()->id)
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam', 'asc');

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $tasks = $query->get();

        return response()->json(['data' => $tasks]);
    }

    public function checkin(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $checkin = TaskCheckin::create([
            'task_id' => $task->id,
            'driver_id' => $request->user()->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'checked_in_at' => now(),
        ]);

        if ($task->status === 'Pending') {
            $task->update(['status' => 'OTW']);
        }

        return response()->json([
            'message' => 'Check-in successful',
            'checkin' => $checkin,
            'task_status' => $task->status,
        ], Response::HTTP_CREATED);
    }

    public function checkout(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:berangkat,tiba'],
            'photo' => ['required', 'file', 'image', 'max:5120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $path = $request->file('photo')->store(
            "mobile/task-proofs/{$task->id}",
            'public'
        );

        $proof = TaskProof::create([
            'task_id' => $task->id,
            'type' => $request->type,
            'file_url' => "/storage/{$path}",
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'captured_at' => now(),
        ]);

        if ($request->type === 'berangkat' && $task->status === 'OTW') {
            $task->update(['status' => 'Tiba']);
        } elseif ($request->type === 'tiba' && $task->status === 'Tiba') {
            $task->update(['status' => 'Selesai']);
        }

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'proof' => $proof,
            'task_status' => $task->status,
        ], Response::HTTP_CREATED);
    }
}
