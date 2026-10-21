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
        Schema::create('inventory_events', function (Blueprint $table) {
            $table->id();
            $table->enum('event_type', [
                'receipt',
                'tube_opening',
                'queue_consumption',
                'sale',
                'supplier_return',
                'customer_return',
                'adjustment',
                'disposal',
            ]);
            $table->timestamp('occurred_at');
            $table->enum('status', ['draft', 'posted', 'void'])->default('draft');
            $table->foreignId('purchase_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['event_type', 'status', 'occurred_at']);
        });

        Schema::create('used_tubes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('opening_event_id')->unique()->constrained('inventory_events')->restrictOnDelete();
            $table->timestamp('opened_at');
            $table->unsignedInteger('original_shuttlecock_count');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'unusable']);
            $table->timestamp('listed_at')->nullable();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE used_tubes ADD CONSTRAINT used_tubes_original_count_positive CHECK (original_shuttlecock_count > 0)');
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER used_tubes_original_count_insert
                BEFORE INSERT ON used_tubes
                FOR EACH ROW WHEN NEW.original_shuttlecock_count <= 0
                BEGIN SELECT RAISE(ABORT, 'original_shuttlecock_count must be positive'); END
            SQL);
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER used_tubes_original_count_update
                BEFORE UPDATE OF original_shuttlecock_count ON used_tubes
                FOR EACH ROW WHEN NEW.original_shuttlecock_count <= 0
                BEGIN SELECT RAISE(ABORT, 'original_shuttlecock_count must be positive'); END
            SQL);
        }

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_event_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->enum('stock_form', ['new_tube', 'used_shuttlecock']);
            $table->integer('quantity_delta');
            $table->foreignId('used_tube_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'stock_form', 'id']);
            $table->index(['inventory_event_id', 'product_id']);
        });

        Schema::create('inventory_cost_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_movement_id')->constrained()->restrictOnDelete();
            $table->foreignId('purchase_item_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity_allocated');
            $table->enum('cost_resolution_status', ['known', 'unresolved']);
            $table->decimal('allocated_cost', 15, 2)->nullable();
            $table->timestamps();

            $table->unique(['inventory_movement_id', 'purchase_item_id']);
            $table->index(['purchase_item_id', 'inventory_movement_id']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE inventory_movements ADD CONSTRAINT inventory_movements_valid_stock_form CHECK (quantity_delta <> 0 AND ((stock_form = 'new_tube' AND used_tube_id IS NULL) OR (stock_form = 'used_shuttlecock' AND used_tube_id IS NOT NULL)))");
            DB::statement("ALTER TABLE inventory_cost_allocations ADD CONSTRAINT inventory_cost_allocations_valid_values CHECK (quantity_allocated > 0 AND ((cost_resolution_status = 'known' AND allocated_cost IS NOT NULL AND allocated_cost >= 0) OR (cost_resolution_status = 'unresolved' AND allocated_cost IS NULL)))");
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER inventory_movements_valid_values_insert
                BEFORE INSERT ON inventory_movements
                FOR EACH ROW WHEN NEW.quantity_delta = 0
                    OR (NEW.stock_form = 'new_tube' AND NEW.used_tube_id IS NOT NULL)
                    OR (NEW.stock_form = 'used_shuttlecock' AND NEW.used_tube_id IS NULL)
                BEGIN SELECT RAISE(ABORT, 'inventory movement stock form is invalid'); END
            SQL);
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER inventory_movements_valid_values_update
                BEFORE UPDATE OF stock_form, quantity_delta, used_tube_id ON inventory_movements
                FOR EACH ROW WHEN NEW.quantity_delta = 0
                    OR (NEW.stock_form = 'new_tube' AND NEW.used_tube_id IS NOT NULL)
                    OR (NEW.stock_form = 'used_shuttlecock' AND NEW.used_tube_id IS NULL)
                BEGIN SELECT RAISE(ABORT, 'inventory movement stock form is invalid'); END
            SQL);
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER inventory_cost_allocations_valid_values_insert
                BEFORE INSERT ON inventory_cost_allocations
                FOR EACH ROW WHEN NEW.quantity_allocated <= 0
                    OR (NEW.cost_resolution_status = 'known' AND (NEW.allocated_cost IS NULL OR NEW.allocated_cost < 0))
                    OR (NEW.cost_resolution_status = 'unresolved' AND NEW.allocated_cost IS NOT NULL)
                BEGIN SELECT RAISE(ABORT, 'inventory cost allocation is invalid'); END
            SQL);
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER inventory_cost_allocations_valid_values_update
                BEFORE UPDATE OF quantity_allocated, cost_resolution_status, allocated_cost ON inventory_cost_allocations
                FOR EACH ROW WHEN NEW.quantity_allocated <= 0
                    OR (NEW.cost_resolution_status = 'known' AND (NEW.allocated_cost IS NULL OR NEW.allocated_cost < 0))
                    OR (NEW.cost_resolution_status = 'unresolved' AND NEW.allocated_cost IS NOT NULL)
                BEGIN SELECT RAISE(ABORT, 'inventory cost allocation is invalid'); END
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS used_tubes_original_count_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS used_tubes_original_count_update');
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_cost_allocations_valid_values_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_cost_allocations_valid_values_update');
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_movements_valid_values_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS inventory_movements_valid_values_update');
        }

        Schema::dropIfExists('inventory_cost_allocations');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('used_tubes');
        Schema::dropIfExists('inventory_events');
    }
};
