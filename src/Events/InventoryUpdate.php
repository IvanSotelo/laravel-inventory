<?php

namespace IvanSotelo\Inventory\Events;

use IvanSotelo\Inventory\Inventory;
use Illuminate\Database\Eloquent\Model;

class InventoryUpdate
{
    /**
     * Old inventory instance before changes have made.
     *
     *  @var \IvanSotelo\Inventory\Inventory|null
     */
    public $oldInventory = null;

    /**
     * New inventory instance that has been persisted to the storage.
     *
     * @var \IvanSotelo\Inventory\Inventory
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
     * @param  \IvanSotelo\Inventory\Inventory|null $oldInventory
     * @param  \IvanSotelo\Inventory\Inventory $newInventory
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function __construct($oldInventory, Inventory $newInventory, Model $model)
    {
        $this->oldInventory = $oldInventory;

        $this->newInventory = $newInventory;

        $this->model = $model;
    }
}