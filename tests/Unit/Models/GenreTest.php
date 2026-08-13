<?php

namespace Tests\Unit\Models;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillableが正しく設定されている(): void
    {
        $genre = new Genre;

        $this->assertSame([
            'name',
        ], $genre->getFillable());
    }

    public function test_booksリレーションがbelongs_to_manyである(): void
    {
        $genre = new Genre;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $genre->books()
        );
    }

    public function test_ジャンル名は重複登録できない(): void
    {
        Genre::factory()->create([
            'name' => 'ミステリー',
        ]);

        $this->expectException(QueryException::class);

        Genre::factory()->create([
            'name' => 'ミステリー',
        ]);
    }
}
