<?php

namespace IvanSotelo\Inventory;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use IvanSotelo\Inventory\Commands\InventoryCommand;

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
            ->hasViews()
            ->hasMigration('create_laravel_inventory_table')
            ->hasCommand(InventoryCommand::class);
    }
}
