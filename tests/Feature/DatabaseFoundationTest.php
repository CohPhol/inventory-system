<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Buyer;
use App\Models\InventoryEvent;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\UsedTube;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_and_product_relationships_are_persisted(): void
    {
        $brand = Brand::create(['name' => 'Feather Works']);
        $product = Product::create([
            'brand_id' => $brand->id,
            'name' => 'Flight Pro',
            'model' => 'FP-77',
            'sku' => 'FW-FP77',
            'speed' => 77,
            'shuttlecocks_per_tube' => 12,
        ]);

        $this->assertSame($brand->id, $product->brand->id);
        $this->assertSame($product->id, $brand->products()->first()->id);
        $this->assertSame(77, $product->speed);
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_database_rejects_a_product_with_an_unsupported_speed(): void
    {
        $this->expectException(QueryException::class);

        Product::create([
            'name' => 'Invalid Speed',
            'speed' => 80,
            'shuttlecocks_per_tube' => 12,
        ]);
    }

    public function test_database_rejects_a_product_with_a_nonpositive_tube_count(): void
    {
        $this->expectException(QueryException::class);

        Product::create([
            'name' => 'Invalid Count',
            'speed' => 77,
            'shuttlecocks_per_tube' => 0,
        ]);
    }

    public function test_brand_names_are_unique(): void
    {
        Brand::create(['name' => 'Unique Brand']);

        $this->expectException(QueryException::class);

        Brand::create(['name' => 'Unique Brand']);
    }

    public function test_non_null_product_skus_are_unique_while_null_skus_are_allowed(): void
    {
        Product::create([
            'name' => 'No SKU One',
            'speed' => 75,
            'shuttlecocks_per_tube' => 12,
        ]);
        Product::create([
            'name' => 'No SKU Two',
            'speed' => 76,
            'shuttlecocks_per_tube' => 12,
        ]);
        Product::create([
            'name' => 'With SKU',
            'sku' => 'UNIQUE-SKU',
            'speed' => 77,
            'shuttlecocks_per_tube' => 12,
        ]);

        $this->expectException(QueryException::class);

        Product::create([
            'name' => 'Duplicate SKU',
            'sku' => 'UNIQUE-SKU',
            'speed' => 78,
            'shuttlecocks_per_tube' => 12,
        ]);
    }

    public function test_suppliers_and_buyers_are_independent_records(): void
    {
        $supplier = Supplier::create(['name' => 'Same Contact']);
        $buyer = Buyer::create(['name' => 'Same Contact']);

        $this->assertModelExists($supplier);
        $this->assertModelExists($buyer);
        $this->assertSame('Same Contact', $supplier->name);
        $this->assertSame('Same Contact', $buyer->name);
    }

    public function test_normal_purchases_and_opening_inventory_keep_distinct_types_and_cost_layers(): void
    {
        $supplier = Supplier::create(['name' => 'Court Supply']);
        $product = $this->createProduct();
        $purchase = Purchase::create([
            'reference' => 'PUR-2026-001',
            'entry_type' => 'purchase',
            'supplier_id' => $supplier->id,
            'document_date' => '2026-10-20',
            'received_at' => '2026-10-20 10:00:00',
        ]);
        $opening = Purchase::create([
            'reference' => 'OPEN-2024-001',
            'entry_type' => 'opening_inventory',
            'document_date' => '2024-01-01',
            'received_at' => '2024-01-01 00:00:00',
        ]);
        $purchaseItem = $purchase->items()->create([
            'product_id' => $product->id,
            'line_number' => 1,
            'quantity_tubes' => 2,
            'cost_resolution_status' => 'known',
            'unit_cost_per_tube' => '250.00',
        ]);
        $openingItem = $opening->items()->create([
            'product_id' => $product->id,
            'line_number' => 1,
            'quantity_tubes' => 3,
            'cost_resolution_status' => 'unresolved',
        ]);

        $this->assertSame('purchase', $purchase->entry_type);
        $this->assertSame('opening_inventory', $opening->entry_type);
        $this->assertSame('500.00', $purchaseItem->fresh()->line_total);
        $this->assertSame('known', $purchaseItem->cost_resolution_status);
        $this->assertSame('unresolved', $openingItem->cost_resolution_status);
        $this->assertNull($openingItem->fresh()->line_total);
    }

    public function test_same_product_can_have_distinct_fifo_layers_at_different_costs(): void
    {
        $product = $this->createProduct();
        $purchase = Purchase::create([
            'reference' => 'PUR-LAYERS-001',
            'entry_type' => 'purchase',
            'document_date' => '2026-10-20',
        ]);
        $firstLayer = $purchase->items()->create([
            'product_id' => $product->id,
            'line_number' => 1,
            'quantity_tubes' => 2,
            'cost_resolution_status' => 'known',
            'unit_cost_per_tube' => '240.00',
        ]);
        $secondLayer = $purchase->items()->create([
            'product_id' => $product->id,
            'line_number' => 2,
            'quantity_tubes' => 1,
            'cost_resolution_status' => 'known',
            'unit_cost_per_tube' => '275.00',
        ]);

        $this->assertSame('240.00', $firstLayer->fresh()->unit_cost_per_tube);
        $this->assertSame('275.00', $secondLayer->fresh()->unit_cost_per_tube);
        $this->assertCount(2, $purchase->items);
    }

    public function test_purchase_history_cannot_be_deleted_while_receipt_layers_reference_it(): void
    {
        $product = $this->createProduct();
        $purchase = Purchase::create([
            'reference' => 'PUR-POSTED-001',
            'entry_type' => 'opening_inventory',
            'document_date' => '2025-01-01',
            'status' => 'posted',
        ]);
        $purchase->items()->create([
            'product_id' => $product->id,
            'line_number' => 1,
            'quantity_tubes' => 1,
            'cost_resolution_status' => 'known',
            'unit_cost_per_tube' => '200.00',
        ]);

        $this->expectException(QueryException::class);

        $purchase->delete();
    }

    public function test_inventory_events_movements_and_cost_allocations_keep_fifo_traceability(): void
    {
        $product = $this->createProduct();
        $purchase = Purchase::create([
            'reference' => 'PUR-TRACE-001',
            'entry_type' => 'purchase',
            'document_date' => '2026-10-20',
        ]);
        $receiptLayer = $purchase->items()->create([
            'product_id' => $product->id,
            'line_number' => 1,
            'quantity_tubes' => 2,
            'cost_resolution_status' => 'known',
            'unit_cost_per_tube' => '250.00',
        ]);
        $event = InventoryEvent::create([
            'event_type' => 'sale',
            'occurred_at' => '2026-10-21 12:00:00',
            'status' => 'posted',
        ]);
        $movement = $event->movements()->create([
            'product_id' => $product->id,
            'stock_form' => 'new_tube',
            'quantity_delta' => -1,
        ]);
        $allocation = $movement->costAllocations()->create([
            'purchase_item_id' => $receiptLayer->id,
            'quantity_allocated' => 1,
            'cost_resolution_status' => 'known',
            'allocated_cost' => '250.00',
        ]);

        $this->assertSame($event->id, $movement->inventoryEvent->id);
        $this->assertSame($product->id, $movement->product->id);
        $this->assertSame($receiptLayer->id, $allocation->purchaseItem->id);
        $this->assertSame('250.00', $allocation->allocated_cost);
        $this->assertModelExists($allocation);
    }

    public function test_used_tube_movements_require_an_individual_used_tube_reference(): void
    {
        $product = $this->createProduct();
        $event = InventoryEvent::create([
            'event_type' => 'tube_opening',
            'occurred_at' => '2026-10-21 12:00:00',
        ]);
        $usedTube = UsedTube::create([
            'product_id' => $product->id,
            'opening_event_id' => $event->id,
            'opened_at' => '2026-10-21 12:00:00',
            'original_shuttlecock_count' => 12,
            'condition' => 'good',
        ]);
        $movement = InventoryMovement::create([
            'inventory_event_id' => $event->id,
            'product_id' => $product->id,
            'stock_form' => 'used_shuttlecock',
            'quantity_delta' => 12,
            'used_tube_id' => $usedTube->id,
        ]);

        $this->assertSame($usedTube->id, $movement->usedTube->id);
        $this->assertSame($event->id, $usedTube->openingEvent->id);
        $this->assertSame(12, $usedTube->original_shuttlecock_count);
    }

    private function createProduct(): Product
    {
        return Product::create([
            'name' => 'Test Shuttlecock',
            'speed' => 77,
            'shuttlecocks_per_tube' => 12,
        ]);
    }
}
