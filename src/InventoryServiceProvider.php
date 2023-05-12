<?php

namespace IvanSotelo\Inventory;

use IvanSotelo\Inventory\Commands\InventoryCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InventoryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-inventory')
            ->hasConfigFile('inventory')
            ->hasMigrations(['create_metrics_table', 'create_locations_table', 'create_inventory_stocks_table', 'create_inventory_movements_table', 'create_inventory_skus_table', 'modify_inventory_table_for_assemblies', 'create_inventory_assemblies_table'])
            ->hasCommand(InventoryCommand::class);
    }
}
