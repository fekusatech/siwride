<?php

use App\Services\DisplayCurrency;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('currency:refresh', function (DisplayCurrency $currency): void {
    $snapshot = $currency->refresh();
    $this->info('Exchange rates updated: '.$snapshot['date']);
})->purpose('Refresh IDR display exchange rates for USD and GBP');

Schedule::command('currency:refresh')->dailyAt('17:00')->timezone('UTC')->withoutOverlapping();
