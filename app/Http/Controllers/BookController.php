<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $title = $request->query('title');

        $cacheKey = "book:$title:$request->orderBy:$request->orderAs:$request->page:$request->pageSize";
        $cached = Cache::get($cacheKey);
        if ($cached)
            return response()->json($cached);

        $query = Book::query();

        if ($title != null)
            $query->where('title', 'like', "%$title%");

        if ($request->has('order_by') && in_array($request->orderBy, Book::$orderable))
            $query->orderBy($request->orderBy, $request->orderAs);

        $data = $query->paginate($request->pageSize);
        if ($data->isNotEmpty())
            Cache::put($cacheKey, $data, 600);

        return response()->json($data);
    }

    public function detail($id)
    {
        $cacheKey = "book:$id";
        $cached = Cache::get($cacheKey);
        if ($cached)
            return response()->json($cached);

        $data = Book::find($id);
        if ($data == null)
            return response()->json(['message' => 'data not found'], 404);

        Cache::put($cacheKey, $data, 600);

        return response()->json($data);
    }

    function validation(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:100',
            'description' => 'max:255',
            'publish_date' => 'date|before:now',
            'author_id' => 'exists:authors,id',
        ]);
    }

    public function store(Request $request)
    {
        $this->validation($request);

        $book = Book::create($request->all());

        $redis = Redis::connection('cache');
        $keys = $redis->keys("book:*$request->title*");
        $keys = array_merge($keys, $redis->keys("book::*"));
        $keys = array_merge($keys, Redis::connection('cache')->keys("author:$book->author_id:books:*"));
        if (count($keys) > 0)
            Cache::deleteMultiple($keys);

        return response()->json($book, 201);
    }

    public function update(Request $request, $id)
    {
        $this->validation($request);

        $data = Book::find($id);
        if ($data == null)
            return response()->json(['message' => 'data not found'], 404);

        $titleBefore = $data->title;
        $authorBefore = $data->author_id;
        $data->update($request->all());

        $keys = ["book:$id"];
        $keys = array_merge($keys, Redis::connection('cache')->keys("book:*$titleBefore*"));
        $keys = array_merge($keys, Redis::connection('cache')->keys("book:*$request->title*"));
        $keys = array_merge($keys, Redis::connection('cache')->keys("author:$authorBefore:books:*"));
        if ($authorBefore <> $request->author_id)
            $keys = array_merge($keys, Redis::connection('cache')->keys("author:*$request->author_id:*"));
        $keys = array_merge($keys, Redis::connection('cache')->keys("book::*"));
        if (count($keys) > 0)
            Cache::deleteMultiple($keys);

        return response()->json($data, 200);
    }

    public function destroy($id)
    {
        $data = Book::find($id);
        if ($data == null)
            return response()->json(['message' => 'data not found'], 404);

        $title = $data->title;
        $authorId = $data->author_id;

        $data->delete();
        $keys = ["book:$id"];
        $keys = array_merge($keys, Redis::connection('cache')->keys("book:*$title*"));
        $keys = array_merge($keys, Redis::connection('cache')->keys("book::*"));
        $keys = array_merge($keys, Redis::connection('cache')->keys("author:$authorId:books:*"));
        if (count($keys) > 0)
            Cache::deleteMultiple($keys);

        return response()->json(null, 204);
    }
}