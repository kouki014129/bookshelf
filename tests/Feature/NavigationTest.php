<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインユーザーのヘッダーに応用機能へのリンクが表示される(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('books.index'));

        $response->assertOk();

        $response->assertSee(route('reports.index'), false);
        $response->assertSee(route('reading-plans.index'), false);
        $response->assertSee(route('notifications.index'), false);

        $response->assertSee('マイレポート');
        $response->assertSee('読書計画');
        $response->assertSee('aria-label="通知一覧"', false);
    }
}
