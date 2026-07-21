<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewLikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function toggle(Review $review): RedirectResponse
    {
        $user = auth()->user();

        if ($review->user_id === $user->id) {
            return redirect()->back()
                ->with('error', '自分のレビューにはいいねできません。');
        }

        $user->likedReviews()->toggle($review->id);

        return redirect()->back();
    }
}
