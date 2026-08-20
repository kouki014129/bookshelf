<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ReviewLikeController extends Controller
{
    /**
     * コントローラーのミドルウェアを設定する。
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * レビューへのいいね登録・解除を切り替える。
     *
     * @param  Review  $review  対象レビュー
     * @return RedirectResponse 元画面へのリダイレクトレスポンス
     */
    public function toggle(Review $review): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $isLiked = $user
            ->likedReviews()
            ->where('review_id', $review->id)
            ->exists();

        $user->likedReviews()->toggle($review->id);

        $message = $isLiked
            ? 'レビューのいいねを解除しました'
            : 'レビューにいいねしました';

        return redirect()
            ->back()
            ->with('success', $message);
    }
}
