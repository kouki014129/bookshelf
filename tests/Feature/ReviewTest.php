<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーはコメントありでレビューを投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 5,
                'comment' => 'とても分かりやすい書籍でした。',
            ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても分かりやすい書籍でした。',
        ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを投稿しました');
    }

    public function test_コメントなしでもレビューを投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 4,
                'comment' => null,
            ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => null,
        ]);

        $response->assertRedirect(route('books.show', $book));
    }

    public function test_評価が未入力の場合はレビューを投稿できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.store', $book), [
                'rating' => null,
                'comment' => 'コメントのみ',
            ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_評価が1未満の場合はレビューを投稿できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.store', $book), [
                'rating' => 0,
                'comment' => null,
            ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_評価が5を超える場合はレビューを投稿できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.store', $book), [
                'rating' => 6,
                'comment' => null,
            ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_コメントが1000文字を超える場合はレビューを投稿できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.store', $book), [
                'rating' => 5,
                'comment' => str_repeat('あ', 1001),
            ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHasErrors('comment');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_同じユーザーは同じ書籍に2件目のレビューを投稿できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 3,
                'comment' => '2件目のレビュー',
            ]);

        $this->assertDatabaseCount('reviews', 1);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas(
            'error',
            '1つの書籍に投稿できるレビューは1件までです。'
        );
    }

    public function test_投稿者はレビュー編集画面を表示できる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reviews.edit', $review));

        $response->assertStatus(200);
        $response->assertViewIs('reviews.edit');
        $response->assertViewHas('review', function (Review $viewReview) use ($review): bool {
            return $viewReview->id === $review->id;
        });
    }

    public function test_投稿者はレビューを更新できる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 3,
            'comment' => '更新前のコメント',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => 5,
                'comment' => '更新後のコメント',
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => '更新後のコメント',
        ]);

        $response->assertRedirect(route('books.show', $review->book_id));
        $response->assertSessionHas('success', 'レビューを更新しました');
    }

    public function test_投稿者はコメントをnullに更新できる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'comment' => '更新前のコメント',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => 4,
                'comment' => null,
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => null,
        ]);

        $response->assertRedirect(route('books.show', $review->book_id));
    }

    public function test_他ユーザーはレビュー編集画面を表示できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->get(route('reviews.edit', $review));

        $response->assertForbidden();
    }

    public function test_他ユーザーはレビューを更新できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
            'rating' => 3,
            'comment' => '変更前',
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->put(route('reviews.update', $review), [
                'rating' => 5,
                'comment' => '不正な更新',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '変更前',
        ]);
    }

    public function test_投稿者はレビューを削除できる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $bookId = $review->book_id;

        $response = $this
            ->actingAs($user)
            ->delete(route('reviews.destroy', $review));

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        $response->assertRedirect(route('books.show', $bookId));
        $response->assertSessionHas('success', 'レビューを削除しました');
    }

    public function test_レビュー削除時に関連いいねも削除される(): void
    {
        $reviewUser = User::factory()->create();
        $likeUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $reviewUser->id,
        ]);

        $likeUser->likedReviews()->attach($review->id);

        $bookId = $review->book_id;

        $response = $this
            ->actingAs($reviewUser)
            ->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $bookId));
        $response->assertSessionHas('success', 'レビューを削除しました');

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $likeUser->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_他ユーザーはレビューを削除できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->delete(route('reviews.destroy', $review));

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}
