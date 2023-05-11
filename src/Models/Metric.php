<?php

namespace IvanSotelo\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class Metric extends Model
{
  protected $table = 'metrics';

  /**
   * The hasMany inventory items relationship.
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function stocks()
  {
    return $this->hasMany(InventoryStock::class);
  }
}
