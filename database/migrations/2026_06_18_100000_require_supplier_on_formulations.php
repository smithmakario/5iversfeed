<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('formulations')->whereNull('supplier_id')->delete();

        if ($this->foreignKeyExists('formulations_supplier_id_foreign')) {
            Schema::table('formulations', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
            });
        }

        if ($this->indexExists('formulations_sku_unique')) {
            Schema::table('formulations', function (Blueprint $table) {
                $table->dropUnique(['sku']);
            });
        }

        if (! $this->indexExists('formulations_supplier_id_sku_unique')) {
            Schema::table('formulations', function (Blueprint $table) {
                $table->unique(['supplier_id', 'sku']);
            });
        }

        Schema::table('formulations', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable(false)->change();
        });

        if (! $this->foreignKeyExists('formulations_supplier_id_foreign')) {
            Schema::table('formulations', function (Blueprint $table) {
                $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if ($this->foreignKeyExists('formulations_supplier_id_foreign')) {
            Schema::table('formulations', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
            });
        }

        if ($this->indexExists('formulations_supplier_id_sku_unique')) {
            Schema::table('formulations', function (Blueprint $table) {
                $table->dropUnique(['supplier_id', 'sku']);
            });
        }

        if (! $this->indexExists('formulations_sku_unique')) {
            Schema::table('formulations', function (Blueprint $table) {
                $table->unique('sku');
            });
        }

        Schema::table('formulations', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->change();
        });

        Schema::table('formulations', function (Blueprint $table) {
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
        });
    }

    private function indexExists(string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('formulations')"))
                ->contains(fn ($index) => $index->name === $indexName);
        }

        return collect(DB::select('SHOW INDEX FROM formulations'))
            ->contains(fn ($index) => $index->Key_name === $indexName);
    }

    private function foreignKeyExists(string $constraintName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select('PRAGMA foreign_key_list(formulations)'))
                ->contains(fn ($foreignKey) => $foreignKey->from === 'supplier_id');
        }

        $database = Schema::getConnection()->getDatabaseName();

        $result = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$database, 'formulations', $constraintName, 'FOREIGN KEY']
        );

        return $result !== [];
    }
};
