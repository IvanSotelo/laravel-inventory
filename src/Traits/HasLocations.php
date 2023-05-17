<?php

namespace IvanSotelo\Inventory\Traits;

use IvanSotelo\Inventory\Models\Location;

trait HasLocations
{
    /**
     * The hasMany locations relationship.
     *
     * @return  \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function locations()
    {
        return $this->morphMany(Location::class, 'locationable')->latest('id');
    }
    
}
