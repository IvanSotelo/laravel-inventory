<?php

namespace IvanSotelo\Inventory\Traits;

use Illuminate\Database\Eloquent\Model;
use IvanSotelo\Inventory\Models\InventoryStock;

trait HasInventory
{
    /**
     * The hasMany stocks relationship.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function stocks()
    {
        return $this->morphMany(InventoryStock::class, 'inventoriable')->latest('id');
    }

    /**
     * Returns the total sum of the current item stock.
     *
     * @return int|float
     */
    public function getTotalStock()
    {
        return $this->stocks->sum('quantity');
    }

    /**
     * Returns true/false if the inventory has stock.
     *
     * @return bool
     */
    public function isInStock()
    {
        return $this->getTotalStock() > 0;
    }

    /**
     * Creates a stock record to the current inventory item.
     *
     * @param  int|float|string  $quantity
     * @param  string  $reason
     * @param  int|float|string  $cost
     * @param  string  $aisle
     * @param  string  $row
     * @param  string  $bin
     * @return Model
     */
    public function createStockOnLocation($quantity, Model $location, $reason = '', $cost = 0, $serial = null, $aisle = null, $row = null, $bin = null)
    {
        /*
        * We'll perform a create so a 'first' movement is generated
        */
        $stock = $this->stocks()->create([
            'inventory_id' => $this->getKey(),
            'location_id' => $location->getKey(),
            'quantity' => 0,
            'aisle' => $aisle,
            'row' => $row,
            'bin' => $bin,
        ]);

        if ($quantity > 0) {
            return $stock->put($quantity, $reason, $cost, null, null, $serial);
        }

        return false;
    }

    /**
     * Takes the specified amount ($quantity) of stock from specified stock location.
     *
     * @param  int|float|string  $quantity
     * @param  string  $reason
     * @return array
     */
    public function takeFromLocation($quantity, Model $location, $reason = '')
    {
        $stock = $this->getStockFromLocation($location);

        if ($stock && $stock->take($quantity, $reason)) {
            return $this;
        }

        return false;
    }

    /**
     * Puts the specified amount ($quantity) of stock into the specified stock location.
     *
     * @param  int|float|string  $quantity
     * @param  string  $reason
     * @param  int|float|string  $cost
     * @return array
     */
    public function putToLocation($quantity, Model $location, $reason = '', $cost = 0)
    {
        $stock = $this->getStockFromLocation($location);

        if ($stock && $stock->put($quantity, $reason, $cost)) {
            return $this;
        }

        return false;
    }

    /**
     * Moves a stock from one location to another.
     *
     *
     * @return mixed
     */
    public function moveStock(Model $fromLocation, Model $toLocation)
    {
        $stock = $this->getStockFromLocation($fromLocation);

        return $stock->moveTo($toLocation);
    }

    /**
     * Retrieves an inventory stock from a given location.
     *
     *
     * @return mixed
     */
    public function getStockFromLocation(Model $location)
    {
        return $this->stocks()->firstOrCreate([
            'location_id' => $location->getKey(),
        ]);
    }
}
