<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(IndexBookRequest $request)
    {
        $query = Book::query()
            ->with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function ($query) use ($keyword) {
                $query
                    ->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $genreId = $request->input('genre_id');

            $query->whereHas('genres', function ($query) use ($genreId) {
                $query->where('genres.id', $genreId);
            });
        }

        $perPage = $request->input('per_page', 10);

        $books = $query
            ->paginate($perPage)
            ->appends($request->query());

        return new BookCollection($books);
    }

    public function store(StoreBookRequest $request)
    {
        $book = DB::transaction(function () use ($request) {
            $book = Book::create([
                'title'          => $request->input('title'),
                'author'         => $request->input('author'),
                'isbn'           => $request->input('isbn'),
                'published_date' => $request->input('published_date'),
                'description'    => $request->input('description'),
                'image_url'      => $request->input('image_url'),

                // 基礎段階は認証なしのため、仮ユーザーを設定
                'user_id'        => 1,
            ]);

            $book->genres()->sync(
                $request->input('genres')
            );

            return $book;
        });

        $book->load([
            'genres',
            'reviews' => function ($query) {
                $query
                    ->with('user')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id');
            },
        ]);

        $book->loadCount('reviews');
        $book->loadAvg('reviews', 'rating');

        return (new BookResource($book))
            ->additional([
                'message' => '書籍を登録しました。',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews' => function ($query) {
                $query
                    ->with('user')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id');
            },
        ]);

        $book->loadCount('reviews');
        $book->loadAvg('reviews', 'rating');

        return new BookResource($book);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $book = DB::transaction(function () use ($request, $book) {
            $book->update(
                $request->safe()->except('genres')
            );

            $book->genres()->sync(
                $request->input('genres')
            );

            return $book;
        });

        $book->load([
            'genres',
            'reviews' => function ($query) {
                $query
                    ->with('user')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id');
            },
        ]);

        $book->loadCount('reviews');
        $book->loadAvg('reviews', 'rating');

        return (new BookResource($book))
            ->additional([
                'message' => '書籍情報を更新しました。',
            ]);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(null, 204);
    }
}