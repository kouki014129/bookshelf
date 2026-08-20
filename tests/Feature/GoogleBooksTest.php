<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBooksTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍登録画面にisbn検索フォームが表示される(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('books.create'));

        $response->assertStatus(200);
        $response->assertSee('ISBNから書籍情報を自動入力');
        $response->assertSee('isbn_search');
        $response->assertSee('isbn_search_button');
        $response->assertSee('/books/isbn/');
    }

    public function test_isbnから書籍情報を取得できる(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'リーダブルコード',
                            'authors' => [
                                'Dustin Boswell',
                                'Trevor Foucher',
                            ],
                            'publishedDate' => '2012-06-23',
                            'description' => '読みやすいコードについて解説する書籍です。',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/book.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson(
            '/books/isbn/9784873115658'
        );

        $response->assertStatus(200);

        $response->assertExactJson([
            'title' => 'リーダブルコード',
            'author' => 'Dustin Boswell, Trevor Foucher',
            'published_date' => '2012-06-23',
            'description' => '読みやすいコードについて解説する書籍です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        Http::assertSent(function ($request): bool {
            return $request->url()
                === 'https://www.googleapis.com/books/v1/volumes?q=isbn%3A9784873115658';
        });
    }

    public function test_存在しないisbnでは404になる(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
            ], 200),
        ]);

        $response = $this->getJson(
            '/books/isbn/9999999999999'
        );

        $response->assertStatus(404);

        $response->assertExactJson([
            'message' => '書籍情報が見つかりませんでした。',
        ]);
    }

    public function test_google_books_apiがエラーの場合はそのステータスを返す(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'error' => [
                    'message' => 'API error',
                ],
            ], 429),
        ]);

        $response = $this->getJson(
            '/books/isbn/9784873115658'
        );

        $response->assertStatus(429);

        $response->assertExactJson([
            'message' => 'Google Books APIから書籍情報を取得できませんでした。',
        ]);
    }

    public function test_isbnが13桁でない場合は422になる(): void
    {
        $response = $this->getJson(
            '/books/isbn/123'
        );

        $response->assertStatus(422);

        $response->assertExactJson([
            'message' => 'ISBNは13桁で指定してください。',
        ]);
    }
}
