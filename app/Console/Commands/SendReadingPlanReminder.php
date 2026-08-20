<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendReadingPlanReminder extends Command
{
    /**
     * Artisanコマンド名。
     *
     * @var string
     */
    protected $signature = 'app:send-reading-plan-reminder';

    /**
     * Artisanコマンドの説明。
     *
     * @var string
     */
    protected $description = '明日期限の読書計画をユーザーへリマインドします。';

    /**
     * 明日期限の未完了読書計画を取得し、データベース通知を保存する。
     *
     * @return int コマンドの終了ステータス
     */
    public function handle(): int
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        $readingPlans = ReadingPlan::query()
            ->with(['user', 'book'])
            ->whereDate('deadline', $tomorrow)
            ->where('status', ReadingPlanStatus::Planning->value)
            ->get();

        $readingPlans->each(function (ReadingPlan $readingPlan): void {
            Notification::send(
                $readingPlan->user,
                new ReadingPlanReminderNotification($readingPlan)
            );
        });

        $this->info("{$readingPlans->count()}件の読書計画リマインダーを送信しました。");

        return self::SUCCESS;
    }
}
