<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands =[
        Commands\CrawlCommands::class,
        Commands\CrawlProductOriginalCommand::class,
        Commands\CrawlProductPartnerCommand::class,
        Commands\ActivePartnerCommand::class,
    ];


    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('crawl:productpartner')->dailyAt('08:43')->timezone('Asia/Ho_Chi_Minh');;
        $schedule->command('crawl:data')->dailyAt('08:43')->timezone('Asia/Ho_Chi_Minh');
        $schedule->command('crawl:productoriginal')->dailyAt('08:43')->timezone('Asia/Ho_Chi_Minh');;
        $schedule->command('cron:partner')->dailyAt('08:43')->timezone('Asia/Ho_Chi_Minh');
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
