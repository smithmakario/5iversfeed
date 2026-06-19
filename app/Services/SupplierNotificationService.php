<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\NewSupplierApplicationNotification;
use App\Notifications\SupplierApplicationSubmittedNotification;
use App\Notifications\SupplierApprovedNotification;
use App\Notifications\SupplierRejectedNotification;
use Illuminate\Support\Facades\Notification;

class SupplierNotificationService
{
    public function notifyApplicationSubmitted(Supplier $supplier, User $user): void
    {
        $user->notify(new SupplierApplicationSubmittedNotification($supplier));

        $admins = User::query()
            ->where('role', UserRole::Admin)
            ->get();

        Notification::send($admins, new NewSupplierApplicationNotification($supplier));
    }

    public function notifyApproved(Supplier $supplier): void
    {
        $supplier->loadMissing('user');

        $notification = new SupplierApprovedNotification($supplier);

        if ($supplier->user) {
            $supplier->user->notify($notification);

            return;
        }

        if (filled($supplier->email)) {
            Notification::route('mail', $supplier->email)->notify($notification);
        }
    }

    public function notifyRejected(Supplier $supplier): void
    {
        $supplier->loadMissing('user');

        $notification = new SupplierRejectedNotification($supplier);

        if ($supplier->user) {
            $supplier->user->notify($notification);

            return;
        }

        if (filled($supplier->email)) {
            Notification::route('mail', $supplier->email)->notify($notification);
        }
    }
}
