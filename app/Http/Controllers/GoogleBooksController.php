<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GoogleBooksController extends Controller
{
    /**
     * ISBNを使ってGoogle Books APIから書籍情報を取得する。
     *
     * @param  string  $isbn  検索対象のISBN-13
     * @return JsonResponse 書籍情報のJSONレスポンス
     */
    public function search(string $isbn): JsonResponse
    {
        if (! preg_match('/^\d{13}$/', $isbn)) {
            return response()->json([
                'message' => 'ISBNは13桁で指定してください。',
            ], 422);
        }

        $response = $this->googleBooksClient()
            ->get(
                'https://www.googleapis.com/books/v1/volumes',
                [
                    'q' => 'isbn:'.$isbn,
                ]
            );

        if ($response->failed()) {
            return response()->json([
                'message' => 'Google Books APIから書籍情報を取得できませんでした。',
            ], $response->status());
        }

        $data = $response->json();

        if (empty($data['items'])) {
            return response()->json([
                'message' => '書籍情報が見つかりませんでした。',
            ], 404);
        }

        $volumeInfo = $data['items'][0]['volumeInfo'];

        return response()->json([
            'title' => $volumeInfo['title'] ?? null,
            'author' => isset($volumeInfo['authors'])
                ? implode(', ', $volumeInfo['authors'])
                : null,
            'published_date' => $volumeInfo['publishedDate'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
        ]);
    }

    /**
     * Google Books API用のHTTPクライアントを生成する。
     *
     * @return PendingRequest HTTPクライアント
     */
    private function googleBooksClient(): PendingRequest
    {
        $apiKey = config('services.google_books.api_key');

        $client = Http::acceptJson();

        if (is_string($apiKey) && $apiKey !== '') {
            return $client->withHeaders([
                'X-Goog-Api-Key' => $apiKey,
            ]);
        }

        return $client;
    }
}
