<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| Here is where you may define all of your Closure based console commands
| and scheduled tasks.
|
*/

// Jika command ter-register (signature 'db:backup'):
// Schedule::command('db:backup')->dailyAt('21:00');
// Schedule::command('db:backup')->everyMinute();

// Alternatif jika command tidak auto-registered
// memanggil artisan secara langsung:
// Schedule::call(function () {
//     Artisan::call('db:backup');
// })->dailyAt('21:00');
