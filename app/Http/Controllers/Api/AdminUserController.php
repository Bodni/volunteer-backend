<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminUserController extends Controller
{
    public function index(Request $request)
{
    $query = User::query()
        ->select([
            'id',
            'name',
            'email',
            'role',
            'points',
            'avatar',
            'volunteer_status',
            'created_at',
        ])
        ->orderBy('id');

    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,volunteer'],
        ]);

        $user = User::create([
    'name' => $data['name'],
    'email' => $data['email'],
    'password' => Hash::make($data['password']),
    'role' => $data['role'],
    'points' => 0,
    'avatar' => '',
    'volunteer_status' => 'free',
]);

        return response()->json($user, 201);
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return response()->json([
                'message' => 'Админа удалять нельзя',
            ], 422);
        }

        Task::where('assigned_to', $user->id)->update([
            'assigned_to' => null,
            'status' => 'open',
        ]);

        $user->delete();

        return response()->json([
            'message' => 'Пользователь удалён',
        ]);
    }

    public function resetPassword(Request $request, User $user)
{
    $data = $request->validate([
        'password' => ['required', 'string', 'min:6'],
    ]);

    $user->update([
        'password' => Hash::make($data['password']),
    ]);

    return response()->json([
        'message' => 'Пароль обновлён',
    ]);
}

    public function addPoints(Request $request, User $user)
    {
        $data = $request->validate([
            'delta' => ['required', 'integer'],
        ]);

        $user->points = (int) $user->points + (int) $data['delta'];
        $user->save();

        return response()->json($user);
    }

public function updateAvatar(Request $request, User $user)
{
    $data = $request->validate([
        'avatar' => ['required', 'image', 'max:5120'],
    ]);

    if ($user->avatar && str_starts_with($user->avatar, '/storage/')) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete(
            str_replace('/storage/', '', $user->avatar)
        );
    }

    $path = $request->file('avatar')->store('avatars', 'public');

    $user->update([
        'avatar' => '/storage/' . $path,
    ]);

    return response()->json($user->fresh());
}
    public function bestVolunteer()
    {
        $user = User::where('role', 'volunteer')
            ->orderByDesc('points')
            ->first();

        return response()->json($user);
    }

    public function topVolunteers()
{
    return \App\Models\User::query()
        ->where('role', 'volunteer')
        ->orderByDesc('points')
        ->limit(3)
        ->get([
            'id',
            'name',
            'points',
            'avatar',
        ]);
}
}