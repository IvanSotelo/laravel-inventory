<?php

namespace IvanSotelo\Inventory\Tests;

class HasInventoryTest extends TestCase
{
    /** @test */
    public function return_true_when_inventory_is_missing()
    {
        $this->assertEquals(0, $this->inventoryModel->inventories->first()->quantity);
        $this->assertTrue($this->inventoryModel->notInInventory());
    }
}