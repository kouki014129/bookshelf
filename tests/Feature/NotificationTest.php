<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_logged_in_user_can_view_own_database_notification(): void
    {
        $user = User::factory()->create();

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'reading_plan_reminder',
            'data' => [
                'message' => '「リーダブルコード」の読書期限は明日です。',
                'deadline' => now()->addDay()->format('Y-m-d'),
                'url' => route('reading-plans.index'),
            ],
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('「リーダブルコード」の読書期限は明日です。');
        $response->assertSee('既読にする');
    }

    public function test_logged_in_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();

        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'reading_plan_reminder',
            'data' => [
                'message' => '「リーダブルコード」の読書期限は明日です。',
                'deadline' => now()->addDay()->format('Y-m-d'),
                'url' => route('reading-plans.index'),
            ],
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('notifications.read', $notification));

        $response->assertRedirect(route('notifications.index'));

        $this->assertNotNull(
            $notification->fresh()->read_at
        );
    }

    public function test_logged_in_user_cannot_mark_other_user_notification_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $notification = $otherUser->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'reading_plan_reminder',
            'data' => [
                'message' => '他ユーザーの通知です。',
                'deadline' => now()->addDay()->format('Y-m-d'),
                'url' => route('reading-plans.index'),
            ],
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('notifications.read', $notification));

        $response->assertForbidden();

        $this->assertNull(
            $notification->fresh()->read_at
        );
    }
}
