<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_can_view_notifications_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('通知はありません。');
    }

    public function test_navigation_contains_link_to_notifications_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('books.index'));

        $response->assertOk();

        $response->assertSee(
            route('notifications.index'),
            false
        );

        $response->assertSee('aria-label="通知一覧"', false);
    }
}
