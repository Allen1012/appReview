<?php

namespace App\Console;

use App\Model\AdsDailyCampaignReport;
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
//        date_default_timezone_set("PRC");
//        $schedule->exec('echo "hahaha_" >>  aa.log')->everyMinute();
//
//        $model = new AdsDailyCampaignReport();
//        $model->apple_id = date('mdHis');
//        $model->date = date('Y-m-d H:i:s');
//        $model->campaign_id = date('dHis');
//        $model->campaign = 'hahah';
//        $model->installs = 125;
//        $model->spent = 234;
//        $model->save();

        // $schedule->command('inspire')

        //          ->hourly();
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
