<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーは書籍をお気に入り登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'お気に入りに登録しました');
    }

    public function test_お気に入り登録済みの書籍は再度実行すると解除される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $response = $this
            ->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'お気に入りを解除しました');
    }

    public function test_解除した書籍は再度お気に入り登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this
            ->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $this
            ->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $response = $this
            ->actingAs($user)
            ->post(route('favorites.toggle', $book));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'お気に入りに登録しました');
    }

    public function test_認証済みユーザーはお気に入り一覧を表示できる(): void
    {
        $user = User::factory()->create();
        $favoriteBook = Book::factory()->create();
        $otherBook = Book::factory()->create();

        $user->favoriteBooks()->attach($favoriteBook->id);

        $response = $this
            ->actingAs($user)
            ->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertViewIs('favorites.index');
        $response->assertViewHas('books');

        $response->assertSee($favoriteBook->title);
        $response->assertDontSee($otherBook->title);
    }

    public function test_お気に入り一覧は1ページ10件で表示される(): void
    {
        $user = User::factory()->create();
        $books = Book::factory()->count(11)->create();

        $user->favoriteBooks()->attach($books->pluck('id')->all());

        $response = $this
            ->actingAs($user)
            ->get(route('favorites.index'));

        $response->assertStatus(200);

        $response->assertViewHas('books', function ($paginatedBooks): bool {
            return $paginatedBooks->perPage() === 10
                && $paginatedBooks->total() === 11
                && $paginatedBooks->count() === 10;
        });
    }

    public function test_未認証ユーザーはお気に入り登録できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('favorites.toggle', $book));

        $response->assertRedirect('/login');

        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_未認証ユーザーはお気に入り一覧を表示できない(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect('/login');
    }
}
