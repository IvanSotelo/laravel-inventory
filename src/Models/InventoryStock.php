<?php

namespace IvanSotelo\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use IvanSotelo\Inventory\Traits\InventoryStockTrait;

class InventoryStock extends Model
{
    use InventoryStockTrait;

    /**
     * The inventories table.
     *
     * @var string
     */
    protected $table = 'inventory_stocks';

    protected $fillable = ['quantity', 'inventoriable_type', 'inventoriable_id', 'location_id'];

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
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'stock_id', 'id');
    }

    /**
     * The belongsTo location relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class)->with('warehouse');
    }

    /**
     * The belongsTo metric relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function metric()
    {
        return $this->belongsTo(Metric::class);
    }
}
