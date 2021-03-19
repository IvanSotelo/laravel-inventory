# Inventory Management for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivansotelo/laravel-inventory.svg?style=flat-square)](https://packagist.org/packages/ivansotelo/laravel-inventory)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/ivansotelo/laravel-inventory/ci?label=tests)](https://github.com/ivansotelo/laravel-inventory/actions?query=workflow%3Aci)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/ivansotelo/laravel-inventory/ci?label=code%20style)](https://github.com/ivansotelo/laravel-inventory/actions?query=workflow%3A"ci")
[![Total Downloads](https://img.shields.io/packagist/dt/ivansotelo/laravel-inventory.svg?style=flat-square)](https://packagist.org/packages/ivansotelo/laravel-inventory)


This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/package-laravel-inventory-laravel.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/package-laravel-inventory-laravel)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require ivansotelo/laravel-inventory
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="IvanSotelo\Inventory\InventoryServiceProvider" --tag="laravel-inventory-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="IvanSotelo\Inventory\InventoryServiceProvider" --tag="laravel-inventory-config"
```

This is the contents of the published config file:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Inventory Class
    |--------------------------------------------------------------------------
    /
    / The class of inventory model that holds all inventories. The model
    / must be or extend `IvanSotelo\Inventory\Inventory::class`
    / for the inventory package to work properly.
    /
    */
    'inventory_model' => IvanSotelo\Inventory\Inventory::class,

    /*
    |--------------------------------------------------------------------------
    | Default field attribute
    |--------------------------------------------------------------------------
    /
    / The name of the column which holds the key for the relationship with the model related to the inventory.
    / You can change this value if you have set a different name in the migration for the inventories
    / table. You might decide to go with the SKU field instead of the ID field.
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
```

## Usage

Add the HasInventory to the Model, the trait will enable inventory tracking.

```php
...
use IvanSotelo\Inventory\HasInventory;

class Product extends Model
{
    use HasInventory;

    ...
}
```

### Functions

```php
...
$product = Product::first();

$product->set(10); // $product->currentInventory()->quantity; (Will result in 10)

$product->currentInventory() //Return inventory instance

$product->add(5); // $product->currentInventory()->quantity; (Will result in 15)

$product->take(5); // $product->currentInventory()->quantity; (Will result in 10)

$product->inInventory(); // Return true

$product->clearInventory(); // $product->currentInventory(); (return null)

$product->notInInventory(); // Return true

--- Scopes ---

Product::InventoryIs(10)->get(); // Return all products with inventory of 10

Product::InventoryIs(10, '>=')->get(); // Return all products with inventory of 10 or greater

Product::InventoryIs(10, '<=')->get(); // Return all products with inventory of 10 or less

Product::InventoryIs(10, '>=', [1,2,3])->get(); // Return all products with inventory of 10 or greater where product id is [1,2,3]

Proudct::InventoryIsNot(10)->get(); // Return all products where inventory is not 10

Proudct::InventoryIsNot(10, [1,2,3])->get(); // Return all products where inventory is not 10 where product id is 1,2,3
```


## Usage

The package fires these events:
- `InventoryUpdate`: Inventory changes.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ivan Sotelo](https://github.com/IvanSotelo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
