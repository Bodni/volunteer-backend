<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RewardController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();

    if (!$user || !in_array($user->role, ['admin', 'volunteer'])) {
        return response()->json([
            'message' => 'Доступ запрещён',
        ], 403);
    }

    $query = Reward::query()->latest('id');

    if ($user->role !== 'admin') {
        $query->where('is_active', true);
    }

    if ($request->filled('category')) {
        $query->where('category', $request->category);
    }

    return response()->json(
        $query->paginate($request->integer('per_page', 12))
    );
}

    public function show(Request $request, Reward $reward)
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['admin', 'volunteer'])) {
            return response()->json([
                'message' => 'Доступ запрещён',
            ], 403);
        }

        if ($user->role !== 'admin' && !$reward->is_active) {
            return response()->json([
                'message' => 'Награда недоступна',
            ], 404);
        }

        return response()->json($reward);
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
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Доступ запрещён',
            ], 403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'price_points' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['category'] = $data['category'] ?? 'other';
        $data['is_active'] = $data['is_active'] ?? true;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('rewards', 'public');
            $data['image'] = '/storage/' . $path;
        }

        $reward = Reward::create($data);

        return response()->json($reward, 201);
    }

    public function update(Request $request, Reward $reward)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Доступ запрещён',
            ], 403);
        }

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'max:5120'],
            'price_points' => ['sometimes', 'required', 'integer', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($reward->image && str_starts_with($reward->image, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $reward->image));
            }

            $path = $request->file('image')->store('rewards', 'public');
            $data['image'] = '/storage/' . $path;
        }

        $reward->update($data);

        return response()->json($reward->fresh());
    }

    public function destroy(Request $request, Reward $reward)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Доступ запрещён',
            ], 403);
        }

        if ($reward->image && str_starts_with($reward->image, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $reward->image));
        }

        $reward->delete();

        return response()->json([
            'message' => 'Награда удалена',
        ]);
    }
}