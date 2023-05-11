<?php

namespace IvanSotelo\Inventory\Tests;

class HasInventoryTest extends TestCase
{
    /** @test */
    public function return_true_when_inventory_is_missing()
    {
        $this->assertEquals(0, $this->inventoryModel->stocks->first()->quantity);
        $this->assertFalse($this->inventoryModel->isInStock());
    }

    /** @test */
    public function return_true_when_has_inventory()
    {
        $this->assertEquals(10, $this->secondInventoryModel->stocks->first()->quantity);
        $this->assertTrue($this->secondInventoryModel->isInStock());
    }
}
