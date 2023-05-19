<?php

namespace IvanSotelo\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    /**
     * The inventory movements table.
     *
     * @var string
     */
    protected $table = 'inventory_movements';

    protected $fillable = [
        'stock_id',
        'user_id',
        'before',
        'after',
        'cost',
        'reason',
    ];

    /**
     * The belongsTo stock relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stock()
    {
        return $this->belongsTo(InventoryStock::class);
    }
}
