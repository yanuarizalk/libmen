<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use OpenApi\Annotations as OA;

class AuthorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/author",
     *     summary="Get all author data",
     *     tags={"author"},
     *     description="",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Author's name",
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Author")
     *         ),
     *     )
     * )
     */
    public function index(Request $request)
    {
        $name = $request->query('name');

        $cacheKey = "author:$name:$request->orderBy:$request->orderAs:$request->page:$request->pageSize";
        $cached = Cache::get($cacheKey);
        if ($cached)
            return response()->json($cached);

        $query = Author::query();

        if ($name != null)
            $query->where('name', 'like', "%$name%");

        if ($request->has('order_by') && in_array($request->orderBy, Author::$orderable))
            $query->orderBy($request->orderBy, $request->orderAs);

        $data = $query->paginate($request->pageSize);
        if ($data->isNotEmpty())
            Cache::put($cacheKey, $data, 600);

        return response()->json($data);
    }

    public function detail($id)
    {
        $cacheKey = "author:$id";
        $cached = Cache::get($cacheKey);
        if ($cached)
            return response()->json($cached);

        $data = Author::find($id);
        if ($data == null)
            return response()->json(['message' => 'data not found'], 404);

        Cache::put($cacheKey, $data, 600);

        return response()->json($data);
    }

    function validation(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:100',
            'bio' => 'max:1024',
            'birth_date' => 'date|before:now',
        ]);
    }

    public function store(Request $request)
    {
        $this->validation($request);

        $data = Author::create($request->all());

        $redis = Redis::connection('cache');
        $keys = $redis->keys("author:*$request->name*");
        $keys = array_merge($keys, $redis->keys("author::*"));
        if (count($keys) > 0)
            Cache::deleteMultiple($keys);

        return response()->json($data, 201);
    }

    public function update(Request $request, $id)
    {
        $this->validation($request);

        $data = Author::find($id);
        if ($data == null)
            return response()->json(['message' => 'data not found'], 404);

        $nameBefore = $data->name;
        $data->update($request->all());

        $keys = ["author:$id"];
        $keys = array_merge($keys, Redis::connection('cache')->keys("author:*$nameBefore*"));
        $keys = array_merge($keys, Redis::connection('cache')->keys("author:*$request->name*"));
        $keys = array_merge($keys, Redis::connection('cache')->keys("author::*"));
        if (count($keys) > 0)
            Cache::deleteMultiple($keys);

        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        $data = Author::find($id);
        if ($data == null)
            return response()->json(['message' => 'data not found'], 404);

        $name = $data->name;

        $data->delete();
        $keys = ["author:$id"];
        $keys = array_merge($keys, Redis::connection('cache')->keys("author:*$name*"));
        $keys = array_merge($keys, Redis::connection('cache')->keys("author::*"));
        if (count($keys) > 0)
            Cache::deleteMultiple($keys);

        return response()->json(null, 204);
    }

    public function books(Request $request, $authorId)
    {
        $cacheKey = "author:$authorId:books:$request->orderBy:$request->orderAs:$request->page:$request->pageSize";
        $cached = Cache::get($cacheKey);
        if ($cached)
            return response()->json($cached);

        $query = Author::find($authorId);

        if ($request->has('order_by') && in_array($request->orderBy, Book::$orderable))
            $query->orderBy($request->orderBy, $request->orderAs);

        $data = $query->books()->paginate($request->pageSize);
        if ($data->isNotEmpty())
            Cache::put($cacheKey, $data, 600);

        return response()->json($data);
    }
}