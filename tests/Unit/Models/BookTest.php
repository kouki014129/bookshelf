<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillableが正しく設定されている(): void
    {
        $book = new Book;

        $this->assertSame([
            'title',
            'author',
            'isbn',
            'published_date',
            'description',
            'image_url',
            'user_id',
        ], $book->getFillable());
    }

    public function test_userリレーションがbelongs_toである(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            BelongsTo::class,
            $book->user()
        );
    }

    public function test_reviewsリレーションがhas_manyである(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            HasMany::class,
            $book->reviews()
        );
    }

    public function test_genresリレーションがbelongs_to_manyである(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->genres()
        );
    }

    public function test_favoritesリレーションがbelongs_to_manyである(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->favorites()
        );
    }

    public function test_isbnは重複登録できない(): void
    {
        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        $this->expectException(QueryException::class);

        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);
    }
}
