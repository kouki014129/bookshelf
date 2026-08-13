<?php

namespace Tests\Unit\Models;

use App\Models\ReviewLike;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillableが正しく設定されている(): void
    {
        $reviewLike = new ReviewLike;

        $this->assertSame([
            'user_id',
            'review_id',
        ], $reviewLike->getFillable());
    }

    public function test_userリレーションがbelongs_toである(): void
    {
        $reviewLike = new ReviewLike;

        $this->assertInstanceOf(
            BelongsTo::class,
            $reviewLike->user()
        );
    }

    public function test_reviewリレーションがbelongs_toである(): void
    {
        $reviewLike = new ReviewLike;

        $this->assertInstanceOf(
            BelongsTo::class,
            $reviewLike->review()
        );
    }

    public function test_同一ユーザーは同じレビューに重複していいねできない(): void
    {
        $reviewLike = ReviewLike::factory()->create();

        $this->expectException(QueryException::class);

        ReviewLike::factory()->create([
            'user_id' => $reviewLike->user_id,
            'review_id' => $reviewLike->review_id,
        ]);
    }
}
