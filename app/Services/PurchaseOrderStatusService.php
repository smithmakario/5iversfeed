<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\PurchaseOrderStatusChangedNotification;
use App\Notifications\PurchaseOrderUpdatedNotification;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class PurchaseOrderStatusService
{
    public function __construct(
        private PurchaseOrderActivityService $activityService,
    ) {}

    public function transition(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderStatus $newStatus,
        ?User $actor = null,
        string $actorRole = 'admin',
    ): void {
        $previousStatus = $purchaseOrder->status;

        if ($previousStatus === $newStatus) {
            return;
        }

        if (! $previousStatus->canTransitionTo($newStatus, $actorRole)) {
            throw new InvalidArgumentException(
                "Cannot transition from {$previousStatus->label()} to {$newStatus->label()} as {$actorRole}."
            );
        }

        $purchaseOrder->update([
            'status' => $newStatus,
            'received_at' => $newStatus === PurchaseOrderStatus::Received
                ? ($purchaseOrder->received_at ?? now())
                : $purchaseOrder->received_at,
            'dispatched_at' => $newStatus === PurchaseOrderStatus::Dispatched
                ? ($purchaseOrder->dispatched_at ?? now())
                : $purchaseOrder->dispatched_at,
            'rejection_reason' => $newStatus === PurchaseOrderStatus::Submitted && $previousStatus === PurchaseOrderStatus::Rejected
                ? null
                : $purchaseOrder->rejection_reason,
        ]);

        if ($newStatus === PurchaseOrderStatus::Dispatched) {
            app(PurchaseOrderPaymentService::class)->syncPaymentFields($purchaseOrder->fresh());
            $purchaseOrder->refresh();
        }

        $purchaseOrder->load(['supplier.user', 'creator']);

        $this->logStatusChange($purchaseOrder, $previousStatus, $newStatus, $actor, $actorRole);
        $this->notifyRecipients($purchaseOrder, $previousStatus, $newStatus, $actor);
    }

    public function notifyForNewOrder(PurchaseOrder $purchaseOrder, ?User $actor = null): void
    {
        $purchaseOrder->load(['supplier.user', 'creator']);

        if ($purchaseOrder->status === PurchaseOrderStatus::Draft) {
            $this->activityService->log(
                $purchaseOrder,
                'order_created',
                'Purchase order saved as draft.',
                $actor,
            );

            return;
        }

        $this->activityService->log(
            $purchaseOrder,
            'order_issued',
            'Purchase order issued to supplier.',
            $actor,
        );

        $this->notifyRecipients(
            $purchaseOrder,
            PurchaseOrderStatus::Draft,
            $purchaseOrder->status,
            $actor,
        );
    }

    public function notifySupplierOfUpdate(PurchaseOrder $purchaseOrder, ?User $actor = null): void
    {
        if (! $purchaseOrder->status->isEditableByAdmin() || $purchaseOrder->status === PurchaseOrderStatus::Draft) {
            return;
        }

        $purchaseOrder->load(['supplier.user']);

        $notification = new PurchaseOrderUpdatedNotification(
            purchaseOrder: $purchaseOrder,
            updatedByName: $actor?->name,
        );

        $supplier = $purchaseOrder->supplier;

        if ($supplier->user) {
            $supplier->user->notify($notification);

            return;
        }

        if (filled($supplier->email)) {
            Notification::route('mail', $supplier->email)->notify($notification);
        }
    }

    private function logStatusChange(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderStatus $previousStatus,
        PurchaseOrderStatus $newStatus,
        ?User $actor,
        string $actorRole,
    ): void {
        $metadata = [
            'from' => $previousStatus->value,
            'to' => $newStatus->value,
            'actor_role' => $actorRole,
        ];

        $description = match ($newStatus) {
            PurchaseOrderStatus::Submitted => 'Purchase order issued to supplier.',
            PurchaseOrderStatus::Confirmed => 'Supplier accepted the purchase order.',
            PurchaseOrderStatus::Dispatched => 'Supplier dispatched the order. Credit period started if applicable.',
            PurchaseOrderStatus::Received => 'Admin acknowledged receipt of goods.',
            PurchaseOrderStatus::Rejected => 'Supplier rejected the purchase order.',
            PurchaseOrderStatus::Cancelled => 'Purchase order was cancelled.',
            default => "Status changed from {$previousStatus->label()} to {$newStatus->label()}.",
        };

        if ($newStatus === PurchaseOrderStatus::Rejected && $purchaseOrder->rejection_reason) {
            $metadata['rejection_reason'] = $purchaseOrder->rejection_reason;
        }

        if ($newStatus === PurchaseOrderStatus::Dispatched && $purchaseOrder->payment_due_date) {
            $metadata['payment_due_date'] = $purchaseOrder->payment_due_date->toDateString();
        }

        $this->activityService->log(
            $purchaseOrder,
            'status_changed',
            $description,
            $actor,
            $metadata,
        );
    }

    private function notifyRecipients(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderStatus $previousStatus,
        PurchaseOrderStatus $newStatus,
        ?User $actor,
    ): void {
        if ($newStatus === PurchaseOrderStatus::Draft) {
            return;
        }

        $changedByName = $actor?->name;

        match ($newStatus) {
            PurchaseOrderStatus::Submitted,
            PurchaseOrderStatus::Received,
            PurchaseOrderStatus::Cancelled => $this->notifySupplier(
                $purchaseOrder,
                $previousStatus,
                $newStatus,
                $changedByName,
            ),
            PurchaseOrderStatus::Confirmed,
            PurchaseOrderStatus::Dispatched,
            PurchaseOrderStatus::Rejected => $this->notifyCreator(
                $purchaseOrder,
                $previousStatus,
                $newStatus,
                $changedByName,
            ),
            default => null,
        };
    }

    private function notifySupplier(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderStatus $previousStatus,
        PurchaseOrderStatus $newStatus,
        ?string $changedByName,
    ): void {
        $supplier = $purchaseOrder->supplier;
        $notification = new PurchaseOrderStatusChangedNotification(
            purchaseOrder: $purchaseOrder,
            previousStatus: $previousStatus,
            newStatus: $newStatus,
            recipientRole: 'supplier',
            changedByName: $changedByName,
        );

        if ($supplier->user) {
            $supplier->user->notify($notification);

            return;
        }

        if (filled($supplier->email)) {
            Notification::route('mail', $supplier->email)->notify($notification);
        }
    }

    private function notifyCreator(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderStatus $previousStatus,
        PurchaseOrderStatus $newStatus,
        ?string $changedByName,
    ): void {
        $creator = $purchaseOrder->creator;

        if (! $creator) {
            return;
        }

        $creator->notify(new PurchaseOrderStatusChangedNotification(
            purchaseOrder: $purchaseOrder,
            previousStatus: $previousStatus,
            newStatus: $newStatus,
            recipientRole: 'admin',
            changedByName: $changedByName,
        ));
    }
}
