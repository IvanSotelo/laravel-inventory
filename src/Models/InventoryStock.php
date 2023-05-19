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
    public function model()
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
     * The hasOne location relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {
        return $this->hasOne(Location::class);
    }
}
