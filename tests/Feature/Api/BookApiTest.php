<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍一覧を取得できる(): void
    {
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'description',
                    'image_url',
                    'genres' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                    'average_rating',
                    'reviews_count',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'prev_page',
                'next_page',
                'per_page',
                'from',
                'to',
                'total',
            ],
        ]);

        $response->assertJsonMissingPath('links');
        $response->assertJsonMissingPath('meta.path');

        $response->assertJsonPath('data.0.id', $book->id);
        $response->assertJsonPath('data.0.title', 'Laravel入門');
        $response->assertJsonPath('data.0.author', '山田太郎');
        $response->assertJsonPath('data.0.isbn', '9781234567890');
        $response->assertJsonPath('data.0.genres.0.id', $genre->id);
        $response->assertJsonPath('data.0.average_rating', null);
        $response->assertJsonPath('data.0.reviews_count', 0);
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.prev_page', null);
        $response->assertJsonPath('meta.next_page', null);
    }

    public function test_一覧は1ページ10件で取得できる(): void
    {
        Book::factory()->count(11)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.per_page', 10);
        $response->assertJsonPath('meta.total', 11);
        $response->assertJsonPath('meta.last_page', 2);
        $response->assertJsonPath('meta.prev_page', null);
        $response->assertJsonPath('meta.next_page', 2);
    }

    public function test_per_pageで一覧件数を指定できる(): void
    {
        Book::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/books?per_page=3');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.per_page', 3);
        $response->assertJsonPath('meta.total', 5);
        $response->assertJsonPath('meta.last_page', 2);
        $response->assertJsonPath('meta.prev_page', null);
        $response->assertJsonPath('meta.next_page', 2);
    }

    public function test_per_pageが50を超える場合は422になる(): void
    {
        $response = $this->getJson('/api/v1/books?per_page=51');

        $response->assertStatus(422);
        $response->assertJsonPath(
            'message',
            '入力内容に誤りがあります。'
        );
        $response->assertJsonValidationErrors('per_page');
    }

    public function test_タイトルのキーワードで部分一致検索できる(): void
    {
        $targetBook = Book::factory()->create([
            'title' => 'Laravel実践入門',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'PHP基礎',
            'author' => '佐藤花子',
        ]);

        $response = $this->getJson(
            '/api/v1/books?keyword=Laravel'
        );

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $targetBook->id);
    }

    public function test_著者名のキーワードで部分一致検索できる(): void
    {
        $targetBook = Book::factory()->create([
            'title' => 'PHP基礎',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '佐藤花子',
        ]);

        $response = $this->getJson(
            '/api/v1/books?keyword=山田'
        );

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $targetBook->id);
    }

    public function test_キーワード前後の空白は除去される(): void
    {
        $targetBook = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $response = $this->getJson(
            '/api/v1/books?keyword=%20Laravel%20'
        );

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $targetBook->id);
    }

    public function test_ジャンルidで書籍を検索できる(): void
    {
        $targetGenre = Genre::factory()->create();
        $otherGenre = Genre::factory()->create();

        $targetBook = Book::factory()->create();
        $otherBook = Book::factory()->create();

        $targetBook->genres()->attach($targetGenre->id);
        $otherBook->genres()->attach($otherGenre->id);

        $response = $this->getJson(
            '/api/v1/books?genre_id='.$targetGenre->id
        );

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $targetBook->id);
    }

    public function test_存在しないジャンルidでは空配列が返る(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson(
            '/api/v1/books?genre_id=999999'
        );

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
        $response->assertJsonPath('meta.total', 0);
        $response->assertJsonPath('meta.prev_page', null);
        $response->assertJsonPath('meta.next_page', null);
    }

    public function test_ジャンルidが文字列の場合は422になる(): void
    {
        $response = $this->getJson(
            '/api/v1/books?genre_id=abc'
        );

        $response->assertStatus(422);
        $response->assertJsonPath(
            'message',
            '入力内容に誤りがあります。'
        );
        $response->assertJsonValidationErrors('genre_id');
    }

    public function test_書籍詳細を取得できる(): void
    {
        $book = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);

        $genre = Genre::factory()->create([
            'name' => 'プログラミング',
        ]);

        $book->genres()->attach($genre->id);

        $reviewUser = User::factory()->create([
            'name' => 'レビュー投稿者',
        ]);

        $review = Review::factory()->create([
            'user_id' => $reviewUser->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '分かりやすいです。',
        ]);

        $response = $this->getJson(
            '/api/v1/books/'.$book->id
        );

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'genres' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'average_rating',
                'reviews_count',
                'reviews' => [
                    '*' => [
                        'id',
                        'user' => [
                            'id',
                            'name',
                        ],
                        'rating',
                        'comment',
                        'created_at',
                    ],
                ],
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertJsonPath('data.id', $book->id);
        $response->assertJsonPath('data.title', 'Laravel入門');
        $response->assertJsonPath('data.genres.0.id', $genre->id);
        $response->assertJsonPath('data.average_rating', 5);
        $response->assertJsonPath('data.reviews_count', 1);
        $response->assertJsonPath('data.reviews.0.id', $review->id);
        $response->assertJsonPath(
            'data.reviews.0.user.id',
            $reviewUser->id
        );
        $response->assertJsonPath(
            'data.reviews.0.user.name',
            'レビュー投稿者'
        );
    }

    public function test_存在しない書籍詳細は404になる(): void
    {
        $response = $this->getJson(
            '/api/v1/books/999999'
        );

        $response->assertStatus(404);
        $response->assertExactJson([
            'message' => '指定された書籍が見つかりません。',
        ]);
    }

    public function test_認証済みユーザーは書籍を登録できる(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $payload = [
            'title' => 'Laravel実践',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => '説明文です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [
                $genre1->id,
                $genre2->id,
            ],
        ];

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', $payload);

        $response->assertCreated();

        $response->assertJsonPath(
            'message',
            '書籍を登録しました。'
        );

        $response->assertJsonPath(
            'data.title',
            'Laravel実践'
        );

        $this->assertDatabaseHas('books', [
            'title' => 'Laravel実践',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'user_id' => $user->id,
        ]);

        $book = Book::where('isbn', '9781234567890')
            ->firstOrFail();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre1->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre2->id,
        ]);
    }

    public function test_未認証では書籍を登録できない(): void
    {
        $genre = Genre::factory()->create();

        $payload = [
            'title' => 'Laravel実践',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'genres' => [
                $genre->id,
            ],
        ];

        $response = $this->postJson(
            '/api/v1/books',
            $payload
        );

        $response->assertUnauthorized();

        $this->assertDatabaseMissing('books', [
            'isbn' => '9781234567890',
        ]);
    }

    public function test_タイトル未入力では422になる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $payload = [
            'title' => '',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_isbnが13桁でない場合は422になる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $payload = [
            'title' => 'Laravel実践',
            'author' => '山田太郎',
            'isbn' => '123',
            'published_date' => '2025-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_存在しないジャンルでは422になる(): void
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Laravel実践',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'genres' => [999999],
        ];

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('genres.0');
    }

    public function test_重複isbnでは422になる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        $payload = [
            'title' => 'Laravel実践',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    public function test_認証済みユーザーは書籍を更新できる(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '更新前',
            'author' => '更新前著者',
            'isbn' => '9781234567890',
        ]);

        $book->genres()->attach($genre1->id);

        $payload = [
            'title' => '更新後',
            'author' => '更新後著者',
            'isbn' => '9781234567891',
            'published_date' => '2025-02-01',
            'description' => '更新後説明',
            'image_url' => 'https://example.com/new.jpg',
            'genres' => [
                $genre2->id,
            ],
        ];

        $response = $this
            ->actingAs($user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $payload);

        $response->assertOk();

        $response->assertJsonPath(
            'message',
            '書籍情報を更新しました。'
        );

        $response->assertJsonPath(
            'data.title',
            '更新後'
        );

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後',
            'author' => '更新後著者',
            'isbn' => '9781234567891',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre2->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre1->id,
        ]);
    }

    public function test_未認証では書籍を更新できない(): void
    {
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'title' => '更新前',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);

        $book->genres()->attach($genre->id);

        $payload = [
            'title' => '更新後',
            'author' => '佐藤花子',
            'isbn' => '9781234567891',
            'published_date' => '2025-01-01',
            'description' => '更新後説明',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [
                $genre->id,
            ],
        ];

        $response = $this->putJson(
            "/api/v1/books/{$book->id}",
            $payload
        );

        $response->assertUnauthorized();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);
    }

    public function test_他ユーザーは書籍を更新できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
            'title' => '更新前',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);

        $book->genres()->attach($genre->id);

        $payload = [
            'title' => '不正な更新',
            'author' => '佐藤花子',
            'isbn' => '9781234567891',
            'published_date' => '2025-01-01',
            'description' => '不正な更新です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [
                $genre->id,
            ],
        ];

        $response = $this
            ->actingAs($otherUser, 'sanctum')
            ->putJson(
                "/api/v1/books/{$book->id}",
                $payload
            );

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $owner->id,
            'title' => '更新前',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);
    }

    public function test_認証済みユーザーはpatchで書籍を更新できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '更新前',
            'author' => '更新前著者',
            'isbn' => '9781234567890',
        ]);

        $payload = [
            'title' => 'PATCH更新後',
            'author' => 'PATCH更新後著者',
            'isbn' => '9781234567890',
            'published_date' => '2025-03-01',
            'description' => 'PATCHで更新しました。',
            'image_url' => 'https://example.com/patch.jpg',
            'genres' => [
                $genre->id,
            ],
        ];

        $response = $this
            ->actingAs($user, 'sanctum')
            ->patchJson(
                "/api/v1/books/{$book->id}",
                $payload
            );

        $response->assertOk();

        $response->assertJsonPath(
            'message',
            '書籍情報を更新しました。'
        );

        $response->assertJsonPath(
            'data.title',
            'PATCH更新後'
        );

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'PATCH更新後',
            'author' => 'PATCH更新後著者',
            'isbn' => '9781234567890',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_更新時に必須項目が不足すると422になる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '更新前',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->putJson(
                "/api/v1/books/{$book->id}",
                [
                    'title' => '',
                ]
            );

        $response->assertStatus(422);
        $response->assertJsonPath(
            'message',
            '入力内容に誤りがあります。'
        );
        $response->assertJsonValidationErrors([
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前',
        ]);
    }

    public function test_存在しない書籍は更新できず404になる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $payload = [
            'title' => '更新後',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => null,
            'image_url' => null,
            'genres' => [
                $genre->id,
            ],
        ];

        $response = $this
            ->actingAs($user, 'sanctum')
            ->putJson(
                '/api/v1/books/999999',
                $payload
            );

        $response->assertStatus(404);
        $response->assertExactJson([
            'message' => '指定された書籍が見つかりません。',
        ]);
    }

    public function test_認証済みの作成者は書籍を削除できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_未認証では書籍を削除できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson(
            "/api/v1/books/{$book->id}"
        );

        $response->assertUnauthorized();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
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
            ->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_存在しない書籍は削除できず404になる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/books/999999');

        $response->assertStatus(404);
        $response->assertExactJson([
            'message' => '指定された書籍が見つかりません。',
        ]);
    }
}
