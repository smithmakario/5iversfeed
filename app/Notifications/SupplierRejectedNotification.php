<?php

namespace App\Notifications;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupplierRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Supplier $supplier,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update on your supplier application')
            ->markdown('mail.suppliers.rejected', [
                'supplier' => $this->supplier,
            ]);
    }
}
