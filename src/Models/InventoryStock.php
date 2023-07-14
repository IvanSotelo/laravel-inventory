<?php

namespace IvanSotelo\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use IvanSotelo\Inventory\Traits\InventoryStockTrait;

/**
 * IvanSotelo\Inventory\Models\InventoryStock
 *
 * @property Location $location
 */
class InventoryStock extends Model
{
    use InventoryStockTrait;

    /**
     * The inventories table.
     *
     * @var string
     */
    protected $table = 'inventory_stocks';

    protected $fillable = ['quantity', 'inventoriable_type', 'inventoriable_id', 'location_id', 'metric_id'];

    /**
     * Relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function inventoriable()
    {
        return $this->morphTo();
    }

    /**
     * The hasMany movements relationship.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'stock_id', 'id');
    }

    /**
     * The belongsTo location relationship.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class)->with('warehouse');
    }

    /**
     * The belongsTo metric relationship.
     */
    public function metric(): BelongsTo
    {
        return $this->belongsTo(Metric::class);
    }
}
