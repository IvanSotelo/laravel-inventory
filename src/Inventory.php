<?php

namespace IvanSotelo\Inventory;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{

    /**
     * The inventories table.
     *
     * @var string
     */
    protected $table = 'inventories';

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
        return $this->hasMany(InventoryMovement::class, 'inventory_id', 'id');
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

    public function __toString()
    {
        return $this->name;
    }
}
