<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function toggle(Book $book): RedirectResponse
    {
        auth()->user()->favorites()->toggle($book->id);

        return redirect()->back();
    }
}
