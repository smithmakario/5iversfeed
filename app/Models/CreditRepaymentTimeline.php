<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditRepaymentTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'days',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function displayLabel(): string
    {
        return "{$this->label} ({$this->days} days)";
    }
}
