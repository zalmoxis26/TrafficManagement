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


// COMANDO PARA PASAR DE FTP A AWS S3


Artisan::command('schedule:adp:sai-process-ftp', function () {
    Artisan::call('dispatch:adp-sai-process-ftp');
})->describe('Programa un job FTP para respladar en AWS');







