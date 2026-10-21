<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['inventory_movement_id', 'purchase_item_id', 'quantity_allocated', 'cost_resolution_status', 'allocated_cost'])]
class InventoryCostAllocation extends Model
{
    /**
     * @return BelongsTo<InventoryMovement, $this>
     */
    public function inventoryMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class);
    }

    /**
     * @return BelongsTo<PurchaseItem, $this>
     */
    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_allocated' => 'integer',
            'allocated_cost' => 'decimal:2',
        ];
    }
}
