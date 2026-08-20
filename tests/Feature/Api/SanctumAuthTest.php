<?php

namespace Tests\Feature\Api;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_正しい認証情報でログインするとトークンが返る(): void
    {
        $user = User::factory()->create([
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'yamada@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();

        $response->assertJsonPath(
            'message',
            'ログインしました。'
        );

        $response->assertJsonStructure([
            'message',
            'token',
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'api-token',
        ]);
    }

    public function test_誤った認証情報ではログインできない(): void
    {
        User::factory()->create([
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'yamada@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);

        $response->assertJsonPath(
            'errors.email.0',
            '認証情報が正しくありません。'
        );

        $this->assertDatabaseCount(
            'personal_access_tokens',
            0
        );
    }

    public function test_トークンで保護された書籍登録apiにアクセスできる(): void
    {
        $user = User::factory()->create();

        $token = $user
            ->createToken('api-token')
            ->plainTextToken;

        $genre = Genre::factory()->create();

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/books', [
                'title' => 'Laravel API入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2026-08-13',
                'description' => 'API認証のテストです。',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [
                    $genre->id,
                ],
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('books', [
            'title' => 'Laravel API入門',
            'isbn' => '9781234567890',
            'user_id' => $user->id,
        ]);
    }

    public function test_ログアウトすると現在のトークンが削除される(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('api-token');

        $response = $this
            ->withHeader(
                'Authorization',
                'Bearer '.$token->plainTextToken
            )
            ->postJson('/api/logout');

        $response->assertOk();

        $response->assertJsonPath(
            'message',
            'ログアウトしました。'
        );

        $this->assertDatabaseCount(
            'personal_access_tokens',
            0
        );
    }

    public function test_未認証ではログアウトできない(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertUnauthorized();
    }
}
