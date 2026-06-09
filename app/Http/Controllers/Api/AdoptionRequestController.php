<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdoptionRequest;
use Illuminate\Http\Request;

class AdoptionRequestController extends Controller
{
    public function index(Request $request)
{
    $query = AdoptionRequest::query()->latest('id');

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('animal_name', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
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
            'animal_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'max:255'],
        ]);

        $data['status'] = $data['status'] ?? 'new';

        $item = AdoptionRequest::create($data);

        return response()->json($item, 201);
    }

    public function update(Request $request, AdoptionRequest $adoptionRequest)
    {
        $data = $request->validate([
            'animal_name' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'string', 'max:255'],
        ]);

        $adoptionRequest->update($data);

        return response()->json($adoptionRequest);
    }

    public function destroy(AdoptionRequest $adoptionRequest)
    {
        $adoptionRequest->delete();

        return response()->json([
            'message' => 'Заявка удалена',
        ]);
    }
}