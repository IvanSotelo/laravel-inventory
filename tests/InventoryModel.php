<?php

namespace IvanSotelo\Inventory\Tests;

use Illuminate\Database\Eloquent\Model;
use IvanSotelo\Inventory\HasInventory;

class InventoryModel extends Model
{
    use HasInventory;

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = [];
}
