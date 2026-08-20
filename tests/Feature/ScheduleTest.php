<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    public function test_読書計画リマインダー通知コマンドが毎日実行されるように登録されている(): void
    {
        $event = $this->findScheduledEvent('app:send-reading-plan-reminder');

        $this->assertNotNull($event);
        $this->assertSame('0 0 * * *', $event->expression);
    }

    public function test_期限切れ読書計画更新コマンドが毎日実行されるように登録されている(): void
    {
        $event = $this->findScheduledEvent('app:expire-reading-plans');

        $this->assertNotNull($event);
        $this->assertSame('0 0 * * *', $event->expression);
    }

    private function findScheduledEvent(string $command): ?Event
    {
        $events = app(Schedule::class)->events();

        foreach ($events as $event) {
            if (str_contains($event->command, $command)) {
                return $event;
            }
        }

        return null;
    }
}
