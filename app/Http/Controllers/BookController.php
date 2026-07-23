<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $books = Book::with(['genres', 'reviews'])->paginate(10);
        return view('books.index', compact('books'));
    }

    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request)
    {
        $book = Book::create([
            'user_id'        => auth()->id(),
            'title'          => $request->title,
            'author'         => $request->author,
            'isbn'           => $request->isbn,
            'published_date' => $request->published_date,
            'description'    => $request->description,
            'image_url'      => $request->image_url,
        ]);

        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました');
    }

    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews.user',
            'reviews.likedByUsers',
        ]);

        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $favorited = $user
            ? $user->favoriteBooks()->where('book_id', $book->id)->exists()
            : false;

        return view('books.show', compact('book','favorited'));
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $book->update([
            'title'          => $request->title,
            'author'         => $request->author,
            'isbn'           => $request->isbn,
            'published_date' => $request->published_date,
            'description'    => $request->description,
            'image_url'      => $request->image_url,
        ]);

        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍情報を更新しました');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました');
    }
}