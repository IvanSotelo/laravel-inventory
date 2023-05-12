<?php

namespace IvanSotelo\Inventory;

use Illuminate\Database\Eloquent\Model;
use IvanSotelo\Inventory\Events\InventoryUpdate;
use IvanSotelo\Inventory\Exeptions\InvalidInventory;
use IvanSotelo\Inventory\Models\InventoryStock;
use IvanSotelo\Inventory\Models\Location;

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
        return $this->getTotalStock() > 0 ? true : false;
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
         // A stock record wasn't found on this location, we'll create one.
         $stock = $this->stocks()->getRelated()->newInstance();

         $stock->setAttribute('inventory_id', $this->getKey());
         $stock->setAttribute('location_id', $location->getKey());
         $stock->setAttribute('quantity', 0);
         $stock->setAttribute('aisle', $aisle);
         $stock->setAttribute('row', $row);
         $stock->setAttribute('bin', $bin);

         if ($stock->save() && $quantity > 0) {
             return $stock->put($quantity, $reason, $cost, null, null, $serial);
         }

        return false;
    }

    /**
     * Create or update model inventory.
     *
     * @param  string  $description
     * @return \IvanSotelo\Inventory\InventoryStock
     */
    public function set(int $quantity, string $description = null, Location $location = null)
    {
        if (! $this->isValidQuantity($quantity, $description)) {
            throw InvalidInventory::value($quantity);
        }

        return $this->createInventory($quantity, $description);
    }

    /**
     * Add or create an inventory.
     *
     * @param  string  $description
     * @return \IvanSotelo\Inventory\InventoryStock
     */
    public function add(int $addQuantity = 1, Location $location = null)
    {
        if (! $this->isValidQuantity($addQuantity, $description)) {
            throw InvalidInventory::value($quantity);
        }

        if (! isset($this->stocks->first()->quantity)) {
            return $this->createInventory($addQuantity, $description);
        }

        $newQuantity = $this->stocks->first()->quantity + $addQuantity;

        return $this->createInventory($newQuantity, $description);
    }

    /**
     * Subtract a given amount from the model inventory.
     *
     * @param  string  $description
     * @return \IvanSotelo\Inventory\InventoryStock
     */
    public function take(int $subtractQuantity = 1, string $description = null)
    {
        $subtractQuantity = abs($subtractQuantity);

        if (! $this->isValidQuantity($subtractQuantity, $description)) {
            throw InvalidInventory::value($quantity);
        }

        if ($this->notInInventory()) {
            throw InvalidInventory::subtract($subtractQuantity);
        }

        $newQuantity = $this->stocks->first()->quantity - abs($subtractQuantity);

        if ($newQuantity < 0) {
            throw InvalidInventory::negative($subtractQuantity);
        }

        return $this->createInventory($newQuantity, $description);
    }

    /**
     * Check if given quantity is a valid int and description is a valid string.
     *
     * @param  string  $description
     * @return bool
     */
    public function isValidQuantity(int $quantity, string $description = null)
    {
        if (gmp_sign($quantity) == -1) {
            throw InvalidInventory::value($quantity);
        }

        return true;
    }

    /**
     * Create a new inventory.
     *
     * @param  string  $description
     * @return \IvanSotelo\Inventory\InventoryStock
     */
    public function createInventory(int $quantity, string $description = null)
    {
        $oldInventory = $this->currentInventory();

        $newInventory = $this->stocks()->create([
            'quantity' => abs($quantity),
            'description' => $description,
        ]);

        event(new InventoryUpdate($oldInventory, $newInventory, $this));

        return $newInventory;
    }

    /**
     * Return the current inventory on the model.
     *
     * @return \IvanSotelo\Inventory\InventoryStock
     */
    public function currentInventory()
    {
        $stocks = $this->relationLoaded('stocks') ? $this->stocks : $this->stocks();

        return $stocks->first();
    }

    /**
     * Delete the inventory from the model.
     *
     * @param  int|null  $newStock (optional passing an int to delete all inventory and create new one)
     * @return \IvanSotelo\Inventory\InventoryStock (if new inventory has been created upon receiving new quantity)
     */
    public function clear($newStock = -1)
    {
        $this->stocks()->delete();

        return $newStock >= 0 ? $this->setInventory($newStock) : true;
    }
}
