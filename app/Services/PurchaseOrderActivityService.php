<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderActivity;
use App\Models\User;

class PurchaseOrderActivityService
{
    public function log(
        PurchaseOrder $purchaseOrder,
        string $type,
        string $description,
        ?User $user = null,
        ?array $metadata = null,
    ): PurchaseOrderActivity {
        return $purchaseOrder->activities()->create([
            'user_id' => $user?->id,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
