<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Trafico;

class FacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $trafico;

    public function __construct(Trafico $trafico)
    {
        $this->trafico = $trafico;
    }

    public function build()
    {
        return $this->view('emails.factura')
                    ->subject('Nueva Factura Ingresada ' . $this->trafico->factura)
                    ->attach(storage_path('app/public/' . $this->trafico->adjuntoFactura))
                    ->with([
                        'trafico' => $this->trafico,
                    ]);
    }
}