<?php

namespace IvanSotelo\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{

    /**
     * The inventory movements table.
     *
     * @var string
     */
    protected $table = 'inventory_movements';

    /**
     * The belongsTo stock relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stock()
    {
        return $this->belongsTo(Inventory::class);
    }
}
