<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::orderBy('id')->take(5)->get();
        $reviews = Review::with('user')
            ->orderBy('id')
            ->get();

        foreach ($reviews as $reviewIndex => $review) {
            $likeUserIds = [];

            foreach ($users as $userIndex => $user) {
                /*
                 * 自分が投稿したレビューにはいいねしない。
                 */
                if ($user->id === $review->user_id) {
                    continue;
                }

                /*
                 * レビューごとに、いいねするユーザーを変えます。
                 */
                if (($reviewIndex + $userIndex) % 3 === 0) {
                    $likeUserIds[] = $user->id;
                }
            }

            $review->likedByUsers()->syncWithoutDetaching($likeUserIds);
        }
    }
}