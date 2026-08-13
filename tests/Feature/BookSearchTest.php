<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_タイトルのキーワードで検索できる(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'PHP基礎',
            'author' => '佐藤花子',
        ]);

        $response = $this->get('/books?keyword=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
        $response->assertDontSee('PHP基礎');
    }

    public function test_著者名のキーワードで検索できる(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'PHP基礎',
            'author' => '佐藤花子',
        ]);

        $response = $this->get('/books?keyword=山田');

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
        $response->assertDontSee('PHP基礎');
    }

    public function test_ジャンルで書籍を絞り込める(): void
    {
        $businessGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        $novelGenre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $businessBook = Book::factory()->create([
            'title' => '人を動かす',
        ]);

        $novelBook = Book::factory()->create([
            'title' => '吾輩は猫である',
        ]);

        $businessBook->genres()->attach($businessGenre->id);
        $novelBook->genres()->attach($novelGenre->id);

        $response = $this->get(
            '/books?genre='.$businessGenre->id
        );

        $response->assertStatus(200);
        $response->assertSee('人を動かす');
        $response->assertDontSee('吾輩は猫である');
    }

    public function test_キーワードとジャンルを組み合わせて検索できる(): void
    {
        $techGenre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $businessGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        $targetBook = Book::factory()->create([
            'title' => 'Laravel実践入門',
            'author' => '山田太郎',
        ]);

        $sameKeywordOtherGenreBook = Book::factory()->create([
            'title' => 'Laravel仕事術',
            'author' => '佐藤花子',
        ]);

        $sameGenreOtherKeywordBook = Book::factory()->create([
            'title' => 'PHP基礎',
            'author' => '鈴木一郎',
        ]);

        $targetBook->genres()->attach($techGenre->id);
        $sameKeywordOtherGenreBook->genres()->attach($businessGenre->id);
        $sameGenreOtherKeywordBook->genres()->attach($techGenre->id);

        $response = $this->get(
            '/books?keyword=Laravel&genre='.$techGenre->id
        );

        $response->assertStatus(200);
        $response->assertSee('Laravel実践入門');
        $response->assertDontSee('Laravel仕事術');
        $response->assertDontSee('PHP基礎');
    }

    public function test_新しい順に並べ替えできる(): void
    {
        $oldBook = Book::factory()->create([
            'title' => '古い本',
            'created_at' => now()->subDays(2),
        ]);

        $newBook = Book::factory()->create([
            'title' => '新しい本',
            'created_at' => now(),
        ]);

        $response = $this->get('/books?sort=latest');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $newBook->title,
            $oldBook->title,
        ]);
    }

    public function test_古い順に並べ替えできる(): void
    {
        $oldBook = Book::factory()->create([
            'title' => '古い本',
            'created_at' => now()->subDays(2),
        ]);

        $newBook = Book::factory()->create([
            'title' => '新しい本',
            'created_at' => now(),
        ]);

        $response = $this->get('/books?sort=oldest');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $oldBook->title,
            $newBook->title,
        ]);
    }

    public function test_タイトル順に並べ替えできる(): void
    {
        $bookB = Book::factory()->create([
            'title' => 'Bの本',
        ]);

        $bookA = Book::factory()->create([
            'title' => 'Aの本',
        ]);

        $response = $this->get('/books?sort=title');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $bookA->title,
            $bookB->title,
        ]);
    }

    public function test_評価順に並べ替えできレビューなしは最後になる(): void
    {
        $highRatedBook = Book::factory()->create([
            'title' => '高評価の本',
        ]);

        $lowRatedBook = Book::factory()->create([
            'title' => '低評価の本',
        ]);

        $noReviewBook = Book::factory()->create([
            'title' => 'レビューなしの本',
        ]);

        Review::factory()->create([
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $lowRatedBook->id,
            'rating' => 3,
        ]);

        $response = $this->get('/books?sort=rating');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $highRatedBook->title,
            $lowRatedBook->title,
            $noReviewBook->title,
        ]);
    }
}
