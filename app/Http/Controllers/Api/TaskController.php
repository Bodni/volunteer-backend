<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
{
    $query = Task::query()
        ->with('assignee:id,name,email,role,volunteer_status')
        ->latest('id');

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('assigned_to')) {
        $query->where('assigned_to', $request->assigned_to);
    }

    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    return response()->json(
        $query->paginate($request->integer('per_page', 15))
    );
}
private function ensureAdmin(Request $request)
{
    if (!$request->user() || $request->user()->role !== 'admin') {
        abort(403, 'Доступ запрещён');
    }
}

    public function store(Request $request)
    {
        $this->ensureAdmin($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'status' => ['nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'points' => ['nullable', 'integer'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);
        if ($request->hasFile('photo')) {
    $path = $request->file('photo')->store('tasks', 'public');
    $data['photo'] = '/storage/' . $path;
}

        $data['status'] = $data['status'] ?? 'open';
        $data['points'] = $data['points'] ?? 10;

        $task = Task::create($data);

        $this->syncVolunteerStatus($task->assigned_to);

        return response()->json($task->fresh('assignee'), 201);
    }

    public function update(Request $request, Task $task)
{
    $oldAssignedTo = $task->assigned_to;
    $oldStatus = $task->status;

    $data = $request->validate([
        'title' => ['sometimes', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'status' => ['sometimes', 'string', 'max:255'],
        'assigned_to' => ['nullable', 'exists:users,id'],
        'points' => ['nullable', 'integer'],
        'photo' => ['nullable', 'image', 'max:5120'],
    ]);

    if ($request->hasFile('photo')) {
        $path = $request->file('photo')->store('tasks', 'public');
        $data['photo'] = '/storage/' . $path;
    }

    $task->update($data);

    if (
        $oldStatus === 'done_pending' &&
        $task->status === 'done' &&
        $task->assigned_to
    ) {
        $user = User::query()->find($task->assigned_to);

        if ($user) {
            $user->points = (int) $user->points + (int) ($task->points ?? 10);
            $user->save();
        }
    }

    if ($task->status === 'open' && $task->assigned_to) {
        $task->assigned_to = null;
        $task->save();
    }

    $this->syncVolunteerStatus($oldAssignedTo);
    $this->syncVolunteerStatus($task->assigned_to);

    return response()->json($task->fresh('assignee'));
}

    public function destroy(Task $task)
    {
        $assignedTo = $task->assigned_to;

        $task->delete();

        $this->syncVolunteerStatus($assignedTo);

        return response()->json([
            'message' => 'Задача удалена',
        ]);
    }

    private function syncVolunteerStatus(?int $userId): void
    {
        if (!$userId) {
            return;
        }

        $user = User::query()
            ->where('id', $userId)
            ->where('role', 'volunteer')
            ->first();

        if (!$user) {
            return;
        }

        $hasInProgress = Task::query()
            ->where('assigned_to', $user->id)
            ->where('status', 'in_progress')
            ->exists();

        $user->volunteer_status = $hasInProgress ? 'busy' : 'free';
        $user->save();
    }

    public function show(\App\Models\Task $task)
{
    return response()->json($task);
}
}