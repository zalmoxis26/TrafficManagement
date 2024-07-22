<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


//COMANDO PARA DESCARGAR Y PROCESAR FTP 

Artisan::command('schedule:ftp-job', function () {
    Artisan::call('dispatch:ftp-job');
})->describe('Programa un job FTP para download and store files every minute');