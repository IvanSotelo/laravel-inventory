<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inventory Class
    |--------------------------------------------------------------------------
    /
    / The class of inventory model that holds all inventories.
    /
    */
    'inventory_table' => 'products',

    /*
    |--------------------------------------------------------------------------
    | Default field attribute
    |--------------------------------------------------------------------------
    /
    / The name of the column which holds the key for the relationship with the model related to the inventory.
    / You can change this value if you have set a different name in the migration for the inventories
    / table. You might decide to go with the SKU field in stead of the ID field.
    /
    */
    'model_primary_field_attribute' => 'inventoriable_id',

    /*
    |--------------------------------------------------------------------------
    | Allow no user
    |--------------------------------------------------------------------------
    |
    | Allows inventory changes to occur without a user responsible.
    |
    */

    'allow_no_user' => false,

    /*
    |--------------------------------------------------------------------------
    | Allow Duplicate Movements
    |--------------------------------------------------------------------------
    |
    | Allows inventory stock movements to have the same before and after quantity.
    |
    */

    'allow_duplicate_movements' => true,

    /*
    |--------------------------------------------------------------------------
    | Rollback Cost
    |--------------------------------------------------------------------------
    |
    | For example, if the movement's cost that is being rolled
    | back is 500, the rolled back movement will be -500.
    |
    */

    'rollback_cost' => true,

    /*
    |--------------------------------------------------------------------------
    | Skus Enabled
    |--------------------------------------------------------------------------
    |
    | Enables SKUs to be automatically generated on item creation.
    |
    */

    'skus_enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Sku Prefix Length
    |--------------------------------------------------------------------------
    |
    | The sku prefix length, not including the code for example:
    |
    | An item with a category named 'Sauce', the sku prefix generated will be: SAU
    |
    */

    'sku_prefix_length' => 3,

    /*
    |--------------------------------------------------------------------------
    | Sku Code Length
    |--------------------------------------------------------------------------
    |
    | The sku code length, not including prefix for example:
    |
    | An item with an ID of 1 (one) the sku code will be: 000001
    |
    */

    'sku_code_length' => 6,

    /*
     * The sku separator for use in separating the prefix from the code.
     *
     * For example, if a hyphen (-) is inserted in the string below, a possible
     * SKU might be 'DRI-00001'
     *
     * @var string
     */

    /*
    |--------------------------------------------------------------------------
    | Sku Separator
    |--------------------------------------------------------------------------
    |
    | The sku separator for use in separating the prefix from the code.
    |
    | For example, if a hyphen (-) is inserted in the string
    | below, a possible SKU might be 'DRI-00001'
    |
    */

    'sku_separator' => '',

];
