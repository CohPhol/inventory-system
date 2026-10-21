<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['purchase_id', 'product_id', 'line_number', 'quantity_tubes', 'cost_resolution_status', 'unit_cost_per_tube'])]
class PurchaseItem extends Model
{
    /**
     * @return BelongsTo<Purchase, $this>
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
            'line_number' => 'integer',
            'quantity_tubes' => 'integer',
            'unit_cost_per_tube' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }
}
