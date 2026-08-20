<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーはレビューにいいねできる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'レビューにいいねしました');
    }

    public function test_いいね済みのレビューは再度実行すると解除される(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $user->likedReviews()->attach($review->id);

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'レビューのいいねを解除しました');
    }

    public function test_解除したレビューは再度いいねできる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this
            ->actingAs($user)
            ->post(route('reviews.like', $review));

        $this
            ->actingAs($user)
            ->post(route('reviews.like', $review));

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'レビューにいいねしました');
    }

    public function test_自分のレビューにもいいねできる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'レビューにいいねしました');
    }

    public function test_未認証ユーザーはレビューにいいねできない(): void
    {
        $review = Review::factory()->create();

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect('/login');

        $this->assertDatabaseCount('review_likes', 0);
    }
}
