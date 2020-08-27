<?php

namespace App\Console;

use App\Console\Commands\AccountCredit;
use App\Console\Commands\AppDetect;
use App\Console\Commands\ChannelCpmTj;
use App\Console\Commands\DateStatis;
use App\Console\Commands\SubTaskMonthSum;
use Carbon\Carbon;
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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('bill:generate')
            ->runInBackground()
            ->monthlyOn(8, '00:50');
        $schedule->command(SubTaskMonthSum::class)->runInBackground()->monthlyOn(8);
        $schedule->command(ChannelCpmTj::class, ["1", "1"])->runInBackground()->dailyAt("0:30");
        $schedule->command(DateStatis::class, [Carbon::now()->subDay()->format('Ymd')])->runInBackground()->dailyAt("0:30");
        $schedule->command(ChannelCpmTj::class)->runInBackground()->withoutOverlapping()->everyTenMinutes();
        $schedule->command(AccountCredit::class)->runInBackground()->withoutOverlapping()->everyFiveMinutes();
        // $schedule->command(AppDetect::class)->runInBackground()->withoutOverlapping()->everyFiveMinutes();
        $schedule->command(DateStatis::class)->runInBackground()->hourly();
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
