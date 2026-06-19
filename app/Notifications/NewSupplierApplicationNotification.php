<?php

namespace App\Notifications;

use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSupplierApplicationNotification extends Notification implements ShouldQueue
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
            ->subject("New supplier application: {$this->supplier->company_name}")
            ->markdown('mail.suppliers.new-application', [
                'supplier' => $this->supplier,
                'actionUrl' => route('admin.suppliers.show', $this->supplier),
            ]);
    }
}
