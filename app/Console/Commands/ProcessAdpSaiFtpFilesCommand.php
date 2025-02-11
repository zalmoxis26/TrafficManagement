<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessAdpSaiFtpFiles;

class ProcessAdpSaiFtpFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch:adp-sai-process-ftp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa y carga archivos de ADP SAI desde FTP a S3';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    { 
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Despachar el Job
        ProcessAdpSaiFtpFiles::dispatch();

        $this->info('El Job ProcessAdpSaiFtpFiles ha sido despachado exitosamente.');

        return 0;
    }
}
