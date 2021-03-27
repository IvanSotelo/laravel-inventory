<?php

namespace IvanSotelo\Inventory;

use Illuminate\Support\Facades\Config;

trait HasSku
{
    /**
     * The hasOne sku relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function sku()
    {
        return $this->morphOne(InventorySku::class, 'skuriable');
    }

    /**
     * Returns an item record by the specified SKU code.
     *
     * @param string $sku
     *
     * @return bool
     */
    public static function findBySku($sku)
    {
        /*
         * Create a new static instance
         */
        $instance = new static();

        /*
         * Try and find the SKU record
         */
        $sku = $instance
            ->sku()
            ->getRelated()
            ->with('item')
            ->where('code', $sku)
            ->first();

        /*
         * Check if the SKU was found, and if an item is
         * attached to the SKU we'll return it
         */
        if ($sku && $sku->item) {
            return $sku->item;
        }

        /*
         * Return false on failure
         */
        return false;
    }

    /**
     * Returns true/false if the current item has an SKU.
     *
     * @return bool
     */
    public function hasSku()
    {
        if ($this->sku) {
            return true;
        }

        return false;
    }

    /**
     * Returns the item's SKU.
     *
     * @return null|string
     */
    public function getSku()
    {
        if ($this->hasSku()) {
            return $this->sku->getAttribute('code');
        }

        return;
    }

    /**
     * Laravel accessor for the current items SKU.
     *
     * @return null|string
     */
    public function getSkuCodeAttribute()
    {
        return $this->getSku();
    }


    /**
     * Generates an item SKU record.
     *
     * If an item already has an SKU, the SKU record will be returned.
     *
     * If an item does not have a category, it will return false.
     *
     * @return bool|mixed
     */
    public function generateSku()
    {
        // Make sure sku generation is enabled and the item has a category, if not we'll return false.
        if (!$this->skusEnabled()) {
            return false;
        }

        // If the item already has an SKU, we'll return it
        if ($this->hasSku()) {
            return $this->sku;
        }

        // Get the set SKU code length from the configuration file
        $codeLength = Config::get('inventory.sku_code_length');

        // Get the set SKU prefix length from the configuration file
        $prefixLength = Config::get('inventory.sku_prefix_length');

        // Get the set SKU separator
        $skuSeparator = Config::get('inventory.sku_separator');

        // Make sure we trim empty spaces in the separator if
        // it's a string, otherwise we'll set it to NULL
        $skuSeparator = (is_string($skuSeparator) ? trim($skuSeparator) : null);

        // Trim the category name to remove blank spaces, then grab
        // the first 3 letters of the string, and uppercase them
        $prefix = strtoupper(substr(trim($this->category->getAttribute('name')), 0, intval($prefixLength)));

        // We'll make sure the prefix length is greater
        // than zero before we try and generate an SKU
        if (strlen($prefix) > 0) {
            // Create the numerical code by the items ID to
            // accompany the prefix and pad left zeros
            $code = str_pad($this->getKey(), $codeLength, '0', STR_PAD_LEFT);

            // Return and process the generation
            return $this->processSkuGeneration($this->getKey(), $prefix.$skuSeparator.$code);
        }

        // Always return false on generation failure
        return false;
    }

    /**
     * Regenerates the current items SKU by
     * deleting its current SKU and creating
     * another. This will also generate an SKU
     * if one does not exist.
     *
     * @return bool|mixed
     */
    public function regenerateSku()
    {
        $sku = $this->sku()->first();

        if ($sku) {
            // Capture current SKU
            $previousSku = $sku;

            // Delete current SKU
            $sku->delete();

            // Try to generate a new SKU
            $newSku = $this->generateSku();

            // New sku generation successful, return it
            if ($newSku) {
                return $newSku;
            }

            // Failed generating a new sku, we'll restore the old one
            return $this->processSkuGeneration($this->getKey(), $previousSku->code);
        }

        // Always generate an SKU if one doesn't exist
        return $this->generateSku();
    }

    /**
     * Creates an SKU with the specified code. If overwrite is true,
     * the current items SKU will be deleted if it exists before creating
     * then SKU. If overwrite is false but the item has an SKU, an exception
     * is thrown.
     *
     * @param string $code
     * @param bool   $overwrite
     *
     * @throws SkuAlreadyExistsException
     *
     * @return mixed|bool
     */
    public function createSku($code, $overwrite = false)
    {
        // Get the current SKU record
        $sku = $this->sku()->first();

        if ($sku) {
            // The dev doesn't want the SKU overridden, we'll thrown an exception
            if (!$overwrite) {
                $message = Lang::get('inventory::exceptions.SkuAlreadyExistsException');

                throw new SkuAlreadyExistsException($message);
            }

            // Overwrite is true, lets update the current SKU
            return $this->updateSku($code, $sku);
        }

        // No SKU exists, lets create one
        return $this->processSkuGeneration($this->getKey(), $code);
    }

    /**
     * Updates the items current SKU or the SKU
     * supplied with the specified code.
     *
     * @param string $code
     * @param null   $sku
     *
     * @return mixed|bool
     */
    public function updateSku($code, $sku = null)
    {
        // Get the current SKU record if one isn't supplied
        if (!$sku) {
            $sku = $this->sku()->first();
        }

        /*
         * If an SKU still doesn't exist after
         * trying to find one, we'll create one
         */
        if (!$sku) {
            return $this->processSkuGeneration($this->getKey(), $code);
        }

        return $this->processSkuUpdate($sku, $code);
    }

    /**
     * Processes an SKU generation covered by database transactions.
     *
     * @param int|string $inventoryId
     * @param string     $code
     *
     * @return bool|mixed
     */
    protected function processSkuGeneration($inventoryId, $code)
    {
        $this->dbStartTransaction();

        try {
            $sku = $this->sku()->getRelated()->newInstance();

            $sku->setAttribute('inventory_id', $inventoryId);
            $sku->setAttribute('code', $code);

            if ($sku->save()) {
                $this->dbCommitTransaction();

                $this->fireEvent('inventory.sku.generated', [
                    'item' => $this,
                    'sku' => $sku,
                ]);

                return $sku;
            }
        } catch (\Exception $e) {
            $this->dbRollbackTransaction();
        }

        return false;
    }

    /**
     * Processes updating the specified SKU
     * record with the specified code.
     *
     * @param Model  $sku
     * @param string $code
     *
     * @return mixed|bool
     */
    protected function processSkuUpdate(Model $sku, $code)
    {
        $this->dbStartTransaction();

        try {
            if ($sku->update(compact('code'))) {
                $this->dbCommitTransaction();

                return $sku;
            }
        } catch (\Exception $e) {
            $this->dbRollbackTransaction();
        }

        return false;
    }

    /**
     * Returns the configuration option for the
     * enablement of automatic SKU generation.
     *
     * @return mixed
     */
    protected function skusEnabled()
    {
        return Config::get('inventory.skus_enabled', false);
    }

}