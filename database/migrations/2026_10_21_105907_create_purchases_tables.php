<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->enum('entry_type', ['purchase', 'opening_inventory']);
            $table->foreignId('supplier_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('document_date');
            $table->timestamp('received_at')->nullable();
            $table->enum('status', ['draft', 'posted', 'void'])->default('draft');
            $table->string('supplier_reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'document_date']);
            $table->index(['supplier_id', 'document_date']);
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('line_number');
            $table->unsignedInteger('quantity_tubes');
            $table->enum('cost_resolution_status', ['known', 'unresolved']);
            $table->decimal('unit_cost_per_tube', 13, 2)->nullable();
            $table->decimal('line_total', 15, 2)->nullable()->storedAs('quantity_tubes * unit_cost_per_tube');
            $table->timestamps();

            $table->unique(['purchase_id', 'line_number']);
            $table->index(['product_id', 'id']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE purchase_items ADD CONSTRAINT purchase_items_quantity_positive CHECK (quantity_tubes > 0)');
            DB::statement("ALTER TABLE purchase_items ADD CONSTRAINT purchase_items_cost_resolution_consistent CHECK ((cost_resolution_status = 'known' AND unit_cost_per_tube IS NOT NULL AND unit_cost_per_tube >= 0) OR (cost_resolution_status = 'unresolved' AND unit_cost_per_tube IS NULL))");
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER purchase_items_valid_values_insert
                BEFORE INSERT ON purchase_items
                FOR EACH ROW WHEN NEW.quantity_tubes <= 0
                    OR (NEW.cost_resolution_status = 'known' AND (NEW.unit_cost_per_tube IS NULL OR NEW.unit_cost_per_tube < 0))
                    OR (NEW.cost_resolution_status = 'unresolved' AND NEW.unit_cost_per_tube IS NOT NULL)
                BEGIN SELECT RAISE(ABORT, 'purchase item quantity or cost resolution is invalid'); END
            SQL);
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER purchase_items_valid_values_update
                BEFORE UPDATE OF quantity_tubes, cost_resolution_status, unit_cost_per_tube ON purchase_items
                FOR EACH ROW WHEN NEW.quantity_tubes <= 0
                    OR (NEW.cost_resolution_status = 'known' AND (NEW.unit_cost_per_tube IS NULL OR NEW.unit_cost_per_tube < 0))
                    OR (NEW.cost_resolution_status = 'unresolved' AND NEW.unit_cost_per_tube IS NOT NULL)
                BEGIN SELECT RAISE(ABORT, 'purchase item quantity or cost resolution is invalid'); END
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS purchase_items_valid_values_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS purchase_items_valid_values_update');
        }

        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
