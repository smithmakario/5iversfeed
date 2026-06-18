<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku');
            $table->unique(['supplier_id', 'sku']);
            $table->text('description')->nullable();
            $table->decimal('protein_percentage', 5, 2)->nullable();
            $table->decimal('fiber_percentage', 5, 2)->nullable();
            $table->decimal('moisture_percentage', 5, 2)->nullable();
            $table->decimal('fat_percentage', 5, 2)->nullable();
            $table->text('ingredients')->nullable();
            $table->string('unit')->default('bag');
            $table->decimal('unit_weight_kg', 10, 2)->nullable();
            $table->decimal('price_per_unit', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formulations');
    }
};
