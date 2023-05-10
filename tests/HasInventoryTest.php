<?php

namespace IvanSotelo\Inventory\Tests;

class HasInventoryTest extends TestCase
{
    /** @test */
    public function return_true_when_inventory_is_missing()
    {
        $this->assertEquals(0, $this->inventoryModel->inventories->first()->quantity);
        $this->assertTrue($this->inventoryModel->notInInventory());
        $this->assertFalse($this->inventoryModel->inInventory());
    }

    /** @test */
    public function return_true_when_has_inventory()
    {
        $this->assertEquals(10, $this->secondInventoryModel->inventories->first()->quantity);
        $this->assertFalse($this->secondInventoryModel->notInInventory());
        $this->assertTrue($this->secondInventoryModel->inInventory());
    }
}
