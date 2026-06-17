<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class VolunteerController extends Controller
{
    public function index()
    {
        $volunteers = User::query()
            ->where('role', 'volunteer')
->where('is_banned', false)
->latest('id')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $user->volunteer_status ?: 'free',
            ])
            ->values();

        return response()->json($volunteers);
    }
}