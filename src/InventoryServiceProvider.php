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
            ->hasConfigFile()
            ->hasMigrations(['create_metrics_table', 'create_locations_table', 'create_inventory_tables', 'create_inventory_sku_table', 'modify_inventory_table_for_assemblies', 'create_inventory_assemblies_table'])
            ->hasCommand(InventoryCommand::class);
    }
}
