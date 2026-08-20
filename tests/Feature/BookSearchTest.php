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
        $bookC = Book::factory()->create([
            'title' => 'C言語入門',
        ]);

        $bookA = Book::factory()->create([
            'title' => 'AWS入門',
        ]);

        $bookB = Book::factory()->create([
            'title' => 'Book管理術',
        ]);

        $response = $this->get('/books?sort=title');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $bookA->title,
            $bookB->title,
            $bookC->title,
        ]);
    }

    public function test_評価が高い順に並べ替えできレビューなしは最後になる(): void
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

    public function test_検索条件と並び順を組み合わせて使える(): void
    {
        $techGenre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $businessGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        $highRatedTargetBook = Book::factory()->create([
            'title' => 'Laravel高評価',
            'author' => '山田太郎',
        ]);

        $lowRatedTargetBook = Book::factory()->create([
            'title' => 'Laravel低評価',
            'author' => '山田太郎',
        ]);

        $otherGenreBook = Book::factory()->create([
            'title' => 'Laravel別ジャンル',
            'author' => '山田太郎',
        ]);

        $highRatedTargetBook->genres()->attach($techGenre->id);
        $lowRatedTargetBook->genres()->attach($techGenre->id);
        $otherGenreBook->genres()->attach($businessGenre->id);

        Review::factory()->create([
            'book_id' => $highRatedTargetBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $lowRatedTargetBook->id,
            'rating' => 2,
        ]);

        Review::factory()->create([
            'book_id' => $otherGenreBook->id,
            'rating' => 1,
        ]);

        $response = $this->get(
            '/books?keyword=Laravel&genre='
            .$techGenre->id
            .'&sort=rating'
        );

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $highRatedTargetBook->title,
            $lowRatedTargetBook->title,
        ]);
        $response->assertDontSee($otherGenreBook->title);
    }

    public function test_ページネーションリンクに検索条件が引き継がれる(): void
    {
        $genre = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $books = Book::factory()
            ->count(11)
            ->create([
                'author' => '山田太郎',
            ]);

        foreach ($books as $index => $book) {
            $book->update([
                'title' => sprintf('Laravel実践 %02d', $index + 1),
            ]);

            $book->genres()->attach($genre->id);
        }

        $response = $this->get(
            '/books?keyword=Laravel&genre='.$genre->id.'&sort=title'
        );

        $response->assertStatus(200);

        $response->assertSee('keyword=Laravel', false);
        $response->assertSee('genre='.$genre->id, false);
        $response->assertSee('sort=title', false);
        $response->assertSee('page=2', false);
    }
}
