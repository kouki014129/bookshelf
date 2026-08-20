<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendReadingPlanReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_tomorrow_planning_reading_plan_is_notified(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'name' => '山田 光輝',
        ]);

        $book = Book::factory()->create([
            'title' => 'リーダブルコード',
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        $this->artisan('app:send-reading-plan-reminder')
            ->expectsOutput('1件の読書計画リマインダーを送信しました。')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function (ReadingPlanReminderNotification $notification) use ($readingPlan) {
                return $notification->toDatabase($readingPlan->user)['reading_plan_id'] === $readingPlan->id
                    && $notification->toDatabase($readingPlan->user)['book_title'] === 'リーダブルコード'
                    && $notification->toDatabase($readingPlan->user)['deadline'] === now()->addDay()->format('Y-m-d');
            }
        );
    }

    public function test_completed_reading_plan_is_not_notified(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'deadline' => now()->addDay()->format('Y-m-d'),
            'status' => 'completed',
        ]);

        $this->artisan('app:send-reading-plan-reminder')
            ->expectsOutput('0件の読書計画リマインダーを送信しました。')
            ->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_today_past_and_future_reading_plans_are_not_notified(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $todayBook = Book::factory()->create();
        $pastBook = Book::factory()->create();
        $futureBook = Book::factory()->create();

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $todayBook->id,
            'deadline' => now()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $pastBook->id,
            'deadline' => now()->subDay()->format('Y-m-d'),
            'status' => 'planning',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $futureBook->id,
            'deadline' => now()->addDays(2)->format('Y-m-d'),
            'status' => 'planning',
        ]);

        $this->artisan('app:send-reading-plan-reminder')
            ->expectsOutput('0件の読書計画リマインダーを送信しました。')
            ->assertExitCode(0);

        Notification::assertNothingSent();
    }
}
