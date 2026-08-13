<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_未認証ユーザーはトップページを表示できる(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_未認証ユーザーは書籍登録画面へアクセスできない(): void
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    public function test_未認証ユーザーはジャンル一覧へアクセスできない(): void
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }

    public function test_未認証ユーザーはお気に入り一覧へアクセスできない(): void
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }

    public function test_認証済みユーザーはトップページを表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/');

        $response->assertStatus(200);
    }

    public function test_認証済みユーザーは書籍登録画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/books/create');

        $response->assertStatus(200);
    }

    public function test_認証済みユーザーはジャンル一覧を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/genres');

        $response->assertStatus(200);
    }

    public function test_認証済みユーザーはお気に入り一覧を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/favorites');

        $response->assertStatus(200);
    }
}
