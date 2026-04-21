<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class VerifyRegistrationEmail extends Notification  implements ShouldQueue
{
    use Queueable;

    public string $verifyUrl;
    public string $name;

    public function __construct(string $verifyUrl, string $name)
    {
        $this->verifyUrl = $verifyUrl;
        $this->name = $name;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('🛡️ Verifica tu dirección de correo electrónico')
            ->markdown('emails.verify-registration', [
                'verifyUrl' => $this->verifyUrl,
                'name' => $this->name,
            ]);
    }
}
