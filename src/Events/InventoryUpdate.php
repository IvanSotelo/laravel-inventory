<?php

namespace IvanSotelo\Inventory\Events;

use Illuminate\Database\Eloquent\Model;
use IvanSotelo\Inventory\Models\InventoryStock;

class InventoryUpdate
{
    /**
     * Old inventory instance before changes have made.
     *
     *  @var \IvanSotelo\Inventory\InventoryStock|null
     */
    public $oldInventory = null;

    /**
     * New inventory instance that has been persisted to the storage.
     *
     * @var \IvanSotelo\Inventory\InventoryStock
     */
    public $newInventory;

    /**
     * The model instance with respect to the inventoriable class.
     *
     *  @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * Create a new InventoryUpdate instance.
     *
     * @param  \IvanSotelo\Inventory\InventoryStock|null  $oldInventory
     * @return void
     */
    public function __construct($oldInventory, InventoryStock $newInventory, Model $model)
    {
        $this->oldInventory = $oldInventory;

        $this->newInventory = $newInventory;

        $this->model = $model;
    }
}
