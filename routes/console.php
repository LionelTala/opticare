<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Envoyer les rappels de RDV tous les jours à 18h00
Schedule::command('rdv:envoyer-rappels')->dailyAt('18:00');