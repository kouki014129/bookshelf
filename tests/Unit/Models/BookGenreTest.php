<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookGenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_同じ書籍とジャンルの組み合わせは重複登録できない(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        DB::table('book_genre')->insert([
            'book_id' => $book->id,
            'genre_id' => $genre->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('book_genre')->insert([
            'book_id' => $book->id,
            'genre_id' => $genre->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
