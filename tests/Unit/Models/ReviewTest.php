<?php

namespace Tests\Unit\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillableが正しく設定されている(): void
    {
        $review = new Review;

        $this->assertSame([
            'book_id',
            'user_id',
            'rating',
            'comment',
        ], $review->getFillable());
    }

    public function test_bookリレーションがbelongs_toである(): void
    {
        $review = new Review;

        $this->assertInstanceOf(
            BelongsTo::class,
            $review->book()
        );
    }

    public function test_userリレーションがbelongs_toである(): void
    {
        $review = new Review;

        $this->assertInstanceOf(
            BelongsTo::class,
            $review->user()
        );
    }

    public function test_liked_by_usersリレーションがbelongs_to_manyである(): void
    {
        $review = new Review;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $review->likedByUsers()
        );
    }

    public function test_同一ユーザーは同じ書籍に重複レビューできない(): void
    {
        $review = Review::factory()->create();

        $this->expectException(QueryException::class);

        Review::factory()->create([
            'user_id' => $review->user_id,
            'book_id' => $review->book_id,
        ]);
    }

    public function test_コメントはnullで保存できる(): void
    {
        $review = Review::factory()->create([
            'comment' => null,
        ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => null,
        ]);
    }
}
