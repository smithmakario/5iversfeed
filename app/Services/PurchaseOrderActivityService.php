<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderActivity;
use App\Models\User;
use Illuminate\Support\Carbon;

class PurchaseOrderActivityService
{
    public function log(
        PurchaseOrder $purchaseOrder,
        string $type,
        string $description,
        ?User $user = null,
        ?array $metadata = null,
        ?Carbon $occurredAt = null,
    ): PurchaseOrderActivity {
        $activity = $purchaseOrder->activities()->create([
            'user_id' => $user?->id,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        if ($occurredAt) {
            $activity->forceFill([
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ])->saveQuietly();
        }

        return $activity;
    }
}
