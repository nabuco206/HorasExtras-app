<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar la expiración diaria de bolsones
app(Schedule::class)->command('bolson:expirar --force')
    ->daily()
    ->at('01:00')
    ->description('Expirar bolsones de tiempo vencidos');
