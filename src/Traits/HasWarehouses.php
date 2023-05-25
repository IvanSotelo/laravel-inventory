<?php

namespace IvanSotelo\Inventory\Traits;

use IvanSotelo\Inventory\Models\Warehouse;

trait HasWarehouses
{
    /**
     * The hasMany warehouses relationship.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function warehouses()
    {
        return $this->morphMany(Warehouse::class, 'warehouseable')->latest('id');
    }
}
