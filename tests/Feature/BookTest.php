<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍一覧を表示できる(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('books.index');
        $response->assertViewHas('books');
    }

    public function test_書籍詳細を表示できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertViewIs('books.show');
        $response->assertViewHas('book', function (Book $viewBook) use ($book): bool {
            return $viewBook->id === $book->id;
        });
    }

    public function test_書籍詳細に必要な情報が表示される(): void
    {
        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $reviewUser = User::factory()->create([
            'name' => 'レビュー投稿者',
        ]);

        $likeUser = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel実践入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2026-08-05',
            'description' => 'Laravelの実践的な解説書です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $book->genres()->attach($genre->id);

        $review = Review::factory()->create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても参考になりました。',
        ]);

        $likeUser->likedReviews()->attach($review->id);

        $response = $this->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee('Laravel実践入門');
        $response->assertSee('山田太郎');
        $response->assertSee('9781234567890');
        $response->assertSee('2026-08-05');
        $response->assertSee('Laravelの実践的な解説書です。');
        $response->assertSee('https://example.com/book.jpg');
        $response->assertSee('技術書');
        $response->assertSee('レビュー投稿者');
        $response->assertSee('★★★★★');
        $response->assertSee('とても参考になりました。');
        $response->assertSee('いいね (1)');
    }

    public function test_認証済みユーザーは書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-05',
                'description' => 'Laravelの入門書です。',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $book = Book::where('isbn', '9781234567890')->firstOrFail();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2026-08-05',
            'description' => 'Laravelの入門書です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました');
    }

    public function test_書籍に複数ジャンルを登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this
            ->actingAs($user)
            ->post(route('books.store'), [
                'title' => '複数ジャンルの書籍',
                'author' => '山田太郎',
                'isbn' => '9781234567891',
                'published_date' => '2026-08-05',
                'description' => null,
                'image_url' => null,
                'genres' => $genres->pluck('id')->all(),
            ]);

        $book = Book::where('isbn', '9781234567891')->firstOrFail();

        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }

        $response->assertRedirect(route('books.show', $book));
    }

    public function test_必須項目が未入力の場合は書籍を登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.create'))
            ->post(route('books.store'), [
                'title' => '',
                'author' => '',
                'isbn' => '',
                'published_date' => '',
                'genres' => [],
            ]);

        $response->assertRedirect(route('books.create'));
        $response->assertSessionHasErrors([
            'title',
            'author',
            'genres',
        ]);

        $response->assertSessionDoesntHaveErrors([
            'isbn',
            'published_date',
        ]);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_isbnが13桁でない場合は書籍を登録できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.create'))
            ->post(route('books.store'), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '123456789012',
                'published_date' => '2026-08-05',
                'genres' => [$genre->id],
            ]);

        $response->assertRedirect(route('books.create'));
        $response->assertSessionHasErrors('isbn');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_重複したisbnでは書籍を登録できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('books.create'))
            ->post(route('books.store'), [
                'title' => '重複する書籍',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-05',
                'genres' => [$genre->id],
            ]);

        $response->assertRedirect(route('books.create'));
        $response->assertSessionHasErrors('isbn');

        $this->assertDatabaseCount('books', 1);
    }

    public function test_不正な出版日では書籍を登録できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.create'))
            ->post(route('books.store'), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '不正な日付',
                'genres' => [$genre->id],
            ]);

        $response->assertRedirect(route('books.create'));
        $response->assertSessionHasErrors('published_date');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_不正な画像urlでは書籍を登録できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.create'))
            ->post(route('books.store'), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-05',
                'image_url' => '画像URLではありません',
                'genres' => [$genre->id],
            ]);

        $response->assertRedirect(route('books.create'));
        $response->assertSessionHasErrors('image_url');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_説明文が1000文字を超える場合は書籍を登録できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.create'))
            ->post(route('books.store'), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-05',
                'description' => str_repeat('あ', 1001),
                'genres' => [$genre->id],
            ]);

        $response->assertRedirect(route('books.create'));
        $response->assertSessionHasErrors('description');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_存在しないジャンルでは書籍を登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.create'))
            ->post(route('books.store'), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-05',
                'genres' => [999999],
            ]);

        $response->assertRedirect(route('books.create'));
        $response->assertSessionHasErrors('genres.0');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_作成者は書籍編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('books.edit', $book));

        $response->assertStatus(200);
        $response->assertViewIs('books.edit');
        $response->assertViewHas('book', function (Book $viewBook) use ($book): bool {
            return $viewBook->id === $book->id;
        });
    }

    public function test_作成者は書籍情報を更新できる(): void
    {
        $user = User::factory()->create();
        $oldGenre = Genre::factory()->create();
        $newGenre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '9781234567890',
        ]);

        $book->genres()->attach($oldGenre->id);

        $response = $this
            ->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => '更新後のタイトル',
                'author' => '更新後の著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-05',
                'description' => '更新後の説明文',
                'image_url' => 'https://example.com/updated.jpg',
                'genres' => [$newGenre->id],
            ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
            'author' => '更新後の著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-08-05',
            'description' => '更新後の説明文',
            'image_url' => 'https://example.com/updated.jpg',
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $newGenre->id,
        ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍情報を更新しました');
    }

    public function test_他ユーザーは書籍編集画面を表示できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->get(route('books.edit', $book));

        $response->assertForbidden();
    }

    public function test_他ユーザーは書籍情報を更新できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
            'title' => '更新前のタイトル',
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->put(route('books.update', $book), [
                'title' => '不正に更新されたタイトル',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'description' => $book->description,
                'image_url' => $book->image_url,
                'genres' => [$genre->id],
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前のタイトル',
        ]);
    }

    public function test_作成者は書籍を削除できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('books.destroy', $book));

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', '書籍を削除しました');
    }

    public function test_書籍削除時に関連データも削除される(): void
    {
        $owner = User::factory()->create();
        $reviewUser = User::factory()->create();
        $favoriteUser = User::factory()->create();
        $likeUser = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $book->genres()->attach($genre->id);
        $favoriteUser->favoriteBooks()->attach($book->id);

        $review = Review::factory()->create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
        ]);

        $likeUser->likedReviews()->attach($review->id);

        ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => ReadingPlanStatus::Planning,
        ]);

        $response = $this
            ->actingAs($owner)
            ->delete(route('books.destroy', $book));

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', '書籍を削除しました');

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $favoriteUser->id,
            'book_id' => $book->id,
        ]);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $likeUser->id,
            'review_id' => $review->id,
        ]);

        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $owner->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_他ユーザーは書籍を削除できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->delete(route('books.destroy', $book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
