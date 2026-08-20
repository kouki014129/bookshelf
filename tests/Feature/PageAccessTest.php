<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_未認証ユーザーは公開ページを表示できる(): void
    {
        $book = Book::factory()->create();

        $this->get('/')
            ->assertOk();

        $this->get(route('books.index'))
            ->assertOk();

        $this->get(route('books.show', $book))
            ->assertOk();

        $this->get(route('ranking.index'))
            ->assertOk();
    }

    public function test_未認証ユーザーは認証必須ページを表示できない(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $review = Review::factory()->create();

        $protectedRoutes = [
            route('books.create'),
            route('books.edit', $book),
            route('favorites.index'),
            route('genres.index'),
            route('genres.show', $genre),
            route('genres.create'),
            route('genres.edit', $genre),
            route('reviews.edit', $review),
            route('reports.index'),
            route('reading-plans.index'),
            route('reading-plans.create'),
            route('notifications.index'),
        ];

        foreach ($protectedRoutes as $route) {
            $this->get($route)
                ->assertRedirect(route('login'));
        }
    }

    public function test_認証済みユーザーは基本画面を表示できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $genre = Genre::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $visibleRoutes = [
            '/',
            route('books.index'),
            route('books.show', $book),
            route('books.create'),
            route('books.edit', $book),
            route('favorites.index'),
            route('genres.index'),
            route('genres.show', $genre),
            route('genres.create'),
            route('genres.edit', $genre),
            route('reviews.edit', $review),
            route('ranking.index'),
            route('reports.index'),
            route('reading-plans.index'),
            route('reading-plans.create'),
            route('notifications.index'),
        ];

        foreach ($visibleRoutes as $route) {
            $this->actingAs($user)
                ->get($route)
                ->assertOk();
        }
    }
}
