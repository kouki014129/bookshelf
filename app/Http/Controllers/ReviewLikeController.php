<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ReviewLikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function toggle(Review $review): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $user->likedReviews()->toggle($review->id);

        return redirect()->back();
    }
}
