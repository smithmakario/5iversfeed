<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'feed_type_id',
        'brand_id',
        'supplier_id',
        'name',
        'sku',
        'description',
        'protein_percentage',
        'fiber_percentage',
        'moisture_percentage',
        'fat_percentage',
        'ingredients',
        'unit',
        'unit_weight_kg',
        'price_per_unit',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'protein_percentage' => 'decimal:2',
            'fiber_percentage' => 'decimal:2',
            'moisture_percentage' => 'decimal:2',
            'fat_percentage' => 'decimal:2',
            'unit_weight_kg' => 'decimal:2',
            'price_per_unit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function feedType(): BelongsTo
    {
        return $this->belongsTo(FeedType::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
