<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('payment_option')->default('one_off')->after('total');
            $table->foreignId('credit_repayment_timeline_id')
                ->nullable()
                ->after('payment_option')
                ->constrained('credit_repayment_timelines')
                ->nullOnDelete();
            $table->decimal('upfront_amount', 14, 2)->default(0)->after('credit_repayment_timeline_id');
            $table->decimal('amount_paid', 14, 2)->default(0)->after('upfront_amount');
            $table->string('payment_status')->default('unpaid')->after('amount_paid');
            $table->date('payment_due_date')->nullable()->after('payment_status');
            $table->timestamp('delivered_at')->nullable()->after('received_at');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('credit_repayment_timeline_id');
            $table->dropColumn([
                'payment_option',
                'upfront_amount',
                'amount_paid',
                'payment_status',
                'payment_due_date',
                'delivered_at',
            ]);
        });
    }
};
