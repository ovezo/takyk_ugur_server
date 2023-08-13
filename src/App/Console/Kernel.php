<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected $commands = [
        Commands\ImportImdatData::class,
        Commands\Notification::class,
       // Commands\RefreshBusLocationsCommand::class
    ];
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function(){
           $count = 1;
           while($count<8){
                Artisan::call('bus:gps');
                sleep(5);
                $count++;
            }
        })->everyMinute();
        $schedule->command('import:imdat')->everyFiveMinutes();
        $schedule->command('send:notification')->everyMinute();
    }
    //protected function shortSchedule(\Spatie\ShortSchedule\ShortSchedule $shortSchedule)
    //{
       // this command will run every 30 seconds
     //  $shortSchedule->command('bus:gps')->everySeconds(30)->withoutOverlapping();
    //}

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
