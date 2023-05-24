<?php

namespace IvanSotelo\Inventor\Models;

use Illuminate\Database\Eloquent\Model;

class InventorySku extends Model
{
    /**
     * Relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function inventoriable()
    {
        return $this->morphTo();
    }
}
