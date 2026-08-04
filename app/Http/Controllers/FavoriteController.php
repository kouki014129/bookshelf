<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function toggle(Book $book): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $user->favoriteBooks()->toggle($book->id);

        return redirect()->back();
    }

    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        $books = $user->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
