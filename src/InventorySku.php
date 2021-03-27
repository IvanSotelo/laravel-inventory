<?php

namespace IvanSotelo\Inventory;

use Illuminate\Database\Eloquent\Model;

class InventorySku extends Model
{

    /**
     * Relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }
}