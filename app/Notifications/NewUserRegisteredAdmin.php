<?php
// app/Notifications/NewUserRegisteredAdmin.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;

class NewUserRegisteredAdmin extends Notification
{
    use Queueable;

    public function __construct(
        public string $approveUrl,
        public string $rejectUrl,
        public string $name,
        public string $email
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nueva solicitud de registro — aprobación requerida')
            ->markdown('mail.admin.approve-registration', [
                'approveUrl' => $this->approveUrl,
                'rejectUrl'  => $this->rejectUrl,
                'name'       => $this->name,
                'email'      => $this->email,
                'expires'    => '60 minutos', // texto informativo
            ]);
    }
}

