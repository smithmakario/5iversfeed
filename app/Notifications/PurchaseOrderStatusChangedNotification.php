<?php

namespace App\Notifications;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PurchaseOrder $purchaseOrder,
        public PurchaseOrderStatus $previousStatus,
        public PurchaseOrderStatus $newStatus,
        public string $recipientRole,
        public ?string $changedByName = null,
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
            ->markdown('mail.purchase-orders.status-changed', [
                'purchaseOrder' => $purchaseOrder,
                'previousStatus' => $this->previousStatus,
                'newStatus' => $this->newStatus,
                'recipientRole' => $this->recipientRole,
                'changedByName' => $this->changedByName,
                'actionUrl' => $url,
            ]);
    }

    private function subject(): string
    {
        return match ($this->newStatus) {
            PurchaseOrderStatus::Submitted => "New purchase order {$this->purchaseOrder->po_number} submitted",
            PurchaseOrderStatus::Confirmed => "Purchase order {$this->purchaseOrder->po_number} confirmed by supplier",
            PurchaseOrderStatus::Received => "Purchase order {$this->purchaseOrder->po_number} marked as received",
            PurchaseOrderStatus::Cancelled => "Purchase order {$this->purchaseOrder->po_number} cancelled",
            default => "Purchase order {$this->purchaseOrder->po_number} status updated",
        };
    }
}
