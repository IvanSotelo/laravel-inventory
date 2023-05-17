<?php

namespace IvanSotelo\Inventory\Traits;

use Illuminate\Support\Facades\Config;

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
     * Processes adding quantity to current stock.
     *
     * @param  int|float|string  $putting
     * @param  string  $reason
     * @param  int|float|string  $cost
     * @return $this|bool
     */
    protected function processPutOperation($putting, $reason = '', $cost = 0, $receiver_id = null, $receiver_type = null, $serial = null)
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
}
