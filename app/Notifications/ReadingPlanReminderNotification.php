<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ReadingPlan $readingPlan
    ) {}

    /**
     * 通知の配信チャンネルを指定する。
     *
     * @param  object  $notifiable  通知対象ユーザー
     * @return array<int, string> 通知チャンネル
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * データベースに保存する通知内容を返す。
     *
     * @param  object  $notifiable  通知対象ユーザー
     * @return array<string, mixed> 通知データ
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'reading_plan_id' => $this->readingPlan->id,
            'book_id' => $this->readingPlan->book_id,
            'book_title' => $this->readingPlan->book->title,
            'deadline' => $this->readingPlan->deadline->format('Y-m-d'),
            'message' => "「{$this->readingPlan->book->title}」の読書期限は明日です。",
            'url' => route('reading-plans.index'),
        ];
    }
}
