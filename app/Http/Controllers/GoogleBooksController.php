<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleBooksController extends Controller
{
    /**
     * ISBNを使ってGoogle Books APIから書籍情報を取得する。
     */
    public function search(Request $request): JsonResponse
    {
        $isbn = $request->query('isbn');

        $response = Http::withHeaders([
            'X-Goog-Api-Key' => env('GOOGLE_BOOKS_API_KEY'),
        ])->get(
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
}
