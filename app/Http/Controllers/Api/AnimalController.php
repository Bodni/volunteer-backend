<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnimalController extends Controller
{
    public function index(Request $request)
{
    $query = Animal::query()
        ->select([
            'id',
            'name',
            'species',
            'age',
            'city',
            'status',
            'description',
            'photo',
            'created_at',
        ])
        ->latest('id');

    if ($request->filled('status')) {
        if ($request->status !== 'all') {
            $query->where('status', $request->status);
        }
    } else {
        // По умолчанию скрываем пристроенных
        $query->where('status', '!=', 'adopted');
    }

    if ($request->filled('species')) {
        $query->where('species', $request->species);
    }

    if ($request->filled('city')) {
        $query->where('city', 'like', '%' . $request->city . '%');
    }

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('species', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%");
        });
    }

    return response()->json(
        $query->paginate($request->integer('per_page', 9))
    );
}

    public function show(Animal $animal)
    {
        return response()->json($animal);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'species' => ['required', 'string', 'max:255'],
            'age' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:looking_home,treatment,adopted'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $data['status'] = $data['status'] ?? 'looking_home';

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('animals', 'public');
            $data['photo'] = '/storage/' . $path;
        }

        $animal = Animal::create($data);

        return response()->json($animal, 201);
    }

    public function update(Request $request, Animal $animal)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'species' => ['sometimes', 'required', 'string', 'max:255'],
            'age' => ['sometimes', 'required', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'in:looking_home,treatment,adopted'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            if ($animal->photo && str_starts_with($animal->photo, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $animal->photo));
            }

            $path = $request->file('photo')->store('animals', 'public');
            $data['photo'] = '/storage/' . $path;
        }

        $animal->update($data);

        return response()->json($animal->fresh());
    }

    public function destroy(Animal $animal)
    {
        if ($animal->photo && str_starts_with($animal->photo, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $animal->photo));
        }

        $animal->delete();

        return response()->json([
            'message' => 'Животное удалено',
        ]);
    }
}