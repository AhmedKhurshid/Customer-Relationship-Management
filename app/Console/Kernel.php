<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use DateTimeZone;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected function scheduleTimezone(): DateTimeZone|string|null
    {
        return 'Asia/Karachi';
    }

    protected function schedule(Schedule $schedule): void
    {

        $schedule->command('token:expire')
            // $schedule->command('schedule:work')
            ->timezone('Asia/Karachi')
            ->dailyAt('20:10');



        // $schedule->call(function () {
        //     DB::table('oauth_access_tokens')->delete();
        // })->timezone('Asia/Karachi')
        //     ->dailyAt('20:18');



        // $schedule->call(function () {
        //     DB::table('recent_users')->delete();
        // })->daily();



        // $schedule->command('inspire')->hourly();

        // $schedule->call('token:expire')->when(function () {
        // });

        // $schedule->call('token:expire')->everySecond();

        // $schedule->call('token:expire')->everySecond()->when(function () {
        // });

        // $schedule->command('token:expire')->when(function () {
        // });

        // $schedule->command('token:expire')->everySecond();




        // $schedule->command('token:expire')->everySecond()->when(function () {
        // });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
