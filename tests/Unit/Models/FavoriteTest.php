<?php

namespace Tests\Unit\Models;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillableが正しく設定されている(): void
    {
        $favorite = new Favorite;

        $this->assertSame([
            'user_id',
            'book_id',
        ], $favorite->getFillable());
    }

    public function test_userリレーションがbelongs_toである(): void
    {
        $favorite = new Favorite;

        $this->assertInstanceOf(
            BelongsTo::class,
            $favorite->user()
        );
    }

    public function test_bookリレーションがbelongs_toである(): void
    {
        $favorite = new Favorite;

        $this->assertInstanceOf(
            BelongsTo::class,
            $favorite->book()
        );
    }

    public function test_同一ユーザーは同じ書籍を重複してお気に入り登録できない(): void
    {
        $favorite = Favorite::factory()->create();

        $this->expectException(QueryException::class);

        Favorite::factory()->create([
            'user_id' => $favorite->user_id,
            'book_id' => $favorite->book_id,
        ]);
    }
}
