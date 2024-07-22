<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessFtpFiles;

class DispatchFtpJob extends Command
{
    protected $signature = 'dispatch:ftp-job';
    protected $description = 'Dispatch FTP job to process files';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ProcessFtpFiles::dispatch();
        $this->info('FTP job dispatched successfully');
    }
}
