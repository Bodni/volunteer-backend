<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
{
    $query = News::query()
        ->select([
            'id',
            'title',
            'image',
            'published_at',
            'created_at',
        ])
        ->orderByRaw('published_at IS NULL')
->orderByDesc('published_at')
->orderByDesc('id');

    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    return response()->json(
        $query->paginate($request->integer('per_page', 6))
    );
    $perPage = min($request->integer('per_page', 6), 1000);

return response()->json(
    $query->paginate($perPage)
);
}

    public function show(News $news)
    {
        return response()->json($news);
    }

    public function store(Request $request)
{

$this->ensureAdmin($request);
    $data = $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'image' => ['nullable', 'image', 'max:2048'], // <= файл
        'published_at' => ['nullable', 'date'],
        'text' => ['nullable', 'string', 'max:5000'],
    ]);

    if ($request->hasFile('image')) {
    $path = $request->file('image')->store('news', 'public');
    $data['image'] = '/storage/' . $path;
}

    $news = News::create($data);

    return response()->json($news, 201);
}

  private function ensureAdmin(Request $request)
{
    if (!$request->user() || $request->user()->role !== 'admin') {
        abort(403, 'Доступ запрещён');
    }
}

    public function update(Request $request, News $news)
{
    $data = $request->validate([
        'title' => ['sometimes', 'string', 'max:255'],
        'image' => ['nullable', 'image', 'max:2048'],
        'published_at' => ['nullable', 'date'],
        'text' => ['nullable', 'string', 'max:5000'],
    ]);

   if ($request->hasFile('image')) {
    $path = $request->file('image')->store('news', 'public');
    $data['image'] = '/storage/' . $path;
}

    $news->update($data);

    return response()->json($news);
}

    public function destroy(News $news)
    {
        $news->delete();

        return response()->json([
            'message' => 'Новость удалена',
        ]);
    }
}