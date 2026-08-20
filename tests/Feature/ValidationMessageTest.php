<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ValidationMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍登録のバリデーションエラーが日本語で表示される(): void
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
            'title' => 'タイトルは必須です。',
            'author' => '著者名は必須です。',
            'isbn' => 'ISBNは必須です。',
            'published_date' => '出版日は必須です。',
            'genres' => 'ジャンルを1つ以上選択してください。',
        ]);
    }

    public function test_書籍更新のバリデーションエラーが日本語で表示される(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('books.edit', $book))
            ->put(route('books.update', $book), [
                'title' => '',
                'author' => '',
                'isbn' => '',
                'published_date' => '',
                'genres' => [],
            ]);

        $response->assertRedirect(route('books.edit', $book));
        $response->assertSessionHasErrors([
            'title' => 'タイトルは必須です。',
            'author' => '著者名は必須です。',
            'isbn' => 'ISBNは必須です。',
            'published_date' => '出版日は必須です。',
            'genres' => 'ジャンルを1つ以上選択してください。',
        ]);
    }

    public function test_ジャンル登録のバリデーションエラーが日本語で表示される(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('genres.create'))
            ->post(route('genres.store'), [
                'name' => '',
            ]);

        $response->assertRedirect(route('genres.create'));
        $response->assertSessionHasErrors([
            'name' => 'ジャンル名は必須です。',
        ]);
    }

    public function test_ジャンル更新のバリデーションエラーが日本語で表示される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('genres.edit', $genre))
            ->put(route('genres.update', $genre), [
                'name' => '',
            ]);

        $response->assertRedirect(route('genres.edit', $genre));
        $response->assertSessionHasErrors([
            'name' => 'ジャンル名は必須です。',
        ]);
    }

    public function test_レビュー投稿のバリデーションエラーが日本語で表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.store', $book), [
                'rating' => '',
                'comment' => str_repeat('あ', 1001),
            ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHasErrors([
            'rating' => '評価は必須です。',
            'comment' => 'コメントは1000文字以内で入力してください。',
        ]);
    }

    public function test_レビュー更新のバリデーションエラーが日本語で表示される(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('reviews.edit', $review))
            ->put(route('reviews.update', $review), [
                'rating' => '',
                'comment' => str_repeat('あ', 1001),
            ]);

        $response->assertRedirect(route('reviews.edit', $review));
        $response->assertSessionHasErrors([
            'rating' => '評価は必須です。',
            'comment' => 'コメントは1000文字以内で入力してください。',
        ]);
    }

    public function test_読書計画登録のバリデーションエラーが日本語で表示される(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('reading-plans.create'))
            ->post(route('reading-plans.store'), [
                'book_id' => '',
                'deadline' => '',
            ]);

        $response->assertRedirect(route('reading-plans.create'));
        $response->assertSessionHasErrors([
            'book_id' => '書籍を選択してください。',
            'deadline' => '期日は必須です。',
        ]);
    }

    public function test_読書計画更新のバリデーションエラーが日本語で表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('reading-plans.edit', $readingPlan))
            ->put(route('reading-plans.update', $readingPlan), [
                'deadline' => '',
            ]);

        $response->assertRedirect(route('reading-plans.edit', $readingPlan));
        $response->assertSessionHasErrors([
            'deadline' => '期日は必須です。',
        ]);
    }

    public function test_書籍一覧検索のバリデーションエラーが日本語で表示される(): void
    {
        $response = $this
            ->from(route('books.index'))
            ->get(route('books.index', [
                'keyword' => str_repeat('あ', 256),
                'genre' => 'abc',
                'sort' => 'invalid',
            ]));

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHasErrors([
            'keyword' => 'キーワードは255文字以内で入力してください。',
            'genre' => 'ジャンルは正しい値を選択してください。',
            'sort' => '並び順の指定が正しくありません。',
        ]);
    }

    public function test_api書籍一覧のバリデーションエラーjsonが日本語で返る(): void
    {
        $response = $this->getJson('/api/v1/books?genre_id=abc&page=0&per_page=51');

        $response->assertStatus(422);
        $response->assertJsonPath('message', '入力内容に誤りがあります。');
        $response->assertJsonPath('errors.genre_id.0', 'ジャンルIDは整数で指定してください。');
        $response->assertJsonPath('errors.page.0', 'ページ番号は1以上の整数で指定してください。');
        $response->assertJsonPath('errors.per_page.0', '1ページの件数は1以上50以下で指定してください。');
    }

    public function test_api書籍登録のバリデーションエラーjsonが日本語で返る(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/books', [
            'title' => '',
            'author' => '',
            'isbn' => '',
            'published_date' => '',
            'genres' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', '入力内容に誤りがあります。');
        $response->assertJsonPath('errors.title.0', 'タイトルは必須です。');
        $response->assertJsonPath('errors.author.0', '著者名は必須です。');
        $response->assertJsonPath('errors.isbn.0', 'ISBNは必須です。');
        $response->assertJsonPath('errors.published_date.0', '出版日は必須です。');
        $response->assertJsonPath('errors.genres.0', 'ジャンルを1つ以上選択してください。');
    }

    public function test_api書籍更新のバリデーションエラーjsonが日本語で返る(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->patchJson('/api/v1/books/'.$book->id, [
            'title' => '',
            'author' => '',
            'isbn' => '',
            'published_date' => '',
            'genres' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', '入力内容に誤りがあります。');
        $response->assertJsonPath('errors.title.0', 'タイトルは必須です。');
        $response->assertJsonPath('errors.author.0', '著者名は必須です。');
        $response->assertJsonPath('errors.isbn.0', 'ISBNは必須です。');
        $response->assertJsonPath('errors.published_date.0', '出版日は必須です。');
        $response->assertJsonPath('errors.genres.0', 'ジャンルを1つ以上選択してください。');
    }

    public function test_apiログイン失敗時のバリデーションエラーjsonが日本語で返る(): void
    {
        User::factory()->create([
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'yamada@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', '認証情報が正しくありません。');
        $response->assertJsonPath('errors.email.0', '認証情報が正しくありません。');
    }
}
