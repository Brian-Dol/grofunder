<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('loans:monitor')->dailyAt('08:00');
        
        // Send agent performance reports daily at 9 AM
        $schedule->command('reports:send-scheduled --type=agent')
            ->dailyAt('09:00')
            ->name('send-agent-reports')
            ->onOneServer();
        
        // Send system analytics reports every Monday at 8 AM
        $schedule->command('reports:send-scheduled --type=system')
            ->weeklyOn(1, '08:00') // 1 = Monday
            ->name('send-system-reports')
            ->onOneServer();
        
        // Send investor portfolio reports every Monday at 10 AM
        $schedule->command('reports:send-scheduled --type=investor')
            ->weeklyOn(1, '10:00') // 1 = Monday
            ->name('send-investor-reports')
            ->onOneServer();
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
