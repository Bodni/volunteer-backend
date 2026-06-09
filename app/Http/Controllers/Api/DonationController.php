<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;

class DonationController extends Controller
{
   public function index(Request $request)
{
    return response()->json(
        Donation::query()
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 5))
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
            'goal' => ['required', 'integer', 'min:0'],
            'raised' => ['required', 'integer', 'min:0'],
            'text' => ['nullable', 'string', 'max:5000'],
        ]);

        $donation = Donation::create($data);

        return response()->json($donation, 201);
    }

    public function update(Request $request, Donation $donation)
    {
        $data = $request->validate([
            'goal' => ['sometimes', 'integer', 'min:0'],
            'raised' => ['sometimes', 'integer', 'min:0'],
            'text' => ['nullable', 'string', 'max:5000'],
        ]);

        $donation->update($data);

        return response()->json($donation);
    }

    public function destroy(Donation $donation)
    {
        $donation->delete();

        return response()->json([
            'message' => 'Сбор удалён',
        ]);
    }
}