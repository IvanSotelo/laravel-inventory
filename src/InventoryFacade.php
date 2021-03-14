<?php

namespace IvanSotelo\Inventory;

use Illuminate\Support\Facades\Facade;

/**
 * @see \IvanSotelo\Inventory\Inventory
 */
class InventoryFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'inventory';
    }
}
