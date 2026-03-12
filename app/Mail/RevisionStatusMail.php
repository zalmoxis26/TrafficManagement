<?php

namespace App\Mail;

use App\Models\Trafico;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RevisionStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $trafico;
    public $status;

    public function __construct(Trafico $trafico, $status)
    {
        $this->trafico = $trafico;
        $this->status = $status;
    }

    public function envelope(): Envelope
    {
        // Definir el asunto según el estatus
        $asunto = match ($this->status) {
            'EN PROCESO' => "REVISION EN PROCESO - FACTURA: {$this->trafico->factura}",
            'EN ESPERA DE CORRECCIONES' => "ESPERA DE CORRECCIONES - FACTURA: {$this->trafico->factura}",
            'LIBERADA' => "FACTURA {$this->trafico->factura} LIBERADA",
            default => "Actualización de Revisión - Factura: {$this->trafico->factura}",
        };

        return new Envelope(
            subject: $asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.revision_status', // Crea esta vista en resources/views/emails/
        );
    }
}