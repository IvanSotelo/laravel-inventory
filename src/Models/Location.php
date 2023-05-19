<?php

namespace IvanSotelo\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';

    protected $fillable = [
        'name',
    ];

    /**
     * Relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function locationable()
    {
        return $this->morphTo();
    }

    /**
     * The hasMany stocks relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }
}
