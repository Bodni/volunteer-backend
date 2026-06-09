<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardOrderController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Не авторизован',
        ], 401);
    }

    $query = RewardOrder::query()
        ->with([
            'user:id,name,email,role,points',
            'reward:id,title,partner_name,image,price_points,stock',
        ])
        ->latest('id');

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

  if ($user->role === 'admin' && $request->boolean('all')) {
    // админ может получить все обмены только явно
} else {
    $query->where('user_id', $user->id);
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
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Не авторизован',
            ], 401);
        }

        if ($user->role !== 'volunteer') {
            return response()->json([
                'message' => 'Обменивать баллы может только волонтёр',
            ], 403);
        }

        $data = $request->validate([
            'reward_id' => ['required', 'exists:rewards,id'],
        ]);

        $createdOrder = DB::transaction(function () use ($user, $data) {
            $freshUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $reward = Reward::query()->lockForUpdate()->findOrFail($data['reward_id']);

            if (!$reward->is_active) {
                return response()->json([
                    'message' => 'Награда недоступна',
                ], 422);
            }

            if ($reward->stock <= 0) {
                return response()->json([
                    'message' => 'Товар закончился',
                ], 422);
            }

            if ((int) $freshUser->points < (int) $reward->price_points) {
                return response()->json([
                    'message' => 'Недостаточно баллов',
                ], 422);
            }
            $user->decrement('points', $reward->price_points);
$reward->decrement('stock');

            $freshUser->points = (int) $freshUser->points - (int) $reward->price_points;
            $freshUser->save();

            $reward->stock = (int) $reward->stock - 1;
            $reward->save();

            return RewardOrder::create([
                'user_id' => $freshUser->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->price_points,
                'status' => 'new',
            ]);
        });

        if ($createdOrder instanceof \Illuminate\Http\JsonResponse) {
            return $createdOrder;
        }

        return response()->json(
            $createdOrder->load([
                'user:id,name,email,role,points',
                'reward:id,title,partner_name,image,price_points,stock',
            ]),
            201
        );
    }

    public function update(Request $request, RewardOrder $rewardOrder)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Доступ запрещён',
            ], 403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:new,approved,rejected,done'],
            'comment' => ['nullable', 'string'],
        ]);

        $rewardOrder->update($data);

        return response()->json(
            $rewardOrder->fresh()->load([
                'user:id,name,email,role,points',
                'reward:id,title,partner_name,image,price_points,stock',
            ])
        );
    }

    public function destroy(Request $request, RewardOrder $rewardOrder)
{
    $user = $request->user();

    if (!$user || $user->role !== 'admin') {
        return response()->json([
            'message' => 'Доступ запрещён',
        ], 403);
    }

    $rewardOrder->delete();

    return response()->json([
        'message' => 'Заявка на обмен удалена',
    ]);
}
}