<?php

namespace Database\Seeders;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\FeedType;
use App\Models\Formulation;
use App\Models\CreditRepaymentTimeline;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@5iversfeed.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $supplierUser = User::query()->create([
            'name' => 'Agro Feeds Ltd',
            'email' => 'supplier@5iversfeed.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Supplier,
            'email_verified_at' => now(),
        ]);

        $supplier = Supplier::query()->create([
            'user_id' => $supplierUser->id,
            'company_name' => 'Agro Feeds Ltd',
            'contact_name' => 'John Supplier',
            'email' => 'supplier@5iversfeed.test',
            'phone' => '+2348012345678',
            'address' => '12 Industrial Estate',
            'city' => 'Lagos',
            'state' => 'Lagos',
            'country' => 'Nigeria',
            'tax_id' => 'TAX-001',
            'registration_number' => 'RC-123456',
            'status' => SupplierStatus::Approved,
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        $poultry = FeedType::query()->create([
            'name' => 'Poultry Feed',
            'slug' => 'poultry-feed',
            'description' => 'Feeds formulated for broilers, layers, and chicks.',
            'sort_order' => 1,
        ]);

        $cattle = FeedType::query()->create([
            'name' => 'Cattle Feed',
            'slug' => 'cattle-feed',
            'description' => 'Nutrition for dairy and beef cattle.',
            'sort_order' => 2,
        ]);

        $brandA = Brand::query()->create(['name' => '5ivers Premium', 'slug' => '5ivers-premium', 'sort_order' => 1]);
        $brandB = Brand::query()->create(['name' => 'FarmGold', 'slug' => 'farmgold', 'sort_order' => 2]);

        $starter = Formulation::query()->create([
            'feed_type_id' => $poultry->id,
            'brand_id' => $brandA->id,
            'supplier_id' => $supplier->id,
            'name' => 'Broiler Starter Mash',
            'sku' => 'FF-BRO-START-25',
            'description' => 'High-protein starter feed for broiler chicks (0–3 weeks).',
            'protein_percentage' => 23.00,
            'fiber_percentage' => 4.50,
            'moisture_percentage' => 12.00,
            'fat_percentage' => 5.00,
            'ingredients' => "Maize, Soybean meal, Fish meal, Limestone, Premix",
            'unit' => 'bag',
            'unit_weight_kg' => 25,
            'price_per_unit' => 18500,
        ]);

        $layer = Formulation::query()->create([
            'feed_type_id' => $poultry->id,
            'brand_id' => $brandB->id,
            'supplier_id' => $supplier->id,
            'name' => 'Layer Mash',
            'sku' => 'FF-LAY-MASH-25',
            'protein_percentage' => 17.00,
            'fiber_percentage' => 6.00,
            'moisture_percentage' => 12.00,
            'unit' => 'bag',
            'unit_weight_kg' => 25,
            'price_per_unit' => 16200,
        ]);

        $dairy = Formulation::query()->create([
            'feed_type_id' => $cattle->id,
            'brand_id' => $brandA->id,
            'supplier_id' => $supplier->id,
            'name' => 'Dairy Pellet',
            'sku' => 'FF-DAIRY-PEL-50',
            'protein_percentage' => 18.00,
            'unit' => 'bag',
            'unit_weight_kg' => 50,
            'price_per_unit' => 32000,
        ]);

        CreditRepaymentTimeline::query()->insert([
            ['label' => '10 Days', 'days' => 10, 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['label' => '20 Days', 'days' => 20, 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['label' => '30 Days', 'days' => 30, 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $timeline30 = CreditRepaymentTimeline::query()->where('days', 30)->first();

        $order = PurchaseOrder::query()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'status' => PurchaseOrderStatus::Submitted,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addDays(7)->toDateString(),
            'tax_amount' => 0,
            'payment_option' => 'full_credit',
            'credit_repayment_timeline_id' => $timeline30->id,
        ]);

        $qty = 100;
        $order->items()->create([
            'formulation_id' => $starter->id,
            'product_name' => $starter->name,
            'quantity' => $qty,
            'unit_price' => $starter->price_per_unit,
            'subtotal' => $qty * $starter->price_per_unit,
        ]);

        $order->recalculateTotals();
    }
}
