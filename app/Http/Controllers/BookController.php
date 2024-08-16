<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class BookController extends Controller
{
    /**
     * @OA\Get(
     *     path="/book",
     *     summary="Get all book data",
     *     tags={"book"},
     *     description="",
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Book's title",
     *         style="form"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page",
     *         style="form"
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         description="Page size (1 - 100)",
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Book")
     *         ),
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/book/{id}",
     *     summary="Get single book data",
     *     tags={"book"},
     *     description="",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Book's id",
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Items(ref="#/components/schemas/Book")
     *         ),
     *     )
     * )
     */
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
            'description' => 'max:1024',
            'publish_date' => 'date|before:now',
            'author_id' => 'exists:authors,id',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/book",
     *     summary="Create Book",
     *     tags={"book"},
     *     description="",
     *     @OA\RequestBody(
     *         description="Book's data",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Items(ref="#/components/schemas/Book")
     *         ),
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/book/{id}",
     *     summary="Update Book",
     *     tags={"book"},
     *     description="",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id",
     *         style="form"
     *     ),
     *     @OA\RequestBody(
     *         description="Book's data",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\Schema(
     *             type="object",
     *             @OA\Items(ref="#/components/schemas/Book")
     *         ),
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/book/{id}",
     *     summary="Delete Book",
     *     tags={"book"},
     *     description="",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Id",
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="successful operation",
     *     )
     * )
     */
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