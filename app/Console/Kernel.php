<?php

namespace App\Console;

use App\Console\Commands\ExpireReadingPlans;
use App\Console\Commands\SendReadingPlanReminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule
            ->command(SendReadingPlanReminder::class)
            ->daily();

        $schedule
            ->command(ExpireReadingPlans::class)
            ->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
