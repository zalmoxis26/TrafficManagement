<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UnprocessedFoldersReport extends Mailable
{
    use Queueable, SerializesModels;

    public $folders;

    /**
     * Create a new message instance.
     *
     * @param array $folders
     * @return void
     */
    public function __construct($folders)
    {
        $this->folders = $folders;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reporte de Carpetas No Procesadas ADP')
                    ->view('emails.unprocessed_folders_report')
                    ->with([
                        'folders' => $this->folders,
                    ]);
    }
}
