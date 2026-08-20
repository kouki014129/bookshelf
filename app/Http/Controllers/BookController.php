<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * コントローラーのミドルウェアを設定する。
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * 書籍一覧を検索・絞り込み条件付きで表示する。
     */
    public function index(IndexBookRequest $request): View
    {
        $keyword = $request->input('keyword');
        $genre = $request->input('genre');
        $sort = $request->input('sort', 'latest');

        $query = Book::query()
            ->with(['genres', 'reviews'])
            ->withAvg('reviews', 'rating');

        if ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($genre) {
            $query->whereHas('genres', function ($query) use ($genre) {
                $query->where('genres.id', $genre);
            });
        }

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at');
                break;

            case 'title':
                $query->orderBy('title');
                break;

            case 'rating':
                $query
                    ->orderByRaw('reviews_avg_rating IS NULL')
                    ->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('created_at');
                break;

            case 'latest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        $books = $query
            ->paginate(10)
            ->appends($request->query());

        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍登録画面を表示する。
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を登録する。
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = DB::transaction(function () use ($request): Book {
            $book = Book::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'published_date' => $request->published_date,
                'description' => $request->description,
                'image_url' => $request->image_url,
            ]);

            $book->genres()->sync($request->genres);

            return $book;
        });

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました');
    }

    /**
     * 書籍詳細画面を表示する。
     */
    public function show(Book $book): View
    {
        $book->load([
            'genres',
            'reviews.user',
            'reviews.likedByUsers',
        ]);

        /** @var User|null $user */
        $user = auth()->user();

        $favorited = $user
            ? $user->favoriteBooks()
                ->where('book_id', $book->id)
                ->exists()
            : false;

        return view('books.show', compact('book', 'favorited'));
    }

    /**
     * 書籍編集画面を表示する。
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍情報を更新する。
     */
    public function update(
        UpdateBookRequest $request,
        Book $book
    ): RedirectResponse {
        $this->authorize('update', $book);

        DB::transaction(function () use ($request, $book): void {
            $book->update([
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'published_date' => $request->published_date,
                'description' => $request->description,
                'image_url' => $request->image_url,
            ]);

            $book->genres()->sync($request->genres);
        });

        return redirect()->route('books.show', $book)
            ->with('success', '書籍情報を更新しました');
    }

    /**
     * 書籍を削除する。
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました');
    }
}
