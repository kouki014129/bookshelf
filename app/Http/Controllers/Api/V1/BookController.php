<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * 検索条件に一致する書籍一覧をJSON形式で返す。
     *
     * @param  IndexBookRequest  $request  書籍一覧APIの検索・ページネーション条件
     * @return BookCollection 書籍一覧のAPI Resource Collection
     */
    public function index(IndexBookRequest $request): BookCollection
    {
        $query = Book::query()
            ->with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function (Builder $query) use ($keyword): void {
                $query
                    ->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $genreId = $request->input('genre_id');

            $query->whereHas('genres', function (Builder $query) use ($genreId): void {
                $query->where('genres.id', $genreId);
            });
        }

        $perPage = $request->input('per_page', 10);

        $books = $query
            ->paginate($perPage)
            ->appends($request->query());

        return new BookCollection($books);
    }

    /**
     * 認証済みユーザーの書籍を登録し、登録結果をJSON形式で返す。
     *
     * @param  StoreBookRequest  $request  書籍登録APIの入力値
     * @return JsonResponse 登録した書籍情報と成功メッセージ
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = DB::transaction(function () use ($request): Book {
            $book = Book::create([
                'user_id' => $request->user()->id,
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'isbn' => $request->input('isbn'),
                'published_date' => $request->input('published_date'),
                'description' => $request->input('description'),
                'image_url' => $request->input('image_url'),
            ]);

            $book->genres()->sync(
                $request->input('genres')
            );

            return $book;
        });

        $this->loadBookRelations($book);

        return (new BookResource($book))
            ->additional([
                'message' => '書籍を登録しました。',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 指定された書籍の詳細情報をJSON形式で返す。
     *
     * @param  Book  $book  詳細表示対象の書籍
     * @return BookResource 書籍詳細のAPI Resource
     */
    public function show(Book $book): BookResource
    {
        $this->loadBookRelations($book);

        return new BookResource($book);
    }

    /**
     * 認証済みユーザーが所有する書籍を更新し、更新結果をJSON形式で返す。
     *
     * @param  UpdateBookRequest  $request  書籍更新APIの入力値
     * @param  Book  $book  更新対象の書籍
     * @return BookResource 更新後の書籍情報
     */
    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        $this->authorize('update', $book);

        $book = DB::transaction(function () use ($request, $book): Book {
            $book->update(
                $request->safe()->except('genres')
            );

            $book->genres()->sync(
                $request->input('genres')
            );

            return $book;
        });

        $this->loadBookRelations($book);

        return (new BookResource($book))
            ->additional([
                'message' => '書籍情報を更新しました。',
            ]);
    }

    /**
     * 認証済みユーザーが所有する書籍を削除する。
     *
     * @param  Book  $book  削除対象の書籍
     * @return JsonResponse 空のレスポンス
     */
    public function destroy(Book $book): JsonResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json(null, 204);
    }

    /**
     * APIレスポンスで使用する書籍関連情報を読み込む。
     *
     * @param  Book  $book  関連情報を読み込む書籍
     */
    private function loadBookRelations(Book $book): void
    {
        $book->load([
            'genres',
            'reviews' => function (HasMany $query): void {
                $query
                    ->with('user')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id');
            },
        ]);

        $book->loadCount('reviews');
        $book->loadAvg('reviews', 'rating');
    }
}
