<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('supplier_notes');
        });

        if (Schema::hasColumn('purchase_orders', 'delivered_at')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->renameColumn('delivered_at', 'dispatched_at');
            });
        }

        DB::table('purchase_orders')
            ->where('status', 'delivered')
            ->update(['status' => 'dispatched']);
    }

    public function down(): void
    {
        DB::table('purchase_orders')
            ->where('status', 'dispatched')
            ->update(['status' => 'delivered']);

        if (Schema::hasColumn('purchase_orders', 'dispatched_at')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->renameColumn('dispatched_at', 'delivered_at');
            });
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });
    }
};
