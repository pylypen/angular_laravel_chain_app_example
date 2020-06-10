<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\WeeklyReport::class,
        Commands\CreateDevelopersAccount::class,
        Commands\RemindDevelopersAccount::class,
        Commands\CheckEmailDaemon::class,
        Commands\SetLessonsMediaOrder::class,
        Commands\RestartEmailDaemon::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (env('APP_ENV', false) == 'production') {
            /** #48 Kill weekly org admin email for now **/
            // $schedule->command('command:weekly-report')->weeklyOn(1, '10:00');
        }
        
        $schedule->command('command:check-email-daemon')->hourly();
        $schedule->command('command:restart-email-daemon')->dailyAt('10:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
