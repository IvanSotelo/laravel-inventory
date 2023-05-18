<?php

namespace IvanSotelo\Inventory\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use IvanSotelo\Inventory\Exceptions\NotEnoughStockException;

trait InventoryStockTrait
{
    /*
     * Helpers for starting database transactions
     */
    use DatabaseTransactionTrait;

    /*
     * Verification helper functions
     */
    use VerifyTrait;

    /*
     * Set's the models constructor method to automatically assign the
     * user_id's attribute to the current logged in user
     */
    use UserIdentificationTrait;

    /**
     * Stores the reason for updating / creating a stock.
     *
     * @var string
     */
    public $reason = '';

    /**
     * Stores the cost for updating a stock.
     *
     * @var int|float|string
     */
    public $cost = 0;

    /**
     * Stores the quantity before an update.
     *
     * @var int|float|string
     */
    protected $beforeQuantity = 0;

    /**
     * Overrides the models boot function to set
     * the user ID automatically to every new record.
     */
    public static function bootInventoryStockTrait()
    {
        static::creating(function (Model $model) {
            $model->user_id = $model->getCurrentUserId();

            /*
             * Check if a reason has been set, if not
             * let's retrieve the default first entry reason
             */
            if (! $model->reason) {
                $model->reason = Lang::get('inventory::reasons.first_record');
            }
        });

        static::created(function (Model $model) {
            $this->generateStockMovement(0, $this->quantity, $this->reason, $this->cost);
        });

        static::updating(function (Model $model) {
            /*
             * Retrieve the original quantity before it was updated,
             * so we can create generate an update with it
             */
            $model->beforeQuantity = $model->getOriginal('quantity');

            /*
             * Check if a reason has been set, if not let's retrieve the default change reason
             */
            if (! $model->reason) {
                $model->reason = Lang::get('inventory::reasons.change');
            }
        });

        static::updated(function (Model $model) {
            $this->generateStockMovement($this->beforeQuantity, $this->quantity, $this->reason, $this->cost);
        });
    }

    /**
     * Processes a 'take' operation on the current stock.
     *
     * @param  int|float|string  $quantity
     * @param  string  $reason
     * @param  int|float|string  $cost
     * @return $this|bool
     *
     * @throws InvalidQuantityException
     * @throws NotEnoughStockException
     */
    public function take($quantity, $reason = '', $cost = 0)
    {
        return $this->processTakeOperation($quantity, $reason, $cost);
    }

    /**
     * Processes a 'put' operation on the current stock.
     *
     * @param  int|float|string  $quantity
     * @param  string  $reason
     * @param  int|float|string  $cost
     * @return $this
     *
     * @throws InvalidQuantityException
     */
    public function put($quantity, $reason = '', $cost = 0)
    {
        if ($this->isValidQuantity($quantity)) {
            return $this->processPutOperation($quantity, $reason, $cost);
        }
    }

    /**
     * Moves a stock to the specified location.
     *
     *
     * @return bool
     */
    public function moveTo(Model $location)
    {
        return $this->processMoveOperation($location);
    }

    /**
     * Processes removing quantity from the current stock.
     *
     * @param  int|float|string  $taking
     * @param  string  $reason
     * @param  int|float|string  $cost
     * @return $this|bool
     */
    protected function processTakeOperation($taking, $reason = '', $cost = 0)
    {
        if ($this->isValidQuantity($taking) && $this->hasEnoughStock($taking)) {
            $available = $this->quantity;

            $left = (float) $available - (float) $taking;

            /*
             * If the updated total and the beginning total are the same, we'll check if
             * duplicate movements are allowed. We'll return the current record if
             * they aren't.
             */
            if ((float) $left === (float) $available && ! $this->allowDuplicateMovementsEnabled()) {
                return $this;
            }

            $this->quantity = $left;

            $this->setReason($reason);

            $this->setCost($cost);

            $this->dbStartTransaction();
            try {
                if ($this->save()) {
                    $this->dbCommitTransaction();

                    $this->fireEvent('inventory.stock.taken', [
                        'stock' => $this,
                    ]);

                    return $this;
                }
            } catch (\Exception $e) {
                $this->dbRollbackTransaction();
            }
        }

        return false;
    }

    /**
     * Processes adding quantity to current stock.
     *
     * @param  int|float|string  $putting
     * @param  string  $reason
     * @param  int|float|string  $cost
     * @return $this|bool
     */
    protected function processPutOperation($putting, $reason = '', $cost = 0)
    {
        if ($this->isValidQuantity($putting)) {
            $current = $this->quantity;

            $total = (float) $putting + (float) $current;

            // If the updated total and the beginning total are the same,
            // we'll check if duplicate movements are allowed.
            if ((float) $total === (float) $current && ! $this->allowDuplicateMovementsEnabled()) {
                return $this;
            }

            $this->quantity = $total;

            $this->setReason($reason);

            $this->setCost($cost);

            $this->dbStartTransaction();

            try {
                if ($this->save()) {
                    $this->dbCommitTransaction();

                    $this->fireEvent('inventory.stock.added', [
                        'stock' => $this,
                    ]);

                    return $this;
                }
            } catch (\Exception $e) {
                $this->dbRollbackTransaction();
            }
        }

        return false;
    }

    /**
     * Processes the stock moving from it's current
     * location, to the specified location.
     *
     * @param  mixed  $location
     * @return bool
     */
    private function processMoveOperation(Model $location)
    {
        $this->location_id = $location->getKey();

        $this->dbStartTransaction();

        try {
            if ($this->save()) {
                $this->dbCommitTransaction();

                $this->fireEvent('inventory.stock.moved', [
                    'stock' => $this,
                ]);

                return $this;
            }
        } catch (\Exception $e) {
            $this->dbRollbackTransaction();
        }

        return false;
    }

    /**
     * Creates a new stock movement record.
     *
     * @param  int|float|string  $before
     * @param  int|float|string  $after
     * @param  string  $reason
     * @param  int|float|string  $cost
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function generateStockMovement($before, $after, $reason = '', $cost = 0)
    {
        $insert = [
            'before' => $before,
            'after' => $after,
            'reason' => $reason,
            'cost' => $cost,
        ];

        return $this->movements()->create($insert);
    }

    /**
     * Sets the cost attribute.
     *
     * @param  int|float|string  $cost
     */
    private function setCost($cost = 0)
    {
        $this->cost = $cost;
    }

    /**
     * Sets the reason attribute.
     *
     * @param  string  $reason
     */
    private function setReason($reason = '')
    {
        $this->reason = $reason;
    }

    /**
     * Returns true/false from the configuration file determining
     * whether or not stock movements can have the same before and after
     * quantities.
     *
     * @return bool
     */
    private function allowDuplicateMovementsEnabled()
    {
        return Config::get('inventory.allow_duplicate_movements');
    }

    /**
     * Returns true if there is enough stock for the specified quantity being taken.
     * Throws NotEnoughStockException otherwise.
     *
     * @param  int|float|string  $quantity
     * @return bool
     *
     * @throws NotEnoughStockException
     */
    public function hasEnoughStock($quantity = 0)
    {
        $available = $this->quantity;

        if ((float) $available === (float) $quantity || $available > $quantity) {
            return true;
        }

        $message = Lang::get('inventory::exceptions.NotEnoughStockException', [
            'quantity' => $quantity,
            'available' => $available,
        ]);

        throw new NotEnoughStockException($message);
    }
}
