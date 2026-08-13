<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーはジャンル一覧を表示できる(): void
    {
        $user = User::factory()->create();

        Genre::factory()->count(3)->create();

        $response = $this
            ->actingAs($user)
            ->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertViewIs('genres.index');
        $response->assertViewHas('genres');
    }

    public function test_認証済みユーザーはジャンル登録画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('genres.create'));

        $response->assertStatus(200);
        $response->assertViewIs('genres.create');
    }

    public function test_認証済みユーザーはジャンル詳細を表示できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertViewIs('genres.show');
        $response->assertViewHas('genre', function (Genre $viewGenre) use ($genre) {
            return $viewGenre->id === $genre->id;
        });
        $response->assertViewHas('books');
    }

    public function test_ジャンルを登録できる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('genres.store'), [
                'name' => 'ミステリー',
            ]);

        $this->assertDatabaseHas('genres', [
            'name' => 'ミステリー',
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを登録しました');
    }

    public function test_ジャンル名が未入力の場合は登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('genres.create'))
            ->post(route('genres.store'), [
                'name' => '',
            ]);

        $response->assertRedirect(route('genres.create'));
        $response->assertSessionHasErrors('name');

        $this->assertDatabaseCount('genres', 0);
    }

    public function test_ジャンル名が20文字を超える場合は登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('genres.create'))
            ->post(route('genres.store'), [
                'name' => str_repeat('あ', 21),
            ]);

        $response->assertRedirect(route('genres.create'));
        $response->assertSessionHasErrors('name');

        $this->assertDatabaseCount('genres', 0);
    }

    public function test_重複したジャンル名では登録できない(): void
    {
        $user = User::factory()->create();

        Genre::factory()->create([
            'name' => 'ミステリー',
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('genres.create'))
            ->post(route('genres.store'), [
                'name' => 'ミステリー',
            ]);

        $response->assertRedirect(route('genres.create'));
        $response->assertSessionHasErrors('name');

        $this->assertDatabaseCount('genres', 1);
    }

    public function test_認証済みユーザーはジャンル編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('genres.edit', $genre));

        $response->assertStatus(200);
        $response->assertViewIs('genres.edit');
        $response->assertViewHas('genre', function (Genre $viewGenre) use ($genre) {
            return $viewGenre->id === $genre->id;
        });
    }

    public function test_ジャンル名を更新できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => '歴史小説',
            ]);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '歴史小説',
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを更新しました');
    }

    public function test_自身のジャンル名は変更せずに更新できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => 'ミステリー',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => 'ミステリー',
            ]);

        $response->assertSessionDoesntHaveErrors('name');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'ミステリー',
        ]);
    }

    public function test_他のジャンルと重複する名前には更新できない(): void
    {
        $user = User::factory()->create();

        Genre::factory()->create([
            'name' => 'ミステリー',
        ]);

        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('genres.edit', $genre))
            ->put(route('genres.update', $genre), [
                'name' => 'ミステリー',
            ]);

        $response->assertRedirect(route('genres.edit', $genre));
        $response->assertSessionHasErrors('name');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '小説',
        ]);
    }

    public function test_書籍が紐付いていないジャンルは削除できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('genres.destroy', $genre));

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを削除しました');
    }

    public function test_書籍が紐付いているジャンルは削除できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $book->genres()->attach($genre->id);

        $response = $this
            ->actingAs($user)
            ->delete(route('genres.destroy', $genre));

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas(
            'error',
            '書籍が登録されているジャンルは削除できません'
        );
    }
}
