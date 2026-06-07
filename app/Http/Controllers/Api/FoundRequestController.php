<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FoundRequest;
use Illuminate\Http\Request;

class FoundRequestController extends Controller
{
    public function index(Request $request)
{
    $query = FoundRequest::query()->latest('id');

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('city')) {
        $query->where('city', 'like', '%' . $request->city . '%');
    }

    return response()->json(
        $query->paginate($request->integer('per_page', 15))
    );
}

    public function store(Request $request)
{
    $data = $request->validate([
        'city' => ['required', 'string', 'max:255'],
        'address' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'photo' => ['nullable', 'image', 'max:5120'],
    ]);

    $data['status'] = 'new';

    if ($request->hasFile('photo')) {
    $path = $request->file('photo')->store('found-requests', 'public');
    $data['photo'] = '/storage/' . $path;
}

    $requestModel = \App\Models\FoundRequest::create($data);

    return response()->json($requestModel, 201);
}

    public function update(Request $request, FoundRequest $foundRequest)
    {
        $data = $request->validate([
            'city' => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $foundRequest->update($data);

        return response()->json($foundRequest);
    }

    public function destroy(FoundRequest $foundRequest)
    {
        $foundRequest->delete();

        return response()->json([
            'message' => 'Заявка удалена',
        ]);
    }
}