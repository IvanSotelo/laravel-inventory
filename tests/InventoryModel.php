<?php

namespace IvanSotelo\Inventory\Tests;

use Illuminate\Database\Eloquent\Model;
use IvanSotelo\Inventory\Traits\HasInventory;

class InventoryModel extends Model
{
    use HasInventory;

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = [];
}
