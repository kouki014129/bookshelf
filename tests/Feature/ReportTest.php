<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインユーザーは読書レポート画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('マイ読書レポート');
        $response->assertSee('基本統計');
        $response->assertSee('評価分布');
        $response->assertSee('高評価書籍 TOP5');
        $response->assertSee('ジャンル別評価傾向 TOP5');
    }

    public function test_未ログインユーザーは読書レポート画面を表示できない(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_自分のレビューだけが基本統計に集計される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $otherBook = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
        ]);

        Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $otherBook->id,
            'rating' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertViewHas('totalReviews', 2);
        $response->assertViewHas('completedBooks', 2);
        $response->assertViewHas('averageRating', 4.0);
    }

    public function test_評価分布が正しく集計される(): void
    {
        $user = User::factory()->create();

        foreach ([5, 5, 4, 3] as $rating) {
            $book = Book::factory()->create();

            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $rating,
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertViewHas('ratingDistribution', [
            1 => 0,
            2 => 0,
            3 => 1,
            4 => 1,
            5 => 2,
        ]);
    }

    public function test_高評価書籍が評価順で取得される(): void
    {
        $user = User::factory()->create();

        $lowBook = Book::factory()->create([
            'title' => '低評価の本',
        ]);

        $highBook = Book::factory()->create([
            'title' => '高評価の本',
        ]);

        $middleBook = Book::factory()->create([
            'title' => '中評価の本',
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $lowBook->id,
            'rating' => 2,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $highBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $middleBook->id,
            'rating' => 4,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '高評価の本',
            '中評価の本',
            '低評価の本',
        ]);
    }

    public function test_ジャンル別評価傾向が表示される(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $book = Book::factory()->create();

        $book->genres()->attach($genre->id);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('技術書');
        $response->assertSee('5.0');
    }
}
