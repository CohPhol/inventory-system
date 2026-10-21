<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['inventory_event_id', 'product_id', 'stock_form', 'quantity_delta', 'used_tube_id'])]
class InventoryMovement extends Model
{
    /**
     * @return BelongsTo<InventoryEvent, $this>
     */
    public function inventoryEvent(): BelongsTo
    {
        return $this->belongsTo(InventoryEvent::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<UsedTube, $this>
     */
    public function usedTube(): BelongsTo
    {
        return $this->belongsTo(UsedTube::class);
    }

    /**
     * @return HasMany<InventoryCostAllocation, $this>
     */
    public function costAllocations(): HasMany
    {
        return $this->hasMany(InventoryCostAllocation::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_delta' => 'integer',
        ];
    }
}
