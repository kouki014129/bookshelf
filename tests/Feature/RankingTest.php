<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ランキング画面を表示できる(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ranking.index');
        $response->assertViewHas('rankedBooks');
    }

    public function test_レビューがある書籍だけランキングに表示される(): void
    {
        $reviewedBook = Book::factory()->create([
            'title' => 'レビューあり書籍',
        ]);

        $bookWithoutReviews = Book::factory()->create([
            'title' => 'レビューなし書籍',
        ]);

        Review::factory()->create([
            'book_id' => $reviewedBook->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use (
            $reviewedBook,
            $bookWithoutReviews
        ) {
            return $rankedBooks->contains('id', $reviewedBook->id)
                && ! $rankedBooks->contains('id', $bookWithoutReviews->id);
        });
    }

    public function test_平均評価が高い書籍から順に並ぶ(): void
    {
        $highRatedBook = Book::factory()->create([
            'title' => '高評価書籍',
        ]);

        $lowRatedBook = Book::factory()->create([
            'title' => '低評価書籍',
        ]);

        Review::factory()->create([
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $lowRatedBook->id,
            'rating' => 3,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use (
            $highRatedBook,
            $lowRatedBook
        ) {
            return $rankedBooks->pluck('id')->all() === [
                $highRatedBook->id,
                $lowRatedBook->id,
            ];
        });
    }

    public function test_平均評価が同じ場合はレビュー件数が多い書籍から順に並ぶ(): void
    {
        $manyReviewsBook = Book::factory()->create([
            'title' => 'レビュー件数が多い書籍',
        ]);

        $fewReviewsBook = Book::factory()->create([
            'title' => 'レビュー件数が少ない書籍',
        ]);

        $users = User::factory()->count(3)->create();

        Review::factory()->create([
            'user_id' => $users[0]->id,
            'book_id' => $manyReviewsBook->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $users[1]->id,
            'book_id' => $manyReviewsBook->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $users[2]->id,
            'book_id' => $fewReviewsBook->id,
            'rating' => 4,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use (
            $manyReviewsBook,
            $fewReviewsBook
        ) {
            return $rankedBooks->pluck('id')->all() === [
                $manyReviewsBook->id,
                $fewReviewsBook->id,
            ];
        });
    }

    public function test_ランキングは最大10冊まで表示される(): void
    {
        $books = Book::factory()->count(11)->create();
        $users = User::factory()->count(11)->create();

        foreach ($books as $index => $book) {
            Review::factory()->create([
                'user_id' => $users[$index]->id,
                'book_id' => $book->id,
                'rating' => 5,
            ]);
        }

        $response = $this->get(route('ranking.index'));

        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            return $rankedBooks->count() === 10;
        });
    }

    public function test_レビューが1件もない場合は空のランキングが表示される(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            return $rankedBooks->isEmpty();
        });
    }
}
