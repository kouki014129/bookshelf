<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBooksTest extends TestCase
{
    public function test_isb_nから書籍情報を取得できる(): void
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
            '/google-books?isbn=9784873115658'
        );

        $response->assertStatus(200);

        $response->assertExactJson([
            'title' => 'リーダブルコード',
            'author' => 'Dustin Boswell, Trevor Foucher',
            'published_date' => '2012-06-23',
            'description' => '読みやすいコードについて解説する書籍です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        Http::assertSent(function ($request) {
            return $request->url()
                === 'https://www.googleapis.com/books/v1/volumes?q=isbn%3A9784873115658';
        });
    }

    public function test_存在しない_isb_nでは404になる(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
            ], 200),
        ]);

        $response = $this->getJson(
            '/google-books?isbn=9999999999999'
        );

        $response->assertStatus(404);

        $response->assertExactJson([
            'message' => '書籍情報が見つかりませんでした。',
        ]);
    }

    public function test_google_books_ap_iがエラーの場合はそのステータスを返す(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'error' => [
                    'message' => 'API error',
                ],
            ], 429),
        ]);

        $response = $this->getJson(
            '/google-books?isbn=9784873115658'
        );

        $response->assertStatus(429);

        $response->assertExactJson([
            'message' => 'Google Books APIから書籍情報を取得できませんでした。',
        ]);
    }
}
