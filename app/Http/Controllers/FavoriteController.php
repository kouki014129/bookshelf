<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * コントローラーのミドルウェアを設定する。
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 書籍のお気に入り登録・解除を切り替える。
     *
     * @param  Book  $book  対象書籍
     * @return RedirectResponse 元画面へのリダイレクトレスポンス
     */
    public function toggle(Book $book): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $isFavorited = $user
            ->favoriteBooks()
            ->where('book_id', $book->id)
            ->exists();

        $user->favoriteBooks()->toggle($book->id);

        $message = $isFavorited
            ? 'お気に入りを解除しました'
            : 'お気に入りに登録しました';

        return redirect()
            ->back()
            ->with('success', $message);
    }

    /**
     * ログインユーザーのお気に入り書籍一覧を表示する。
     *
     * @return View お気に入り一覧画面
     */
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $books = $user->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
