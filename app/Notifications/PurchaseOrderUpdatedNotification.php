<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PurchaseOrder $purchaseOrder,
        public ?string $updatedByName = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $purchaseOrder = $this->purchaseOrder;
        $purchaseOrder->loadMissing(['supplier', 'creator']);

        return (new MailMessage)
            ->subject("Purchase order {$purchaseOrder->po_number} has been updated")
            ->markdown('mail.purchase-orders.updated', [
                'purchaseOrder' => $purchaseOrder,
                'updatedByName' => $this->updatedByName,
                'actionUrl' => route('supplier.purchase-orders.show', $purchaseOrder),
            ]);
    }
}
