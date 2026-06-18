<?php

namespace App\Models;

use App\Enums\PaymentOption;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseOrderStatus;
use App\Services\PurchaseOrderPaymentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'created_by',
        'status',
        'order_date',
        'expected_delivery_date',
        'received_at',
        'dispatched_at',
        'subtotal',
        'tax_amount',
        'total',
        'payment_option',
        'credit_repayment_timeline_id',
        'upfront_amount',
        'amount_paid',
        'payment_status',
        'payment_due_date',
        'notes',
        'supplier_notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseOrderStatus::class,
            'payment_option' => PaymentOption::class,
            'payment_status' => PaymentStatus::class,
            'order_date' => 'date',
            'expected_delivery_date' => 'date',
            'payment_due_date' => 'date',
            'received_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'upfront_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $order): void {
            if (blank($order->po_number)) {
                $order->po_number = static::generatePoNumber();
            }
        });
    }

    public static function generatePoNumber(): string
    {
        return DB::transaction(function () {
            $prefix = 'PO-'.now()->format('Ymd');
            $latest = static::query()
                ->where('po_number', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('po_number')
                ->value('po_number');

            $sequence = $latest
                ? ((int) substr($latest, -4)) + 1
                : 1;

            return sprintf('%s-%04d', $prefix, $sequence);
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creditRepaymentTimeline(): BelongsTo
    {
        return $this->belongsTo(CreditRepaymentTimeline::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(PurchaseOrderActivity::class)->latest();
    }

    public function canSupplierAccept(): bool
    {
        return $this->status->canTransitionTo(PurchaseOrderStatus::Confirmed, 'supplier');
    }

    public function canSupplierDispatch(): bool
    {
        return $this->status->canTransitionTo(PurchaseOrderStatus::Dispatched, 'supplier');
    }

    public function canAdminReceive(): bool
    {
        return $this->status->canTransitionTo(PurchaseOrderStatus::Received, 'admin');
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $this->forceFill([
            'subtotal' => $subtotal,
            'total' => $subtotal + $this->tax_amount,
        ])->save();
    }

    public function syncPaymentStatus(): void
    {
        app(PurchaseOrderPaymentService::class)->syncPaymentFields($this);
    }

    public function creditAmount(): float
    {
        return app(PurchaseOrderPaymentService::class)->creditAmount($this);
    }

    public function amountOutstanding(): float
    {
        return app(PurchaseOrderPaymentService::class)->amountOutstanding($this);
    }
}
