<?php

namespace IvanSotelo\Inventory\Traits;

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
