<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PurchaseOrder $purchaseOrder,
        public string $reminderType,
        public string $recipientRole,
        public float $amountDue,
        public ?int $daysToDue = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $purchaseOrder = $this->purchaseOrder;
        $purchaseOrder->loadMissing(['supplier', 'creator']);

        $url = $this->recipientRole === 'supplier'
            ? route('supplier.purchase-orders.show', $purchaseOrder)
            : route('admin.purchase-orders.show', $purchaseOrder);

        return (new MailMessage)
            ->subject($this->subject())
            ->markdown('mail.purchase-orders.payment-due', [
                'purchaseOrder' => $purchaseOrder,
                'reminderType' => $this->reminderType,
                'recipientRole' => $this->recipientRole,
                'amountDue' => $this->amountDue,
                'daysToDue' => $this->daysToDue,
                'actionUrl' => $url,
            ]);
    }

    private function subject(): string
    {
        $poNumber = $this->purchaseOrder->po_number;

        return match ($this->reminderType) {
            'due_soon' => "Payment due soon for purchase order {$poNumber}",
            'due_today' => "Payment due today for purchase order {$poNumber}",
            'overdue' => "Payment overdue for purchase order {$poNumber}",
            default => "Payment reminder for purchase order {$poNumber}",
        };
    }
}
