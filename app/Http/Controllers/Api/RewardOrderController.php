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

    if ($user->role === 'volunteer') {
        $query->where('user_id', $user->id);
    } elseif ($user->role !== 'admin') {
        return response()->json([
            'message' => 'Доступ запрещён',
        ], 403);
    }

    return response()->json(
        $query->paginate($request->integer('per_page', 15))
    );
}

    public function store(Request $request)
    {
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
}