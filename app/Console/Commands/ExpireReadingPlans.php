<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireReadingPlans extends Command
{
    /**
     * Artisanコマンド名。
     *
     * @var string
     */
    protected $signature = 'app:expire-reading-plans';

    /**
     * Artisanコマンドの説明。
     *
     * @var string
     */
    protected $description = '期限切れの読書計画を失効状態に更新します。';

    /**
     * 期限切れかつ未完了の読書計画を失効状態に更新する。
     *
     * @return int コマンドの終了ステータス
     */
    public function handle(): int
    {
        $count = ReadingPlan::query()
            ->whereDate('deadline', '<', Carbon::today())
            ->where('status', ReadingPlanStatus::Planning->value)
            ->update([
                'status' => ReadingPlanStatus::Expired->value,
            ]);

        $this->info("{$count}件の読書計画を失効しました。");

        return self::SUCCESS;
    }
}
