<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FlashMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_読書計画の操作成功時にフラッシュメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $createResponse = $this
            ->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'deadline' => now()->addDays(3)->format('Y-m-d'),
            ]);

        $createResponse->assertRedirect(route('reading-plans.index'));
        $createResponse->assertSessionHas('success', '読書計画を登録しました。');

        $readingPlan = ReadingPlan::query()
            ->where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->firstOrFail();

        $updateResponse = $this
            ->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), [
                'deadline' => now()->addDays(7)->format('Y-m-d'),
            ]);

        $updateResponse->assertRedirect(route('reading-plans.index'));
        $updateResponse->assertSessionHas('success', '読書計画を更新しました。');

        $completeResponse = $this
            ->actingAs($user)
            ->post(route('reading-plans.complete', $readingPlan));

        $completeResponse->assertRedirect(route('reading-plans.index'));
        $completeResponse->assertSessionHas('success', '読了しました。');

        $deleteResponse = $this
            ->actingAs($user)
            ->delete(route('reading-plans.destroy', $readingPlan));

        $deleteResponse->assertRedirect(route('reading-plans.index'));
        $deleteResponse->assertSessionHas('success', '読書計画を削除しました。');
    }

    public function test_読書計画の操作失敗時にフラッシュメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $completedPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->addDays(3)->format('Y-m-d'),
            'status' => ReadingPlanStatus::Completed,
        ]);

        $expiredPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->subDay()->format('Y-m-d'),
            'status' => ReadingPlanStatus::Expired,
        ]);

        $editResponse = $this
            ->actingAs($user)
            ->get(route('reading-plans.edit', $completedPlan));

        $editResponse->assertRedirect(route('reading-plans.index'));
        $editResponse->assertSessionHas('error', '読了済みまたは期限切れの読書計画は編集できません。');

        $updateResponse = $this
            ->actingAs($user)
            ->put(route('reading-plans.update', $completedPlan), [
                'deadline' => now()->addDays(10)->format('Y-m-d'),
            ]);

        $updateResponse->assertRedirect(route('reading-plans.index'));
        $updateResponse->assertSessionHas('error', '読了済みまたは期限切れの読書計画は更新できません。');

        $completeResponse = $this
            ->actingAs($user)
            ->post(route('reading-plans.complete', $expiredPlan));

        $completeResponse->assertRedirect(route('reading-plans.index'));
        $completeResponse->assertSessionHas('error', '計画中の読書計画のみ読了できます。');
    }

    public function test_通知を既読にした時にフラッシュメッセージが表示される(): void
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
            ->post(route('notifications.read', $notification));

        $response->assertRedirect(route('notifications.index'));
        $response->assertSessionHas('success', '通知を既読にしました。');
    }
}
