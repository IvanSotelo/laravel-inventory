<?php

namespace IvanSotelo\Inventory\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use IvanSotelo\Inventory\Exceptions\InvalidMovementException;
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
     * Stores the quantity before an update.
     *
     * @var int|float|string
     */
    protected $afterQuantity = 0;

    /**
     * Overrides the models boot function to set
     * the user ID automatically to every new record.
     */
    public static function bootInventoryStockTrait()
    {
        static::creating(function ($model) {
            $model->setAttribute('user_id', $model->getCurrentUserId());

            $model->afterQuantity = $model->getAttribute('quantity');
            /*
             * Check if a reason has been set, if not
             * let's retrieve the default first entry reason
             */
            if (! $model->getAttribute('reason')) {
                $model->reason = Lang::get('inventory::reasons.first_record');
            }
        });

        static::created(function ($model) {
            $model->postCreate();
        });

        static::updating(function ($model) {
            /*
             * Retrieve the original quantity before it was updated,
             * so we can create generate an update with it
             */
            $model->beforeQuantity = $model->getOriginal('quantity') ?? 0;

            /*
             * Check if a reason has been set, if not let's retrieve the default change reason
             */
            if (! $model->reason) {
                $model->reason = $model->getOriginal('quantity') === null ? Lang::get('inventory::reasons.first_record') : Lang::get('inventory::reasons.change');
            }
        });

        static::updated(function ($model) {
            $model->postUpdate();
        });
    }

    /**
     * Generates a stock movement after a stock is created.
     *
     * @return void
     */
    public function postCreate()
    {
        $this->generateStockMovement(0, $this->afterQuantity, $this->reason, $this->cost);
    }

    /**
     * Generates a stock movement after a stock is updated.
     *
     * @return void
     */
    public function postUpdate()
    {
        $this->generateStockMovement($this->beforeQuantity, $this->getAttribute('quantity'), $this->reason, $this->cost);
    }

    /**
     * Processes a 'take' operation on the current stock.
     *
     * @param  int|float|string  $quantity
     * @param  string  $reason
     * @param  int|float|string  $cost
     * @return $this|bool
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
     */
    public function put($quantity, $reason = '', $cost = 0)
    {
        return $this->processPutOperation($quantity, $reason, $cost);
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
     * Rolls back the last movement, or the movement specified. If recursive is set to true,
     * it will rollback all movements leading up to the movement specified.
     *
     * @param  mixed  $movement
     * @param  bool  $recursive
     * @return $this
     */
    public function rollback($movement = null, $recursive = false)
    {
        if ($movement) {
            $movement = $this->getMovement($movement);
        } else {
            $movement = $this->getLastMovement();
        }

        return $this->processRollbackOperation($movement, $recursive);
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
            $available = $this->getAttribute('quantity');

            $left = (float) $available - (float) $taking;

            /*
             * If the updated total and the beginning total are the same, we'll check if
             * duplicate movements are allowed. We'll return the current record if
             * they aren't.
             */
            if ((float) $left === (float) $available && ! $this->allowDuplicateMovementsEnabled()) {
                return $this;
            }

            $this->setAttribute('quantity', $left);

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
            $current = $this->getAttribute('quantity');

            $total = (float) $putting + (float) $current;

            // If the updated total and the beginning total are the same,
            // we'll check if duplicate movements are allowed.
            if ((float) $total === (float) $current && ! $this->allowDuplicateMovementsEnabled()) {
                return $this;
            }

            $this->setAttribute('quantity', $total);

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
     * @return bool
     */
    private function processMoveOperation(Model $location)
    {
        $this->setAttribute('location_id', $location->getKey());

        $this->dbStartTransaction();

        try {
            if ($this->save()) {
                $this->dbCommitTransaction();

                $this->fireEvent('inventory.stock.moved', [
                    'stock' => $this,
                ]);

                return true;
            }
        } catch (\Exception $e) {
            $this->dbRollbackTransaction();
        }

        return false;
    }

    /**
     * Processes a single rollback operation.
     *
     * @param  bool  $recursive
     * @return $this|bool|array
     */
    protected function processRollbackOperation(Model $movement, $recursive = false)
    {
        if ($recursive) {
            return $this->processRecursiveRollbackOperation($movement);
        }

        $amt = $movement->getAttribute('after') - $movement->getAttribute('before');
        $this->setAttribute('quantity', $this->getAttribute('quantity') - $amt);

        $reason = Lang::get('inventory::reasons.rollback', [
            'id' => $movement->getOriginal('id'),
            'date' => $movement->getOriginal('created_at'),
        ]);

        $this->setReason($reason);

        if ($this->rollbackCostEnabled()) {
            $this->setCost($movement->getAttribute('cost'));

            $this->reverseCost();
        }

        $this->dbStartTransaction();

        try {
            if ($this->save()) {
                $this->dbCommitTransaction();

                $this->fireEvent('inventory.stock.rollback', [
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
     * Processes a recursive rollback operation.
     *
     *
     * @return array
     */
    protected function processRecursiveRollbackOperation(Model $movement)
    {
        /*
         * Retrieve movements that were created after
         * the specified movement, and order them descending
         */
        $movements = $this
            ->movements()
            ->where('created_at', '>=', $movement->getOriginal('created_at'))
            ->orderBy('created_at', 'DESC')
            ->get();

        $rollbacks = [];

        if ($movements->count() > 0) {
            foreach ($movements as $movement) {
                $rollbacks = $this->processRollbackOperation($movement);
            }
        }

        return $rollbacks;
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
            'warehouse_id' => $this->location->locationable_id
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
     * Reverses the cost of a movement.
     */
    protected function reverseCost()
    {
        $cost = $this->getAttribute('cost');

        if ($this->isPositive($cost)) {
            $this->setCost(-abs($cost));
        } else {
            $this->setCost(abs($cost));
        }
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
        $available = $this->getAttribute('quantity');

        if ((float) $available === (float) $quantity || $available > $quantity) {
            return true;
        }

        $message = Lang::get('inventory::exceptions.NotEnoughStockException', [
            'quantity' => $quantity,
            'available' => $available,
        ]);

        throw new NotEnoughStockException($message);
    }

    /**
     * Returns the last movement on the current stock record.
     *
     * @return bool|Model
     */
    public function getLastMovement()
    {
        $movement = $this->movements()->orderBy('created_at', 'DESC')->first();

        if ($movement) {
            return $movement;
        }

        return false;
    }

    /**
     * Returns a movement depending on the specified argument. If an object is supplied, it is checked if it
     * is an instance of an eloquent model. If a numeric value is entered, it is retrieved by it's ID.
     *
     * @param  int|string|Model  $movement
     * @return mixed
     *
     * @throws InvalidMovementException
     */
    public function getMovement($movement)
    {
        if ($this->isModel($movement)) {
            return $movement;
        } elseif (is_numeric($movement)) {
            return $this->getMovementById($movement);
        } else {
            $message = Lang::get('inventory::exceptions.InvalidMovementException', [
                'movement' => $movement,
            ]);

            throw new InvalidMovementException($message);
        }
    }

    /**
     * Retrieves a movement by the specified ID.
     *
     * @param  int|string  $id
     * @return null|Model
     */
    protected function getMovementById($id)
    {
        return $this->movements()->find($id);
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
     * Returns true/false from the configuration file determining
     * whether or not to rollback costs when a rollback occurs on
     * a stock.
     *
     * @return bool
     */
    protected function rollbackCostEnabled()
    {
        return Config::get('inventory.rollback_cost');
    }
}
