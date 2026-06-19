<?php

namespace App\Notifications;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupplierApprovedNotification extends Notification implements ShouldQueue
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
            ->subject('Your supplier account has been approved')
            ->markdown('mail.suppliers.approved', [
                'supplier' => $this->supplier,
                'actionUrl' => route('login'),
            ]);
    }
}
