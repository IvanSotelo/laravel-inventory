<?php

namespace IvanSotelo\Inventory\Traits;

use Illuminate\Support\Facades\Lang;
use IvanSotelo\Inventory\Exceptions\InvalidQuantityException;

/**
 * Trait VerifyTrait.
 */
trait VerifyTrait
{
    /**
     * Returns true if the specified quantity is valid, throws
     * InvalidQuantityException otherwise.
     *
     * @param  int|float|string  $quantity
     * @return bool
     *
     * @throws InvalidQuantityException
     */
    public function isValidQuantity($quantity)
    {
        if ($this->isPositive($quantity)) {
            return true;
        }

        $message = Lang::get('inventory::exceptions.InvalidQuantityException', [
            'quantity' => $quantity,
        ]);

        throw new InvalidQuantityException($message);
    }

    /**
     * Returns true/false if the number specified is numeric.
     *
     * @param  int|float|string  $number
     * @return bool
     */
    private function isNumeric($number)
    {
        return is_numeric($number);
    }

    /**
     * Returns true or false if the number inserted is positive.
     *
     * @param  int|float|string  $number
     * @return bool
     */
    private function isPositive($number)
    {
        if ($this->isNumeric($number)) {
            return $number >= 0 ? true : false;
        }

        return false;
    }

    /**
     * Returns true/false if the specified model is a subclass
     * of the Eloquent Model.
     *
     * @param  mixed  $model
     * @return bool
     */
    private function isModel($model)
    {
        return is_subclass_of($model, 'Illuminate\Database\Eloquent\Model');
    }
}
